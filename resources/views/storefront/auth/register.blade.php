<x-layouts.storefront title="Create Account">
    <section class="sf-section" style="padding-top: 8rem;">
        <div class="container" style="max-width: 480px;">
            <div class="text-center mb-4" data-aos="fade-up">
                <h2 class="sf-section-title">Create your account</h2>
                <p class="text-body-secondary">Track orders and quotes, and check out faster next time.</p>
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

                <form method="POST" action="{{ route('storefront.register.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email address</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="+255 7XX XXX XXX">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Create Account</button>
                </form>

                <p class="text-center small text-body-secondary mt-3 mb-0">
                    Already have an account? <a href="{{ route('storefront.login') }}">Log in</a>
                </p>
            </div>
        </div>
    </section>
</x-layouts.storefront>
