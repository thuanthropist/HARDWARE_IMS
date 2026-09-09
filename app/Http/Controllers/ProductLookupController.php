<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductLookupController extends Controller
{
    public function index(): View
    {
        return view('product-lookup.index');
    }

    public function search(Request $request): JsonResponse
    {
        $code = trim((string) $request->query('code', ''));

        if ($code === '') {
            return response()->json(['found' => false]);
        }

        $variant = ProductVariant::query()
            ->where('sku', $code)
            ->orWhere('barcode', $code)
            ->with(['product.department', 'product.category', 'product.brand', 'product.productAttributes', 'stockLevels.warehouse'])
            ->first();

        if (! $variant) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found' => true,
            'variant' => [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'variant_name' => $variant->variant_name,
                'edit_url' => route('products.edit', $variant->product),
            ],
            'product' => [
                'name' => $variant->product->name,
                'department' => $variant->product->department->name,
                'category' => $variant->product->category->name,
                'brand' => $variant->product->brand->name ?? null,
                'selling_price' => (float) $variant->product->selling_price,
                'attributes' => $variant->product->attributesArray(),
            ],
            'stock' => $variant->stockLevels->map(fn ($level) => [
                'warehouse' => $level->warehouse->name,
                'quantity' => $level->quantity,
            ]),
        ]);
    }
}
