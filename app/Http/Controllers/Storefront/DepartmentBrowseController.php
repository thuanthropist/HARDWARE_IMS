<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentBrowseController extends Controller
{
    public function show(Department $department, Request $request): View
    {
        $schemas = $department->attributeSchemas;

        $query = Product::query()
            ->active()
            ->where('department_id', $department->id)
            ->withComputedStock()
            ->with(['category', 'brand']);

        if ($request->filled('category')) {
            $categorySlug = $request->string('category')->toString();
            $query->whereHas('category', fn (Builder $q) => $q->where('slug', $categorySlug));
        }

        if ($request->filled('q')) {
            $search = $request->string('q')->toString();
            $query->where(fn (Builder $q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%"));
        }

        $attributeFilters = (array) $request->input('attrs', []);

        foreach ($attributeFilters as $key => $value) {
            $schema = $schemas->firstWhere('attribute_key', $key);

            if (! $schema || $value === null || $value === '') {
                continue;
            }

            if ($schema->input_type === 'number' && is_array($value)) {
                $min = $value['min'] ?? null;
                $max = $value['max'] ?? null;

                if ($min !== null && $min !== '') {
                    $query->whereHas('productAttributes', fn (Builder $q) => $q
                        ->where('attribute_key', $key)
                        ->whereRaw('CAST(attribute_value AS DECIMAL(12,2)) >= ?', [$min]));
                }

                if ($max !== null && $max !== '') {
                    $query->whereHas('productAttributes', fn (Builder $q) => $q
                        ->where('attribute_key', $key)
                        ->whereRaw('CAST(attribute_value AS DECIMAL(12,2)) <= ?', [$max]));
                }

                continue;
            }

            if (is_array($value)) {
                $values = array_values(array_filter($value, fn ($v) => $v !== null && $v !== ''));

                if ($values === []) {
                    continue;
                }

                $query->whereHas('productAttributes', fn (Builder $q) => $q
                    ->where('attribute_key', $key)
                    ->whereIn('attribute_value', $values));

                continue;
            }

            $query->whereHas('productAttributes', fn (Builder $q) => $q
                ->where('attribute_key', $key)
                ->where('attribute_value', $value));
        }

        $sort = $request->string('sort', 'name_asc')->toString();

        match ($sort) {
            'price_asc' => $query->orderBy('selling_price'),
            'price_desc' => $query->orderByDesc('selling_price'),
            'name_desc' => $query->orderByDesc('name'),
            default => $query->orderBy('name'),
        };

        $products = $query->paginate(12)->withQueryString();

        $categories = $department->categories()->withCount([
            'products' => fn (Builder $q) => $q->where('is_active', true),
        ])->orderBy('name')->get();

        return view('storefront.departments.show', [
            'department' => $department,
            'products' => $products,
            'schemas' => $schemas,
            'categories' => $categories,
            'filters' => [
                'q' => $request->string('q')->toString(),
                'category' => $request->string('category')->toString(),
                'sort' => $sort,
                'attrs' => $attributeFilters,
            ],
        ]);
    }
}
