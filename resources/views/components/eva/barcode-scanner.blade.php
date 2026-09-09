@props(['label' => 'Scan Barcode'])

<div x-data="barcodeScanner()">
    <button type="button" @click="start()" {{ $attributes->class(['inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50']) }}>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7V5a1 1 0 011-1h2M4 17v2a1 1 0 001 1h2m10-14h2a1 1 0 011 1v2m-4 12h2a1 1 0 001-1v-2M8 8v8m3-8v8m3-8v8m3-8v8" /></svg>
        {{ $label }}
    </button>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-4" @keydown.escape.window="stop()">
        <div @click.outside="stop()" class="w-full max-w-sm rounded-xl bg-white p-5 shadow-xl">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-base font-semibold text-slate-900">Scan Barcode</h3>
                <button type="button" @click="stop()" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <div x-show="!error" class="overflow-hidden rounded-lg bg-black">
                <video x-ref="video" class="w-full" autoplay muted playsinline></video>
            </div>

            <p x-show="starting" class="mt-2 text-xs text-slate-400">Requesting camera access…</p>
            <p x-show="error" x-text="error" class="mt-2 text-sm text-red-600"></p>

            <div class="mt-4 border-t border-slate-100 pt-4">
                <label class="mb-1 block text-xs font-medium text-slate-600">Or enter code manually</label>
                <div class="flex gap-2">
                    <input type="text" x-ref="manual" @keydown.enter.prevent="onDecode($refs.manual.value)"
                           placeholder="SKU or barcode"
                           class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500">
                    <button type="button" @click="onDecode($refs.manual.value)" class="rounded-lg bg-amber-500 px-3 py-2 text-sm font-medium text-white hover:bg-amber-600">Go</button>
                </div>
            </div>
        </div>
    </div>
</div>
