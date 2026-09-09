<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\CalculatorType;
use App\Models\Department;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $departments = Department::active()
            ->withCount(['products' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();

        $calculatorTypes = CalculatorType::active()->with('department')->orderBy('name')->get();

        $featuredPaints = Product::active()
            ->whereHas('department', fn ($query) => $query->where('slug', 'paints'))
            ->with(['department', 'category'])
            ->withComputedStock()
            ->orderBy('selling_price')
            ->take(4)
            ->get();

        $heroImages = Product::active()
            ->whereNotNull('image_path')
            ->inRandomOrder()
            ->limit(10)
            ->pluck('image_path');

        return view('storefront.home', [
            'departments' => $departments,
            'calculatorTypes' => $calculatorTypes,
            'featuredPaints' => $featuredPaints,
            'heroImages' => $heroImages,
            'stats' => [
                'departments' => $departments->count(),
                'products' => Product::active()->count(),
                'calculators' => $calculatorTypes->count(),
            ],
        ]);
    }
}
