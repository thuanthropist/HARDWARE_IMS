<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $departments = Department::withCount('products')->orderBy('sort_order')->get();

        $stockValue = (float) DB::table('stock_levels')
            ->join('product_variants', 'stock_levels.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->sum(DB::raw('stock_levels.quantity * products.cost_price'));

        $lowStockFilterDepartment = $request->integer('low_stock_department') ?: null;

        $lowStockProducts = Product::query()
            ->active()
            ->lowStock()
            ->with('department')
            ->when($lowStockFilterDepartment, fn ($query) => $query->where('department_id', $lowStockFilterDepartment))
            ->orderBy('department_id')
            ->get();

        return view('dashboard', [
            'totalProducts' => Product::count(),
            'departments' => $departments,
            'stockValue' => $stockValue,
            'lowStockCount' => Product::active()->lowStock()->count(),
            'lowStockProducts' => $lowStockProducts,
            'lowStockFilterDepartment' => $lowStockFilterDepartment,
        ]);
    }
}
