<x-layouts.storefront title="Home">
    {{-- HERO --}}
    <section class="sf-hero">
        @if ($heroImages->isNotEmpty())
            <div class="sf-hero-slideshow" data-hero-slideshow>
                @foreach ($heroImages as $image)
                    <img src="{{ asset('storage/'.$image) }}" alt="" aria-hidden="true">
                @endforeach
            </div>
        @endif
        <div class="sf-hero-overlay"></div>

        <div class="container sf-hero-content text-center">
            <div class="sf-fade-up" style="animation-delay: 0ms;">
                <span class="sf-eyebrow mb-4">🎨 Paint Specialists · Building Materials · Plumbing · Electrical · Solar</span>
            </div>
            <h1 class="sf-fade-up text-white mb-3" style="animation-delay: 80ms;">
                Every colour, every finish,<br class="d-none d-md-block"> plus the numbers to back it up.
            </h1>
            <p class="sf-fade-up text-white-50 fs-5 mx-auto mb-4" style="max-width: 620px; animation-delay: 160ms;">
                Your paint specialists for interior, exterior, wood & metal finishes — plus a full hardware catalog
                and free planning calculators to get a real, priced material list before you buy a single item.
            </p>
            <div class="sf-fade-up d-flex flex-wrap justify-content-center gap-3" style="animation-delay: 240ms;">
                <a href="#departments" class="btn btn-primary btn-lg">Shop by Department</a>
                <a href="#tools" class="btn btn-pill-outline btn-lg">Try a Planning Tool</a>
            </div>
        </div>
    </section>

    {{-- STATS --}}
    <div class="container sf-stats-row">
        <div class="row g-3">
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="0">
                <div class="sf-glass-card">
                    <div class="sf-stat-value">{{ $stats['departments'] }}</div>
                    <div class="sf-stat-label">Departments</div>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="80">
                <div class="sf-glass-card">
                    <div class="sf-stat-value">{{ $stats['products'] }}+</div>
                    <div class="sf-stat-label">Products in Stock</div>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="160">
                <div class="sf-glass-card">
                    <div class="sf-stat-value">{{ $stats['calculators'] }}</div>
                    <div class="sf-stat-label">Planning Calculators</div>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="240">
                <div class="sf-glass-card">
                    <div class="sf-stat-value">{{ (int) round(setting('tax.vat_rate', 0.18) * 100) }}%</div>
                    <div class="sf-stat-label">VAT-Pricing</div>
                </div>
            </div>
        </div>
    </div>

    {{-- FEATURED PAINTS --}}
    @if ($featuredPaints->isNotEmpty())
        <section class="sf-section" id="paints">
            <div class="container">
                <div class="rounded-4 p-4 p-md-5" style="background: linear-gradient(135deg, var(--sf-paints), #4c1d95);">
                    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4" data-aos="fade-up">
                        <div>
                            <span class="sf-kicker text-white-50">Our Speciality</span>
                            <h2 class="text-white fw-bold mt-2 mb-2">Every colour of construction paint</h2>
                            <p class="text-white-50 mb-0" style="max-width: 520px;">Interior, exterior, wood & metal finishes — water-based, oil-based, and solvent-based, in stock and ready to mix.</p>
                        </div>
                        <a href="{{ route('storefront.departments.show', 'paints') }}" class="btn btn-light fw-semibold flex-shrink-0">Shop All Paints &rarr;</a>
                    </div>
                    <div class="row g-4">
                        @foreach ($featuredPaints as $index => $product)
                            <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="{{ $index * 80 }}">
                                <x-storefront.product-card :product="$product" />
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- DEPARTMENTS --}}
    <section class="sf-section pt-0" id="departments">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <span class="sf-kicker">Shop by Department</span>
                <h2 class="sf-section-title mt-2 mb-2">{{ $departments->count() }} departments, one store</h2>
                <p class="sf-section-subtitle mx-auto">Whatever the job needs, it's organized so you can find it fast.</p>
            </div>
            @php
                $deptColClass = match (true) {
                    $departments->count() === 1 => 'col-sm-8 col-md-6 col-lg-5 col-xl-4',
                    $departments->count() === 2 => 'col-md-6 col-lg-5',
                    $departments->count() === 3 => 'col-md-6 col-lg-4',
                    default => 'col-md-6 col-lg-3',
                };
            @endphp
            <div class="row g-4 justify-content-center">
                @foreach ($departments as $index => $department)
                    <div class="{{ $deptColClass }}" data-aos="fade-up" data-aos-delay="{{ $index * 80 }}">
                        <a href="{{ route('storefront.departments.show', $department->slug) }}" class="sf-dept-card d-block text-decoration-none"
                           style="{{ $department->image_path
                                ? 'background: linear-gradient(160deg, rgba(17,20,28,.15), rgba(17,20,28,.88)), url(\''.asset('storage/'.$department->image_path).'\') center / cover;'
                                : '--sf-card-color: var('.$department->colorVar.');' }}">
                            <div class="sf-dept-body">
                                <div class="sf-dept-icon fs-4">{{ $department->emoji }}</div>
                                <h5 class="text-white fw-bold mb-1">{{ $department->name }}</h5>
                                <p class="text-white-50 small mb-2">{{ $department->products_count }} products</p>
                                <span class="text-white small fw-semibold">Browse &rarr;</span>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- PLANNING TOOLS --}}
    <section class="sf-section bg-white" id="tools">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <span class="sf-kicker">Smart Planning Tools</span>
                <h2 class="sf-section-title mt-2 mb-2">Not sure how much you need?</h2>
                <p class="sf-section-subtitle mx-auto">Answer a few questions and get a real, priced material list — matched to what's actually in stock.</p>
            </div>
            @php
                $toolMeta = [
                    'wiring-consultant' => '🔌',
                    'solar-calculator' => '☀️',
                    'plumbing-calculator' => '🚰',
                    'building-calculator' => '🧱',
                    'paint-calculator' => '🎨',
                ];
                $toolColClass = match (true) {
                    $calculatorTypes->count() === 1 => 'col-sm-8 col-md-6 col-lg-5 col-xl-4',
                    $calculatorTypes->count() === 2 => 'col-md-6 col-lg-5',
                    $calculatorTypes->count() === 3 => 'col-md-6 col-lg-4',
                    default => 'col-md-6 col-lg-3',
                };
            @endphp
            <div class="row g-4 justify-content-center">
                @foreach ($calculatorTypes as $index => $tool)
                    <div class="{{ $toolColClass }}" data-aos="fade-up" data-aos-delay="{{ $index * 80 }}">
                        <div class="sf-tool-card">
                            <div class="sf-tool-icon fs-4">{{ $toolMeta[$tool->key] ?? '🧮' }}</div>
                            <h5 class="fw-bold mb-2">{{ $tool->name }}</h5>
                            <p class="text-body-secondary small mb-3">{{ $tool->description }}</p>
                            <a href="{{ route('storefront.tools.show', $tool->key) }}" class="btn btn-outline-primary btn-sm">Start Calculating</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="sf-section pt-0">
        <div class="container">
            <div class="rounded-4 p-5 text-center text-white" style="background: linear-gradient(135deg, var(--sf-dark), var(--sf-primary-dark));" data-aos="zoom-in">
                <h3 class="fw-bold mb-2">Have a bigger project in mind?</h3>
                <p class="text-white-50 mb-4">Request a professional quote and our team will help you plan it out.</p>
                <a href="{{ route('storefront.quotes.create') }}" class="btn btn-light btn-lg">Request a Quote</a>
            </div>
        </div>
    </section>

    @if ($heroImages->isNotEmpty())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const slideshow = document.querySelector('[data-hero-slideshow]');
                const images = slideshow ? Array.from(slideshow.querySelectorAll('img')) : [];
                if (images.length === 0) return;

                let currentIndex = 0;
                images[currentIndex].classList.add('is-active');

                if (images.length > 1) {
                    setInterval(() => {
                        let nextIndex = Math.floor(Math.random() * images.length);
                        if (nextIndex === currentIndex) {
                            nextIndex = (nextIndex + 1) % images.length;
                        }
                        images[currentIndex].classList.remove('is-active');
                        images[nextIndex].classList.add('is-active');
                        currentIndex = nextIndex;
                    }, 5000);
                }
            });
        </script>
    @endif
</x-layouts.storefront>
