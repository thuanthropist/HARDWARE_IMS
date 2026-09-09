<x-layouts.storefront title="My Quotes">
    <div class="container" style="padding-top: 7.5rem;">
        <h1 class="fw-bold mb-4" data-aos="fade-up">My Quotes</h1>

        @if ($quotes->isEmpty())
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center" data-aos="fade-up">
                <p class="mb-3">You haven't requested any quotes yet.</p>
                <a href="{{ route('storefront.quotes.create') }}" class="btn btn-primary">Request a Quote</a>
            </div>
        @else
            <div class="card border-0 shadow-sm rounded-4 p-2" data-aos="fade-up">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr class="small text-body-secondary text-uppercase">
                                <th class="ps-3">Quote #</th>
                                <th class="d-none d-md-table-cell">Date</th>
                                <th>Status</th>
                                <th class="pe-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($quotes as $quote)
                                <tr>
                                    <td class="ps-3 fw-semibold">{{ $quote->quote_number }}</td>
                                    <td class="small text-body-secondary d-none d-md-table-cell">{{ $quote->created_at->format('d M Y') }}</td>
                                    <td><span class="sf-badge-stock sf-badge-low-stock">{{ Str::headline($quote->status) }}</span></td>
                                    <td class="pe-3 text-end">
                                        <a href="{{ route('storefront.account.quotes.show', $quote->quote_number) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">{{ $quotes->links() }}</div>
        @endif
    </div>
</x-layouts.storefront>
