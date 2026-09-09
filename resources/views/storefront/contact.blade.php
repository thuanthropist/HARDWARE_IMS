<x-layouts.storefront title="Contact Us">
    <section class="text-white" style="background: linear-gradient(135deg, var(--sf-dark), var(--sf-primary-dark)); padding-block: 8rem 4rem;">
        <div class="container text-center sf-fade-up">
            <span class="sf-eyebrow mb-3">Get in Touch</span>
            <h1 class="fw-bold mb-2" style="font-size: 2rem;">We're Here to Help</h1>
            <p class="text-white-50 mb-0" style="max-width: 560px; margin-inline: auto;">
                Questions about a product, a bulk order, or a planning tool result? Reach out — our team responds fast.
            </p>
        </div>
    </section>

    <section class="sf-section pt-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up">
                    <div class="card border-0 shadow-sm rounded-4 p-4 h-100 text-center">
                        <div class="sf-tool-icon mx-auto">📍</div>
                        <h6 class="fw-bold mb-1">Visit Us</h6>
                        <p class="small text-body-secondary mb-0">{{ setting('whatsapp.contact_address') ?: setting('business.address', 'Kariakoo, Dar es Salaam, Tanzania') }}</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="80">
                    <div class="card border-0 shadow-sm rounded-4 p-4 h-100 text-center">
                        <div class="sf-tool-icon mx-auto">📞</div>
                        <h6 class="fw-bold mb-1">Call Us</h6>
                        <p class="small text-body-secondary mb-0">{{ setting('whatsapp.contact_phone') ?: setting('business.phone', '+255 22 286 1000') }}</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="160">
                    <div class="card border-0 shadow-sm rounded-4 p-4 h-100 text-center">
                        <div class="sf-tool-icon mx-auto">✉️</div>
                        <h6 class="fw-bold mb-1">Email Us</h6>
                        <p class="small text-body-secondary mb-0">{{ setting('whatsapp.contact_email') ?: setting('business.email', 'info@hardwareims.example') }}</p>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center mt-4">
                <div class="col-md-8 text-center" data-aos="fade-up">
                    <div class="card border-0 rounded-4 p-5" style="background: color-mix(in srgb, var(--sf-primary) 8%, white);">
                        <h5 class="fw-bold mb-2">Fastest way to reach us? WhatsApp.</h5>
                        <p class="text-body-secondary mb-4">Chat with our team directly for quick answers on stock, pricing and delivery.</p>
                        <x-storefront.whatsapp-link
                            class="btn btn-success btn-lg mx-auto"
                            message="Hi! I have a question about a product or order."
                            style="width: fit-content;"
                        >
                            Chat on WhatsApp
                        </x-storefront.whatsapp-link>
                    </div>
                </div>
            </div>

            @if ($departments->isNotEmpty())
                <div class="text-center mt-5" data-aos="fade-up">
                    <h6 class="fw-bold mb-3">Or browse by department</h6>
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        @foreach ($departments as $department)
                            <a href="{{ route('storefront.departments.show', $department->slug) }}" class="btn btn-outline-primary btn-sm">
                                {{ $department->emoji }} {{ $department->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
</x-layouts.storefront>
