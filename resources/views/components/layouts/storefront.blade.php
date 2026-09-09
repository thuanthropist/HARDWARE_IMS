<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? setting('business.name', 'Hardware IMS') }} · Hardware & Building Materials Store</title>
    <meta name="description" content="{{ $metaDescription ?? 'Building materials, plumbing, electrical and solar supplies with free material planning calculators.' }}">
    @if (setting('business.logo_path'))
        <link rel="icon" href="{{ asset('storage/'.setting('business.logo_path')) }}">
    @endif
    @vite(['resources/css/storefront.css', 'resources/js/storefront.js'])
</head>
<body>
    <nav class="navbar navbar-expand-lg sf-navbar" data-bs-theme="dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('storefront.home') }}" title="{{ setting('business.name', 'Hardware IMS') }}">
                @if (setting('business.logo_path'))
                    <img src="{{ asset('storage/'.setting('business.logo_path')) }}" alt="{{ setting('business.name', 'Hardware IMS') }}" class="rounded-3" style="width:2.1rem;height:2.1rem;object-fit:contain;">
                @else
                    <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-white bg-opacity-10" style="width:2.1rem;height:2.1rem;">🛠️</span>
                @endif
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sfNav" aria-controls="sfNav" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="sfNav">
                <ul class="navbar-nav mb-2 mb-lg-0">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Departments</a>
                        <ul class="dropdown-menu">
                            @foreach ($navDepartments ?? [] as $department)
                                <li><a class="dropdown-item" href="{{ route('storefront.departments.show', $department->slug) }}">{{ $department->name }}</a></li>
                            @endforeach
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Planning Tools</a>
                        <ul class="dropdown-menu">
                            @foreach ($navCalculatorTypes ?? [] as $tool)
                                <li><a class="dropdown-item" href="{{ route('storefront.tools.show', $tool->key) }}">{{ $tool->name }}</a></li>
                            @endforeach
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('storefront.contact') }}">Contact</a></li>
                </ul>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a href="{{ route('storefront.orders.track') }}" class="btn btn-pill-outline btn-sm sf-icon-btn" title="Track Order" aria-label="Track Order">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9-4 9 4-9 4-9-4zm0 0v10l9 4m0-14v14m9-14v10l-9 4" /></svg>
                    </a>

                    <div class="dropdown">
                        <button class="btn btn-outline-light btn-sm position-relative sf-icon-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Cart" aria-label="Cart">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l3-8H5.4M7 13L5.4 5M7 13l-1.5 6h11M9 21a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z" /></svg>
                            @if (($cartItemCount ?? 0) > 0)
                                <span class="badge rounded-pill bg-primary position-absolute top-0 start-100 translate-middle">{{ $cartItemCount }}</span>
                            @endif
                        </button>
                        <div class="dropdown-menu dropdown-menu-end p-3" style="width: 320px;">
                            @if (! ($cartPreview?->items->isNotEmpty()))
                                <p class="text-body-secondary small mb-0 text-center py-2">Your cart is empty.</p>
                            @else
                                <div style="max-height: 260px; overflow-y: auto;">
                                    @foreach ($cartPreview->items as $item)
                                        <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                                            <div class="small text-truncate">
                                                <span class="fw-semibold">{{ $item->quantity }}&times;</span> {{ $item->product->name }}
                                            </div>
                                            <div class="small fw-semibold text-nowrap">TZS {{ number_format($item->lineTotal, 0) }}</div>
                                        </div>
                                    @endforeach
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between fw-bold small mb-3">
                                    <span>Subtotal</span>
                                    <span>TZS {{ number_format($cartPreview->subtotal, 0) }}</span>
                                </div>
                                <a href="{{ route('storefront.cart.show') }}" class="btn btn-primary btn-sm w-100">View Cart</a>
                            @endif
                        </div>
                    </div>

                    @auth('customer')
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ Str::before(auth('customer')->user()->name, ' ') }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('storefront.account.orders') }}">My Orders</a></li>
                                <li><a class="dropdown-item" href="{{ route('storefront.account.quotes') }}">My Quotes</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('storefront.logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Log out</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('storefront.login') }}" class="btn btn-primary btn-sm">Shop Now</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main>
        @if (session('success'))
            <div class="container" style="padding-top: 6.5rem;">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif
        @if (session('error'))
            <div class="container" style="padding-top: 6.5rem;">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        {{ $slot }}
    </main>

    <footer class="sf-footer">
        <div class="container">
            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        @if (setting('business.logo_path'))
                            <img src="{{ asset('storage/'.setting('business.logo_path')) }}" alt="{{ setting('business.name', 'Hardware IMS') }}" style="height: 28px; width: 28px; object-fit: contain;" class="rounded-2">
                        @endif
                        <h6 class="text-white fw-bold mb-0">{{ setting('business.name', 'Hardware IMS') }}</h6>
                    </div>
                    <p class="small mb-0">Your one-stop shop for building materials, plumbing, electrical and solar supplies.</p>
                </div>
                <div class="col-lg-2 col-6">
                    <h6 class="small text-uppercase text-white-50 mb-2">Departments</h6>
                    <ul class="list-unstyled small mb-0">
                        @foreach ($navDepartments ?? [] as $department)
                            <li class="mb-1"><a href="{{ route('storefront.departments.show', $department->slug) }}">{{ $department->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-lg-2 col-6">
                    <h6 class="small text-uppercase text-white-50 mb-2">Planning Tools</h6>
                    <ul class="list-unstyled small mb-0">
                        @foreach ($navCalculatorTypes ?? [] as $tool)
                            <li class="mb-1"><a href="{{ route('storefront.tools.show', $tool->key) }}">{{ $tool->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6 class="small text-uppercase text-white-50 mb-2">Get in Touch</h6>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-1">📍 {{ setting('business.address', 'Kariakoo, Dar es Salaam, Tanzania') }}</li>
                        <li class="mb-1">📞 {{ setting('business.phone', '+255 22 286 1000') }}</li>
                        <li class="mb-1">✉️ {{ setting('business.email', 'info@hardwareims.example') }}</li>
                    </ul>
                </div>
            </div>
            <hr class="border-secondary mt-3 mb-2">
            <div class="d-flex flex-wrap justify-content-between small sf-footer-bottom">
                <span>&copy; {{ now()->year }} {{ setting('business.name', 'Hardware IMS') }}. All rights reserved.</span>
                <span>Prices shown in TZS.</span>
            </div>
        </div>
    </footer>

    <x-storefront.whatsapp-link
        class="sf-whatsapp-fab"
        aria-label="Chat on WhatsApp"
    >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="26" height="26" fill="currentColor"><path d="M20.52 3.48A11.94 11.94 0 0012.04 0C5.5 0 .2 5.3.2 11.84c0 2.09.55 4.13 1.6 5.93L0 24l6.4-1.68a11.8 11.8 0 005.63 1.43h.01c6.54 0 11.84-5.3 11.84-11.84 0-3.16-1.23-6.13-3.36-8.43zM12.04 21.5a9.6 9.6 0 01-4.9-1.34l-.35-.21-3.8 1 1.01-3.7-.23-.38a9.63 9.63 0 01-1.48-5.13c0-5.32 4.33-9.65 9.66-9.65a9.6 9.6 0 016.83 2.83 9.6 9.6 0 012.83 6.83c0 5.32-4.33 9.65-9.57 9.65zm5.3-7.23c-.29-.15-1.72-.85-1.99-.95-.27-.1-.46-.15-.66.15-.2.29-.76.94-.93 1.14-.17.2-.34.22-.63.07-.29-.15-1.23-.45-2.34-1.44-.87-.77-1.45-1.72-1.62-2.01-.17-.29-.02-.45.13-.6.13-.13.29-.34.44-.51.15-.17.2-.29.29-.49.1-.2.05-.37-.02-.51-.07-.15-.66-1.59-.9-2.18-.24-.57-.48-.5-.66-.5h-.56c-.2 0-.51.07-.78.37-.27.29-1.02 1-1.02 2.44 0 1.44 1.05 2.83 1.2 3.03.15.2 2.06 3.15 5 4.41.7.3 1.24.48 1.67.62.7.22 1.34.19 1.84.11.56-.08 1.72-.7 1.96-1.38.24-.68.24-1.26.17-1.38-.07-.12-.26-.2-.55-.35z"/></svg>
    </x-storefront.whatsapp-link>
</body>
</html>
