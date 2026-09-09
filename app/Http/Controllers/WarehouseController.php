<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\WarehouseRequest;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(): View
    {
        $warehouses = Warehouse::withCount('stockLevels')->orderBy('name')->paginate(20);

        return view('warehouses.index', ['warehouses' => $warehouses]);
    }

    public function create(): View
    {
        return view('warehouses.create');
    }

    public function store(WarehouseRequest $request): RedirectResponse
    {
        Warehouse::create($request->validatedForModel());

        return redirect()->route('warehouses.index')->with('success', 'Warehouse created.');
    }

    public function edit(Warehouse $warehouse): View
    {
        return view('warehouses.edit', ['warehouse' => $warehouse]);
    }

    public function update(WarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $warehouse->update($request->validatedForModel());

        return redirect()->route('warehouses.index')->with('success', 'Warehouse updated.');
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        if ($warehouse->stockLevels()->where('quantity', '>', 0)->exists()) {
            return redirect()->route('warehouses.index')->with('error', 'Cannot delete a warehouse that still holds stock.');
        }

        $warehouse->delete();

        return redirect()->route('warehouses.index')->with('success', 'Warehouse deleted.');
    }
}
