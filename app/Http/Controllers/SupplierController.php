<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Models\Department;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(): View
    {
        $suppliers = Supplier::with('departments')->orderBy('name')->paginate(20);

        return view('suppliers.index', ['suppliers' => $suppliers]);
    }

    public function create(): View
    {
        return view('suppliers.create', ['departments' => Department::orderBy('sort_order')->get()]);
    }

    public function store(SupplierRequest $request): RedirectResponse
    {
        $supplier = Supplier::create($request->validatedForModel());
        $supplier->departments()->sync($request->departmentIds());

        return redirect()->route('suppliers.index')->with('success', 'Supplier created.');
    }

    public function edit(Supplier $supplier): View
    {
        $supplier->load('departments');

        return view('suppliers.edit', ['supplier' => $supplier, 'departments' => Department::orderBy('sort_order')->get()]);
    }

    public function update(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($request->validatedForModel());
        $supplier->departments()->sync($request->departmentIds());

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted.');
    }
}
