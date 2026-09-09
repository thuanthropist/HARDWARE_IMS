<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Picqer\Barcode\BarcodeGeneratorSVG;

class ProductVariantBarcodeController extends Controller
{
    public function svg(ProductVariant $productVariant): Response
    {
        $generator = new BarcodeGeneratorSVG();
        $svg = $generator->getBarcode($productVariant->barcode ?: $productVariant->sku, $generator::TYPE_CODE_128, 2, 60);

        return response($svg, 200, ['Content-Type' => 'image/svg+xml']);
    }

    public function label(ProductVariant $productVariant): View
    {
        $productVariant->load('product');

        return view('product-variants.label', ['variant' => $productVariant]);
    }
}
