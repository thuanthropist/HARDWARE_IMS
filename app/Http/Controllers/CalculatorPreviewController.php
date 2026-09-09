<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CalculatorType;
use App\Services\CalculatorEngine;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class CalculatorPreviewController extends Controller
{
    public function index(): View
    {
        $calculatorTypes = CalculatorType::with('department')->orderBy('name')->get();

        return view('calculator-preview.index', ['calculatorTypes' => $calculatorTypes]);
    }

    public function show(CalculatorType $calculatorType): View
    {
        $calculatorType->load('inputFields');

        return view('calculator-preview.show', [
            'calculatorType' => $calculatorType,
            'values' => $calculatorType->defaultInputData(),
            'result' => null,
            'error' => null,
        ]);
    }

    public function run(Request $request, CalculatorType $calculatorType, CalculatorEngine $engine): View
    {
        $calculatorType->load(['inputFields', 'formulas', 'outputProductMappings']);

        $inputData = $request->input('input_data', []);
        $result = null;
        $error = null;

        try {
            $result = $engine->preview($calculatorType, $inputData);
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }

        return view('calculator-preview.show', [
            'calculatorType' => $calculatorType,
            'values' => $inputData,
            'result' => $result,
            'error' => $error,
        ]);
    }
}
