<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CalculatorTypeRequest;
use App\Models\CalculatorType;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CalculatorTypeController extends Controller
{
    public function index(): View
    {
        $calculatorTypes = CalculatorType::withCount(['inputFields', 'formulas', 'submissions'])
            ->with('department')
            ->orderBy('name')
            ->paginate(15);

        return view('calculator-types.index', ['calculatorTypes' => $calculatorTypes]);
    }

    public function create(): View
    {
        return view('calculator-types.create', ['departments' => Department::orderBy('sort_order')->get()]);
    }

    public function store(CalculatorTypeRequest $request): RedirectResponse
    {
        $calculatorType = CalculatorType::create($request->validatedForModel());

        return redirect()->route('calculator-types.edit', $calculatorType)->with('success', 'Calculator created.');
    }

    public function show(CalculatorType $calculatorType): RedirectResponse
    {
        return redirect()->route('calculator-types.edit', $calculatorType);
    }

    public function edit(CalculatorType $calculatorType): View
    {
        $calculatorType->load(['inputFields', 'formulas', 'outputProductMappings']);

        return view('calculator-types.edit', [
            'calculatorType' => $calculatorType,
            'departments' => Department::orderBy('sort_order')->get(),
            'testerDefaults' => $calculatorType->defaultInputData(),
        ]);
    }

    public function update(CalculatorTypeRequest $request, CalculatorType $calculatorType): RedirectResponse
    {
        $calculatorType->update($request->validatedForModel());

        return redirect()->route('calculator-types.edit', $calculatorType)->with('success', 'Calculator updated.');
    }

    public function destroy(CalculatorType $calculatorType): RedirectResponse
    {
        if ($calculatorType->submissions()->exists()) {
            return redirect()->route('calculator-types.index')
                ->with('error', 'Cannot delete a calculator that has customer submissions.');
        }

        $calculatorType->delete();

        return redirect()->route('calculator-types.index')->with('success', 'Calculator deleted.');
    }
}
