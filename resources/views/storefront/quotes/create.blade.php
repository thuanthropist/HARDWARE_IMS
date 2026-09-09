<x-layouts.storefront title="Request a Quote">
    <div class="container" style="padding-top: 7.5rem; max-width: 720px;">
        <div class="text-center mb-4" data-aos="fade-up">
            <h1 class="fw-bold mb-2">Request a Professional Quote</h1>
            <p class="text-body-secondary">Tell us about your project and our team will get back to you with pricing and availability.</p>
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-4" data-aos="fade-up" data-aos-delay="100">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('storefront.quotes.store') }}"
                x-data="{ items: @js($items) }"
            >
                @csrf
                <input type="hidden" name="calculator_submission_id" value="{{ $calculatorSubmission?->id }}">

                @if ($calculatorSubmission)
                    <div class="alert alert-light border small mb-3">
                        Based on your <strong>{{ $calculatorSubmission->calculatorType->name }}</strong> results
                        (est. {{ number_format((float) $calculatorSubmission->estimated_total) }} TZS).
                    </div>
                @endif

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $customer?->name) }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Email</label>
                        <input type="email" name="email" value="{{ old('email', $customer?->email) }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $customer?->phone) }}" class="form-control" placeholder="+255 7XX XXX XXX">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Preferred Contact Method</label>
                        <select name="preferred_contact_method" class="form-select">
                            <option value="phone">Phone Call</option>
                            <option value="email">Email</option>
                            <option value="whatsapp">WhatsApp</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Project Timeline</label>
                        <input type="text" name="timeline" value="{{ old('timeline') }}" class="form-control" placeholder="e.g. Within 2 weeks">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Project Description</label>
                    <textarea name="project_description" rows="4" class="form-control" placeholder="Tell us more about what you're building...">{{ old('project_description') }}</textarea>
                </div>

                <template x-if="items.length > 0">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Items for this Quote</label>
                        <div class="border rounded-3 p-3">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="d-flex align-items-center gap-2 py-2 border-bottom">
                                    <input type="hidden" :name="'items[' + index + '][product_id]'" :value="item.product_id">
                                    <span class="flex-grow-1 small" x-text="item.name"></span>
                                    <input type="number" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" min="1" class="form-control form-control-sm" style="width: 80px;">
                                    <button type="button" @click="items.splice(index, 1)" class="btn btn-sm btn-outline-danger">&times;</button>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <button type="submit" class="btn btn-primary w-100">Submit Quote Request</button>
            </form>
        </div>
    </div>
</x-layouts.storefront>
