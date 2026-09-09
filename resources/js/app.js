import './bootstrap';

import Alpine from 'alpinejs';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);
window.Chart = Chart;

window.productForm = function (config) {
    return {
        schemas: config.departmentSchemas || {},
        categoriesByDept: config.departmentCategories || {},
        departmentId: config.selectedDepartment,
        categoryId: config.selectedCategory,
        values: config.initialAttributes || {},

        get currentSchema() {
            return this.departmentId ? (this.schemas[this.departmentId] || []) : [];
        },

        get currentCategories() {
            return this.departmentId ? (this.categoriesByDept[this.departmentId] || []) : [];
        },
    };
};

window.purchaseOrderForm = function (config) {
    return {
        supplierId: config.selectedSupplier,
        showAllProducts: false,
        variants: config.variants || [],
        suppliers: config.suppliers || [],
        items: (config.initialItems && config.initialItems.length) ? config.initialItems : [{ product_variant_id: '', quantity_ordered: 1, unit_cost: 0 }],

        get supplierDepartmentIds() {
            const supplier = this.suppliers.find((s) => s.id === this.supplierId);
            return supplier ? supplier.department_ids : [];
        },

        get filteredVariants() {
            if (this.showAllProducts || this.supplierDepartmentIds.length === 0) {
                return this.variants;
            }

            return this.variants.filter((v) => this.supplierDepartmentIds.includes(v.department_id));
        },

        get total() {
            return this.items.reduce((sum, item) => sum + (Number(item.quantity_ordered) || 0) * (Number(item.unit_cost) || 0), 0);
        },

        addItem() {
            this.items.push({ product_variant_id: '', quantity_ordered: 1, unit_cost: 0 });
        },

        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },

        onProductChange(item) {
            const variant = this.variants.find((v) => v.id === Number(item.product_variant_id));
            if (variant) {
                item.unit_cost = variant.cost_price;
            }
        },
    };
};

window.stockTransferForm = function (config) {
    return {
        variants: config.variants || [],
        items: [{ product_variant_id: '', quantity: 1 }],

        addItem() {
            this.items.push({ product_variant_id: '', quantity: 1 });
        },

        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
    };
};

window.barcodeScanner = function () {
    return {
        open: false,
        starting: false,
        error: null,
        reader: null,

        async start() {
            this.open = true;
            this.starting = true;
            this.error = null;

            try {
                const { BrowserMultiFormatReader } = await import('@zxing/browser');
                this.reader = new BrowserMultiFormatReader();

                const devices = await BrowserMultiFormatReader.listVideoInputDevices();
                if (devices.length === 0) {
                    this.error = 'No camera found on this device. Use manual entry instead.';
                    this.starting = false;
                    return;
                }

                await this.reader.decodeFromVideoDevice(devices[0].deviceId, this.$refs.video, (result) => {
                    if (result) {
                        this.onDecode(result.getText());
                    }
                });

                this.starting = false;
            } catch (e) {
                this.error = 'Camera access failed — ' + (e && e.message ? e.message : 'unknown error') + '. Use manual entry instead.';
                this.starting = false;
            }
        },

        stop() {
            if (this.reader) {
                this.reader.reset();
                this.reader = null;
            }
            this.open = false;
        },

        onDecode(code) {
            this.stop();
            this.$dispatch('barcode-scanned', { code });
        },
    };
};

window.posTerminal = function (lookupUrl, canDiscount, vatRate) {
    return {
        items: [],
        code: '',
        canDiscount: canDiscount,
        vatRate: vatRate,
        error: null,

        async handleScan(code) {
            this.code = code;
            await this.lookup();
        },

        async lookup() {
            if (!this.code) return;

            const res = await fetch(lookupUrl + '?code=' + encodeURIComponent(this.code));
            const data = await res.json();

            if (!data.found) {
                this.error = 'No product found for code: ' + this.code;
                this.code = '';
                return;
            }

            this.error = null;
            const existing = this.items.find((i) => i.product_variant_id === data.product_variant_id);

            if (existing) {
                existing.quantity += 1;
            } else {
                this.items.push({
                    product_variant_id: data.product_variant_id,
                    name: data.name,
                    sku: data.sku,
                    quantity: 1,
                    unit_price: data.unit_price,
                    discount_amount: 0,
                    available: data.available,
                });
            }

            this.code = '';
        },

        remove(index) {
            this.items.splice(index, 1);
        },

        lineTotal(item) {
            const gross = (parseFloat(item.unit_price) || 0) * (parseInt(item.quantity) || 0);
            return Math.max(0, gross - (parseFloat(item.discount_amount) || 0));
        },

        money(value) {
            return Number(value || 0).toLocaleString(undefined, { maximumFractionDigits: 0 });
        },

        get grossSubtotal() {
            return this.items.reduce((sum, i) => sum + (parseFloat(i.unit_price) || 0) * (parseInt(i.quantity) || 0), 0);
        },
        get discountTotal() {
            return this.items.reduce((sum, i) => sum + (parseFloat(i.discount_amount) || 0), 0);
        },
        get netSubtotal() {
            return this.grossSubtotal - this.discountTotal;
        },
        get vat() {
            return this.netSubtotal * this.vatRate;
        },
        get total() {
            return this.netSubtotal + this.vat;
        },
    };
};

window.Alpine = Alpine;
Alpine.start();
