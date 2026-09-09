<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CalculatorInputFieldRequest;
use App\Models\CalculatorInputField;
use App\Models\CalculatorType;
use Illuminate\Http\RedirectResponse;

class CalculatorInputFieldController extends Controller
{
    public function store(CalculatorInputFieldRequest $request, CalculatorType $calculatorType): RedirectResponse
    {
        $calculatorType->inputFields()->create($request->validatedForModel());

        return redirect()->route('calculator-types.edit', $calculatorType)->with('success', 'Input field added.');
    }

    public function update(CalculatorInputFieldRequest $request, CalculatorInputField $calculatorInputField): RedirectResponse
    {
        $calculatorInputField->update($request->validatedForModel());

        return redirect()->route('calculator-types.edit', $calculatorInputField->calculator_type_id)->with('success', 'Input field updated.');
    }

    public function destroy(CalculatorInputField $calculatorInputField): RedirectResponse
    {
        $calculatorTypeId = $calculatorInputField->calculator_type_id;
        $calculatorInputField->delete();

        return redirect()->route('calculator-types.edit', $calculatorTypeId)->with('success', 'Input field removed.');
    }
}
