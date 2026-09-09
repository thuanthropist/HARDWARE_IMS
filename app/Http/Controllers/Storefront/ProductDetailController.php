<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\CalculatorType;
use App\Models\Product;
use Illuminate\View\View;

class ProductDetailController extends Controller
{
    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load(['department', 'category', 'brand', 'variants', 'productAttributes']);

        $specs = $product->department->attributeSchemas->map(fn ($schema) => [
            'label' => $schema->label,
            'unit' => $schema->unit,
            'value' => $product->productAttributes->firstWhere('attribute_key', $schema->attribute_key)?->attribute_value,
        ])->filter(fn (array $spec) => $spec['value'] !== null && $spec['value'] !== '');

        $relatedProducts = Product::query()
            ->active()
            ->where('department_id', $product->department_id)
            ->where('id', '!=', $product->id)
            ->withComputedStock()
            ->inRandomOrder()
            ->limit(4)
            ->get();

        $calculatorTypes = CalculatorType::active()
            ->where('department_id', $product->department_id)
            ->get();

        return view('storefront.products.show', [
            'product' => $product,
            'specs' => $specs,
            'relatedProducts' => $relatedProducts,
            'calculatorTypes' => $calculatorTypes,
        ]);
    }
}
