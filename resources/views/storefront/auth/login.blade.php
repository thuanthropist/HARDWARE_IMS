<x-layouts.storefront title="Log In">
    <section class="sf-section" style="padding-top: 8rem;">
        <div class="container" style="max-width: 440px;">
            <div class="text-center mb-4" data-aos="fade-up">
                <h2 class="sf-section-title">Welcome back</h2>
                <p class="text-body-secondary">Log in to track orders and quotes.</p>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4" data-aos="fade-up" data-aos-delay="100">
                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('storefront.login.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email address</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Log In</button>
                </form>

                <p class="text-center small text-body-secondary mt-3 mb-0">
                    New here? <a href="{{ route('storefront.register') }}">Create an account</a>
                </p>
            </div>

            <p class="text-center small text-body-secondary mt-3 mb-0">
                Staff member? <a href="{{ route('login') }}">Log in here</a>
            </p>
        </div>
    </section>
</x-layouts.storefront>
