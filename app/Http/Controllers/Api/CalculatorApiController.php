<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CalculatorSubmission;
use App\Models\CalculatorType;
use App\Services\CalculatorEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

/**
 * Public, unauthenticated API backing Phase 4's storefront calculator pages.
 * Never exposes formula_expression or product_attribute_filters — only what a
 * customer-facing form/result page needs.
 */
class CalculatorApiController extends Controller
{
    public function index(): JsonResponse
    {
        $calculatorTypes = CalculatorType::active()->with('department')->orderBy('name')->get();

        return response()->json([
            'data' => $calculatorTypes->map(fn (CalculatorType $type) => $this->summarize($type)),
        ]);
    }

    public function show(CalculatorType $calculatorType): JsonResponse
    {
        abort_unless($calculatorType->is_active, 404);

        $calculatorType->load('inputFields');

        return response()->json(['data' => [
            ...$this->summarize($calculatorType),
            'input_fields' => $calculatorType->inputFields->map(fn ($field) => [
                'field_key' => $field->field_key,
                'label' => $field->label,
                'input_type' => $field->input_type,
                'unit' => $field->unit,
                'is_required' => $field->is_required,
                'options' => $field->options,
            ]),
        ]]);
    }

    public function calculate(Request $request, CalculatorType $calculatorType, CalculatorEngine $engine): JsonResponse
    {
        abort_unless($calculatorType->is_active, 404);

        $calculatorType->load(['inputFields', 'formulas', 'outputProductMappings']);

        $validator = Validator::make($request->all(), $this->validationRules($calculatorType));

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // customer_id is intentionally always null here — there's no customer auth
            // system yet. Once Phase 4 adds one, pass the authenticated customer's id
            // server-side (never trust a customer_id from the request body).
            $submission = $engine->run($calculatorType, $request->input('input_data', []), null);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Could not compute this calculator: '.$e->getMessage()], 422);
        }

        return response()->json([
            'data' => [
                'submission_id' => $submission->id,
                'items' => $submission->computed_output,
                'estimated_total' => (float) $submission->estimated_total,
            ],
        ], 201);
    }

    public function cartItems(CalculatorSubmission $calculatorSubmission, CalculatorEngine $engine): JsonResponse
    {
        $items = $engine->toCartItems($calculatorSubmission);

        $calculatorSubmission->update(['converted_to_cart' => true]);

        return response()->json([
            'data' => [
                'submission_id' => $calculatorSubmission->id,
                'items' => $items,
            ],
        ]);
    }

    private function summarize(CalculatorType $type): array
    {
        return [
            'key' => $type->key,
            'name' => $type->name,
            'department' => $type->department->name,
            'description' => $type->description,
        ];
    }

    private function validationRules(CalculatorType $calculatorType): array
    {
        $rules = [];

        foreach ($calculatorType->inputFields as $field) {
            $key = "input_data.{$field->field_key}";
            $fieldRules = [$field->is_required ? 'required' : 'nullable'];

            $fieldRules[] = match ($field->input_type) {
                'number' => 'numeric',
                'repeater' => 'array',
                'select', 'radio' => Rule::in(array_keys($field->choices())),
                default => 'string',
            };

            if ($field->input_type === 'repeater' && $field->is_required) {
                $fieldRules[] = 'min:1';
            }

            $rules[$key] = $fieldRules;
        }

        return $rules;
    }
}
