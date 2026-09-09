<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\CalculatorType;
use App\Services\CalculatorEngine;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class ToolController extends Controller
{
    public function show(CalculatorType $calculatorType): View
    {
        abort_unless($calculatorType->is_active, 404);

        $calculatorType->load('inputFields');

        return view('storefront.tools.show', [
            'calculatorType' => $calculatorType,
            'values' => $calculatorType->defaultInputData(),
            'submission' => null,
            'error' => null,
        ]);
    }

    public function calculate(Request $request, CalculatorType $calculatorType, CalculatorEngine $engine): View
    {
        abort_unless($calculatorType->is_active, 404);

        $calculatorType->load(['inputFields', 'formulas', 'outputProductMappings']);

        $inputData = (array) $request->input('input_data', []);
        $submission = null;
        $error = null;

        try {
            $submission = $engine->run($calculatorType, $inputData, auth('customer')->id());
        } catch (Throwable) {
            $error = 'We could not calculate your results with those inputs. Please check the values and try again.';
        }

        return view('storefront.tools.show', [
            'calculatorType' => $calculatorType,
            'values' => $inputData,
            'submission' => $submission,
            'error' => $error,
        ]);
    }
}
