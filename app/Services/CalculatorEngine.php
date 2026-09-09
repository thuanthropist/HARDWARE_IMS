<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CalculatorOutputProductMapping;
use App\Models\CalculatorSubmission;
use App\Models\CalculatorType;
use App\Models\Product;
use Illuminate\Support\Collection;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Evaluates a calculator type's formulas against customer input, then matches each
 * output to a real catalog product and prices it. Formulas are admin-editable
 * expressions (Symfony ExpressionLanguage), never raw PHP — see registerFunctions()
 * for the full list of available helper functions, and README notes in the admin
 * formula screen for the variable-naming convention.
 */
class CalculatorEngine
{
    private ExpressionLanguage $expressionLanguage;

    public function __construct()
    {
        $this->expressionLanguage = new ExpressionLanguage();
        $this->registerFunctions();
    }

    /**
     * Evaluate every formula for a calculator type against the given input, in
     * sort_order sequence. Each formula's result is added to the variable context
     * under its own output_key, so later formulas can reference earlier ones
     * (e.g. a "breaker_count" formula can use the "total_circuits" formula's result).
     *
     * @return array<string, array{label: string, quantity: float|string, unit: ?string}>
     */
    public function calculate(CalculatorType $calculatorType, array $inputData): array
    {
        $variables = $this->buildVariables($calculatorType, $inputData);
        $output = [];

        foreach ($calculatorType->formulas as $formula) {
            $result = $this->expressionLanguage->evaluate($formula->formula_expression, $variables);
            $result = is_numeric($result) ? round((float) $result, 2) : $result;

            $output[$formula->output_key] = [
                'label' => $formula->label,
                'quantity' => $result,
                'unit' => $formula->unit,
            ];

            $variables[$formula->output_key] = $result;
        }

        return $output;
    }

    /**
     * Match each computed output to a real product and price it. Outputs with no
     * mapping configured are left as informational-only (e.g. estimated labor days).
     * Outputs with a mapping but no matching product are flagged so the caller can
     * show "material needed — contact us" instead of breaking the result.
     *
     * @param  array<string, array{label: string, quantity: float|string, unit: ?string}>  $computedOutput
     * @return array{items: array, estimated_total: float}
     */
    public function priceOutput(CalculatorType $calculatorType, array $computedOutput): array
    {
        $mappings = $calculatorType->outputProductMappings->keyBy('output_key');
        $total = 0.0;

        foreach ($computedOutput as $key => &$row) {
            $mapping = $mappings->get($key);

            if (! $mapping) {
                $row['status'] = 'informational';
                $row['product'] = null;
                $row['line_total'] = null;

                continue;
            }

            $product = $this->findMatchingProduct($mapping);

            if (! $product) {
                $row['status'] = 'no_product_found';
                $row['product'] = null;
                $row['line_total'] = null;

                continue;
            }

            $unitPrice = (float) $product->selling_price;
            $quantity = is_numeric($row['quantity']) ? (float) $row['quantity'] : 0.0;
            $lineTotal = round($unitPrice * $quantity, 2);

            $row['status'] = 'matched';
            $row['product'] = [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'unit_price' => $unitPrice,
                'in_stock' => $product->currentStock > 0,
            ];
            $row['line_total'] = $lineTotal;

            $total += $lineTotal;
        }
        unset($row);

        return ['items' => $computedOutput, 'estimated_total' => round($total, 2)];
    }

    /**
     * calculate() + priceOutput() without persisting — used by the admin Preview
     * screen and can be reused by Phase 4's storefront for a "quote before you submit"
     * step.
     */
    public function preview(CalculatorType $calculatorType, array $inputData): array
    {
        return $this->priceOutput($calculatorType, $this->calculate($calculatorType, $inputData));
    }

    /**
     * Full pipeline, persisted as a CalculatorSubmission for analytics and later
     * cart/quote conversion.
     */
    public function run(CalculatorType $calculatorType, array $inputData, ?int $customerId = null): CalculatorSubmission
    {
        $priced = $this->preview($calculatorType, $inputData);

        return CalculatorSubmission::create([
            'calculator_type_id' => $calculatorType->id,
            'customer_id' => $customerId,
            'input_data' => $inputData,
            'computed_output' => $priced['items'],
            'estimated_total' => $priced['estimated_total'],
        ]);
    }

    /**
     * Flatten a submission's matched outputs into cart-ready line items. Phase 4/5
     * call this rather than re-deriving cart lines from computed_output themselves.
     */
    public function toCartItems(CalculatorSubmission $submission): array
    {
        $items = [];

        foreach ($submission->computed_output as $row) {
            if (($row['status'] ?? null) === 'matched' && ! empty($row['product'])) {
                $items[] = [
                    'product_id' => $row['product']['id'],
                    'sku' => $row['product']['sku'],
                    'name' => $row['product']['name'],
                    'quantity' => $row['quantity'],
                    'unit' => $row['unit'] ?? null,
                    'unit_price' => $row['product']['unit_price'],
                    'line_total' => $row['line_total'],
                ];
            }
        }

        return $items;
    }

