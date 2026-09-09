<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CalculatorFormulaRequest;
use App\Models\CalculatorFormula;
use App\Models\CalculatorType;
use App\Services\CalculatorEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class CalculatorFormulaController extends Controller
{
    public function store(CalculatorFormulaRequest $request, CalculatorType $calculatorType): RedirectResponse
    {
        $calculatorType->formulas()->create($request->validatedForModel());

        return redirect()->route('calculator-types.edit', $calculatorType)->with('success', 'Formula added.');
    }

    public function update(CalculatorFormulaRequest $request, CalculatorFormula $calculatorFormula): RedirectResponse
    {
        $calculatorFormula->update($request->validatedForModel());

        return redirect()->route('calculator-types.edit', $calculatorFormula->calculator_type_id)->with('success', 'Formula updated.');
    }

    public function destroy(CalculatorFormula $calculatorFormula): RedirectResponse
    {
        $calculatorTypeId = $calculatorFormula->calculator_type_id;
        $calculatorFormula->delete();

        return redirect()->route('calculator-types.edit', $calculatorTypeId)->with('success', 'Formula removed.');
    }

    /**
     * Test the calculator's formulas against sample input without saving anything.
     * The "formulas" payload reflects the browser's current (possibly unsaved) draft
     * of the formula list, so an admin can tweak an expression and see the effect
     * before persisting it.
     */
    public function test(Request $request, CalculatorType $calculatorType, CalculatorEngine $engine): JsonResponse
    {
        $calculatorType->loadMissing('inputFields', 'formulas');

        // If the request supplies a draft formula list (an in-progress, unsaved edit),
        // use that instead of what's persisted — lets an admin test a tweak before saving it.
        if ($request->has('formulas')) {
            $draftFormulas = collect($request->input('formulas', []))
                ->map(fn (array $formula, int $index) => new CalculatorFormula([
                    'calculator_type_id' => $calculatorType->id,
                    'output_key' => $formula['output_key'] ?? "output_{$index}",
                    'label' => $formula['label'] ?? '',
                    'formula_expression' => $formula['formula_expression'] ?? '',
                    'unit' => $formula['unit'] ?? null,
                    'sort_order' => (int) ($formula['sort_order'] ?? $index),
                ]))
                ->sortBy('sort_order')
                ->values();

            $calculatorType = clone $calculatorType;
            $calculatorType->setRelation('formulas', $draftFormulas);
        }

        try {
            $output = $engine->calculate($calculatorType, $request->input('input_data', []));

            return response()->json(['success' => true, 'output' => $output]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }
}
