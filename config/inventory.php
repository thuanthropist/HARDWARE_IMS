<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Stock Adjustment Approval Threshold
    |--------------------------------------------------------------------------
    |
    | Adjustments at or below both thresholds are auto-approved immediately.
    | Adjustments exceeding either threshold are held as "pending" until a
    | user with the manage-stock-adjustments permission approves them.
    |
    */

    'stock_adjustment_threshold_quantity' => (int) env('STOCK_ADJUSTMENT_THRESHOLD_QTY', 50),

    'stock_adjustment_threshold_value' => (float) env('STOCK_ADJUSTMENT_THRESHOLD_VALUE', 500000),

    /*
    |--------------------------------------------------------------------------
    | Low Stock Alerts
    |--------------------------------------------------------------------------
    */

    'expiry_warning_days' => (int) env('EXPIRY_WARNING_DAYS', 30),

];
