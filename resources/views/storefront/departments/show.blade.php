<x-layouts.storefront :title="$department->name">
    {{-- DEPARTMENT HEADER --}}
    <section class="text-white" style="{{ $department->image_path
            ? 'background: linear-gradient(135deg, rgba(17,20,28,.75), rgba(17,20,28,.55)), url(\''.asset('storage/'.$department->image_path).'\') center / cover; padding-block: 8rem 3rem;'
            : 'background: linear-gradient(135deg, var(--sf-dark), var('.$department->colorVar.')); padding-block: 8rem 3rem;' }}">
        <div class="container">
            <div class="d-flex align-items-center gap-3 sf-fade-up">
                <div class="sf-tool-icon" style="background: rgba(255,255,255,0.15); color: #fff; width: 3.5rem; height: 3.5rem; font-size: 1.5rem;">
                    {{ $department->emoji }}
                </div>
                <div>
                    <h1 class="fw-bold mb-1" style="font-size: 2rem;">{{ $department->name }}</h1>
                    <p class="text-white-50 mb-0">{{ $department->description }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="sf-section pt-4">
        <div class="container">
            <div class="row g-4">
                {{-- FILTER SIDEBAR --}}
                <div class="col-lg-3">
                    @php $filtersActive = $filters['q'] || $filters['category'] || array_filter($filters['attrs']); @endphp

                    <button type="button" class="btn btn-outline-secondary w-100 d-lg-none mb-3 d-flex align-items-center justify-content-center gap-2"
                            data-bs-toggle="collapse" data-bs-target="#departmentFilters" aria-expanded="false" aria-controls="departmentFilters">
                        <span>Filters</span>
                        @if ($filtersActive)
                            <span class="badge rounded-pill text-bg-primary">Active</span>
                        @endif
                    </button>

                    <div class="collapse d-lg-block" id="departmentFilters">
                        <form method="GET" action="{{ route('storefront.departments.show', $department->slug) }}" class="card border-0 shadow-sm rounded-4 p-3" data-aos="fade-up">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Search</label>
                                <input type="text" name="q" value="{{ $filters['q'] }}" class="form-control form-control-sm" placeholder="Product name or SKU">
                            </div>

                            @if ($categories->isNotEmpty())
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Category</label>
                                    <select name="category" class="form-select form-select-sm">
                                        <option value="">All categories</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->slug }}" @selected($filters['category'] === $category->slug)>
                                                {{ $category->name }} ({{ $category->products_count }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @foreach ($schemas as $schema)
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">
                                        {{ $schema->label }}@if ($schema->unit) <span class="text-body-secondary">({{ $schema->unit }})</span>@endif
                                    </label>

                                    @if ($schema->input_type === 'select')
                                        @php $selected = (array) ($filters['attrs'][$schema->attribute_key] ?? []); @endphp
                                        <div class="d-flex flex-column gap-1">
                                            @foreach ($schema->options ?? [] as $option)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="attrs[{{ $schema->attribute_key }}][]" value="{{ $option }}" id="attr-{{ $schema->attribute_key }}-{{ $loop->index }}" @checked(in_array($option, $selected, true))>
                                                    <label class="form-check-label small" for="attr-{{ $schema->attribute_key }}-{{ $loop->index }}">{{ $option }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    @elseif ($schema->input_type === 'number')
                                        @php $range = (array) ($filters['attrs'][$schema->attribute_key] ?? []); @endphp
                                        <div class="d-flex gap-2">
                                            <input type="number" name="attrs[{{ $schema->attribute_key }}][min]" value="{{ $range['min'] ?? '' }}" class="form-control form-control-sm" placeholder="Min">
                                            <input type="number" name="attrs[{{ $schema->attribute_key }}][max]" value="{{ $range['max'] ?? '' }}" class="form-control form-control-sm" placeholder="Max">
                                        </div>
                                    @else
                                        <input type="text" name="attrs[{{ $schema->attribute_key }}]" value="{{ $filters['attrs'][$schema->attribute_key] ?? '' }}" class="form-control form-control-sm">
                                    @endif
                                </div>
                            @endforeach

                            <button type="submit" class="btn btn-primary btn-sm w-100 mt-2">Apply Filters</button>
                            @if ($filtersActive)
                                <a href="{{ route('storefront.departments.show', $department->slug) }}" class="btn btn-link btn-sm w-100 mt-1">Clear all</a>
                            @endif
                        </form>
                    </div>
                </div>

                {{-- PRODUCT GRID --}}
                <div class="col-lg-9">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" data-aos="fade-up">
                        <p class="text-body-secondary mb-0">{{ $products->total() }} product{{ $products->total() === 1 ? '' : 's' }} found</p>
                        <form method="GET" action="{{ route('storefront.departments.show', $department->slug) }}" class="d-flex align-items-center gap-2">
                            @foreach (request()->except(['sort', 'page']) as $key => $value)
                                @if (is_array($value))
                                    @foreach ($value as $subKey => $subValue)
                                        @if (is_array($subValue))
                                            @foreach ($subValue as $innerKey => $innerValue)
                                                <input type="hidden" name="{{ $key }}[{{ $subKey }}][{{ $innerKey }}]" value="{{ $innerValue }}">
                                            @endforeach
                                        @else
                                            <input type="hidden" name="{{ $key }}[{{ $subKey }}]" value="{{ $subValue }}">
                                        @endif
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <label class="small text-body-secondary mb-0">Sort by</label>
                            <select name="sort" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                                <option value="name_asc" @selected($filters['sort'] === 'name_asc')>Name (A–Z)</option>
                                <option value="name_desc" @selected($filters['sort'] === 'name_desc')>Name (Z–A)</option>
                                <option value="price_asc" @selected($filters['sort'] === 'price_asc')>Price (Low to High)</option>
                                <option value="price_desc" @selected($filters['sort'] === 'price_desc')>Price (High to Low)</option>
                            </select>
                        </form>
                    </div>

                    @if ($products->isEmpty())
                        <div class="card border-0 shadow-sm rounded-4 p-5 text-center" data-aos="fade-up">
                            <p class="mb-2 fs-5">No products match your filters.</p>
                            <p class="text-body-secondary mb-0">Try clearing some filters or search terms.</p>
                        </div>
                    @else
                        <div class="row g-4">
                            @foreach ($products as $index => $product)
                                <div class="col-md-6 col-xl-4" data-aos="fade-up" data-aos-delay="{{ min($index, 6) * 60 }}">
                                    <x-storefront.product-card :product="$product" />
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            {{ $products->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</x-layouts.storefront>
