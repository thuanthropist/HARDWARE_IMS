<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CalculatorOutputProductMappingRequest;
use App\Models\CalculatorOutputProductMapping;
use App\Models\CalculatorType;
use Illuminate\Http\RedirectResponse;

class CalculatorOutputProductMappingController extends Controller
{
    public function store(CalculatorOutputProductMappingRequest $request, CalculatorType $calculatorType): RedirectResponse
    {
        $calculatorType->outputProductMappings()->create($request->validatedForModel());

        return redirect()->route('calculator-types.edit', $calculatorType)->with('success', 'Product mapping added.');
    }

    public function update(CalculatorOutputProductMappingRequest $request, CalculatorOutputProductMapping $calculatorOutputProductMapping): RedirectResponse
    {
        $calculatorOutputProductMapping->update($request->validatedForModel());

        return redirect()->route('calculator-types.edit', $calculatorOutputProductMapping->calculator_type_id)->with('success', 'Product mapping updated.');
    }

    public function destroy(CalculatorOutputProductMapping $calculatorOutputProductMapping): RedirectResponse
    {
        $calculatorTypeId = $calculatorOutputProductMapping->calculator_type_id;
        $calculatorOutputProductMapping->delete();

        return redirect()->route('calculator-types.edit', $calculatorTypeId)->with('success', 'Product mapping removed.');
    }
}
