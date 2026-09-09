<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ProductBatch;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductBatchController extends Controller
{
    public function index(Request $request): View
    {
        $batches = ProductBatch::query()
            ->with(['productVariant.product', 'warehouse'])
            ->where('quantity', '>', 0)
            ->when($request->filled('expiring'), fn ($query) => $query->expiringWithin((int) config('inventory.expiry_warning_days')))
            ->orderBy('expiry_date')
            ->paginate(20)
            ->withQueryString();

        return view('product-batches.index', ['batches' => $batches]);
    }
}