    private function buildVariables(CalculatorType $calculatorType, array $inputData): array
    {
        $variables = [];

        foreach ($calculatorType->inputFields as $field) {
            $value = $inputData[$field->field_key] ?? null;

            $variables[$field->field_key] = match ($field->input_type) {
                'repeater' => is_array($value) ? $value : [],
                'text' => (string) ($value ?? ''),
                default => is_numeric($value) ? (float) $value : (string) ($value ?? ''),
            };
        }

        return $variables;
    }

    private function findMatchingProduct(CalculatorOutputProductMapping $mapping): ?Product
    {
        $filters = $mapping->product_attribute_filters;

        $query = Product::query()->active();

        if (! empty($filters['department'])) {
            $query->whereHas('department', fn ($q) => $q->where('slug', $filters['department']));
        }

        if (! empty($filters['category'])) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $filters['category']));
        }

        if (! empty($filters['attributes']) && is_array($filters['attributes'])) {
            foreach ($filters['attributes'] as $attributeKey => $attributeValue) {
                $query->whereHas('productAttributes', fn ($q) => $q
                    ->where('attribute_key', $attributeKey)
                    ->where('attribute_value', $attributeValue));
            }
        }

        $products = $query->get();

        if ($products->isEmpty()) {
            return null;
        }

        return $this->pickBestMatch($products, $mapping->selection_strategy);
    }

    private function pickBestMatch(Collection $products, string $strategy): ?Product
    {
        return match ($strategy) {
            'cheapest' => $products->sortBy('selling_price')->first(),
            'highest_stock' => $products->sortByDesc(fn (Product $p) => $p->currentStock)->first(),
            'cheapest_in_stock' => $products->filter(fn (Product $p) => $p->currentStock > 0)->sortBy('selling_price')->first()
                ?? $products->sortBy('selling_price')->first(),
            default => $products->first(),
        };
    }

    /**
     * Custom functions available to every formula_expression, on top of standard
     * arithmetic (+ - * / %) and comparison/ternary operators that ExpressionLanguage
     * already supports:
     *
     *   ceil(x), floor(x), round(x, precision = 0), max(a, b), min(a, b)
     *   count(repeaterFieldKey)                     — number of rows in a repeater field
     *   sum(repeaterFieldKey, "subFieldKey")         — sum of one sub-field across all rows
     *   sum_product(repeaterFieldKey, "a", "b")      — sum of subField-a * subField-b across all rows
     *                                                   (e.g. total Wh = sum_product(appliances, "wattage", "hours_per_day"))
     */
    private function registerFunctions(): void
    {
        $this->expressionLanguage->register(
            'ceil',
            fn ($v) => sprintf('ceil(%s)', $v),
            fn ($values, $v) => (float) ceil((float) $v)
        );

        $this->expressionLanguage->register(
            'floor',
            fn ($v) => sprintf('floor(%s)', $v),
            fn ($values, $v) => (float) floor((float) $v)
        );

        $this->expressionLanguage->register(
            'round',
            fn ($v, $p = '0') => sprintf('round(%s, %s)', $v, $p),
            fn ($values, $v, $p = 0) => (float) round((float) $v, (int) $p)
        );

        $this->expressionLanguage->register(
            'max',
            fn ($a, $b) => sprintf('max(%s, %s)', $a, $b),
            fn ($values, $a, $b) => max((float) $a, (float) $b)
        );

        $this->expressionLanguage->register(
            'min',
            fn ($a, $b) => sprintf('min(%s, %s)', $a, $b),
            fn ($values, $a, $b) => min((float) $a, (float) $b)
        );

        $this->expressionLanguage->register(
            'count',
            fn ($arr) => sprintf('count(%s)', $arr),
            fn ($values, $arr) => is_array($arr) ? count($arr) : 0
        );

        $this->expressionLanguage->register(
            'sum',
            fn ($arr, $key) => sprintf('sum(%s, %s)', $arr, $key),
            function ($values, $arr, $key) {
                $arr = is_array($arr) ? $arr : [];

                return array_sum(array_map(fn ($item) => (float) ($item[$key] ?? 0), $arr));
            }
        );

        $this->expressionLanguage->register(
            'sum_product',
            fn ($arr, $key1, $key2) => sprintf('sum_product(%s, %s, %s)', $arr, $key1, $key2),
            function ($values, $arr, $key1, $key2) {
                $arr = is_array($arr) ? $arr : [];

                return array_sum(array_map(
                    fn ($item) => (float) ($item[$key1] ?? 0) * (float) ($item[$key2] ?? 0),
                    $arr
                ));
            }
        );
    }
}
