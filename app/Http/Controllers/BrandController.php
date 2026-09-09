<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BrandRequest;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(): View
    {
        $brands = Brand::withCount('products')->orderBy('name')->paginate(20);

        return view('brands.index', ['brands' => $brands]);
    }

    public function create(): View
    {
        return view('brands.create');
    }

    public function store(BrandRequest $request): RedirectResponse
    {
        Brand::create($request->validatedForModel());

        return redirect()->route('brands.index')->with('success', 'Brand created.');
    }

    public function edit(Brand $brand): View
    {
        return view('brands.edit', ['brand' => $brand]);
    }

    public function update(BrandRequest $request, Brand $brand): RedirectResponse
    {
        $brand->update($request->validatedForModel());

        return redirect()->route('brands.index')->with('success', 'Brand updated.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        if ($brand->products()->exists()) {
            return redirect()->route('brands.index')->with('error', 'Cannot delete a brand that still has products.');
        }

        $brand->delete();

        return redirect()->route('brands.index')->with('success', 'Brand deleted.');
    }
}
