<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Label · {{ $variant->sku }}</title>
    <style>
        @page { size: 70mm 35mm; margin: 0; }
        body { font-family: -apple-system, Segoe UI, Roboto, sans-serif; margin: 0; padding: 0; }
        .label { width: 70mm; height: 35mm; padding: 3mm; box-sizing: border-box; text-align: center; }
        .name { font-size: 9px; font-weight: 600; margin: 0 0 1mm; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .barcode { width: 100%; height: 16mm; }
        .sku { font-size: 9px; letter-spacing: 1px; margin-top: 1mm; }
        .print-bar { text-align: center; padding: 16px; }
        @media print { .print-bar { display: none; } }
    </style>
</head>
<body>
    <div class="print-bar">
        <button onclick="window.print()">Print Label</button>
    </div>

    <div class="label">
        <p class="name">{{ $variant->product->name }}</p>
        <img class="barcode" src="{{ route('product-variants.barcode', $variant) }}" alt="{{ $variant->sku }}">
        <p class="sku">{{ $variant->sku }}</p>
    </div>
</body>
</html>
