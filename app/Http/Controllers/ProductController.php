<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Brand;
use App\Models\Department;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $departments = Department::orderBy('sort_order')->get();
        $activeDepartmentId = request()->integer('department') ?: null;

        $products = Product::query()
            ->with(['department', 'category', 'brand', 'variants.stockLevels'])
            ->when($activeDepartmentId, fn ($query) => $query->where('department_id', $activeDepartmentId))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $tabs = collect([['label' => 'All', 'href' => route('products.index'), 'active' => ! $activeDepartmentId]])
            ->concat($departments->map(fn (Department $department) => [
                'label' => $department->name,
                'href' => route('products.index', ['department' => $department->id]),
                'active' => $activeDepartmentId === $department->id,
            ]))
            ->all();

        return view('products.index', ['products' => $products, 'tabs' => $tabs]);
    }

    public function create(): View
    {
        return view('products.create', $this->formData());
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $data = $request->validatedForModel();

            if ($request->hasFile('image')) {
                $data['image_path'] = $request->file('image')->store('products', 'public');
            }

            $product = Product::create($data);

            foreach ($request->attributesForModel() as $attribute) {
                $product->productAttributes()->create($attribute);
            }

            $variantSku = ProductVariant::generateSku($product);

            ProductVariant::create([
                'product_id' => $product->id,
                'variant_name' => 'Standard',
                'sku' => $variantSku,
                'barcode' => $variantSku,
                'additional_price' => 0,
            ]);

            AuditLogService::log('product.created', $product, null, $request->validatedForModel());
        });

        return redirect()->route('products.index')->with('success', 'Product created.');
    }

    public function show(Product $product): RedirectResponse
    {
        return redirect()->route('products.edit', $product);
    }

    public function edit(Product $product): View
    {
        $product->load('productAttributes', 'variants');

        return view('products.edit', $this->formData($product));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        DB::transaction(function () use ($request, $product): void {
            $original = $product->getOriginal();
            $data = $request->validatedForModel();

            if ($request->hasFile('image')) {
                if ($product->image_path) {
                    Storage::disk('public')->delete($product->image_path);
                }

                $data['image_path'] = $request->file('image')->store('products', 'public');
            }

            $product->update($data);

            $submittedKeys = collect($request->attributesForModel())->pluck('attribute_key');

            $product->productAttributes()->whereNotIn('attribute_key', $submittedKeys)->delete();

            foreach ($request->attributesForModel() as $attribute) {
                $product->productAttributes()->updateOrCreate(
                    ['attribute_key' => $attribute['attribute_key']],
                    $attribute
                );
            }

            AuditLogService::log('product.updated', $product, $original, $request->validatedForModel());
        });

        return redirect()->route('products.edit', $product)->with('success', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        AuditLogService::log('product.deleted', $product, $product->getOriginal(), null);

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }

    private function formData(?Product $product = null): array
    {
        $departments = Department::with(['categories', 'attributeSchemas'])->orderBy('sort_order')->get();

        return [
            'product' => $product,
            'departments' => $departments,
            'brands' => Brand::orderBy('name')->get(),
            'departmentSchemas' => $departments->mapWithKeys(fn (Department $department) => [
                $department->id => $department->attributeSchemas->map(fn ($schema) => [
                    'key' => $schema->attribute_key,
                    'label' => $schema->label,
                    'type' => $schema->input_type,
                    'unit' => $schema->unit,
                    'required' => $schema->is_required,
                    'options' => $schema->options ?? [],
                ]),
            ]),
            'departmentCategories' => $departments->mapWithKeys(fn (Department $department) => [
                $department->id => $department->categories->map(fn ($category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                ]),
            ]),
            'currentAttributes' => $product ? $product->attributesArray() : [],
        ];
    }
}
