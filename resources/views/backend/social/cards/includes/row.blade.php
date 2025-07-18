<x-livewire-tables::bs5.table.cell>
    <div style="position: inherit;">
        <strong style="font-weight: 600; font-size: 16px; color: #597a96; display: inherit;">{{ $row->id }}</strong>
        <span style="font-size: 12px; font-weight: 400; color: #aab8c2;">#{{ base_convert($row->id, 10, 36) }}</span>
    </div>
</x-livewire-tables::bs5.table.cell>

<x-livewire-tables::bs5.table.cell>
    <img src="{{ $row->getPicture() }}" width="128" height="72" class="img-fluid rounded" style="max-width: 128px; max-height: 72px; object-fit: cover;" alt="{{ $row->content }}">
</x-livewire-tables::bs5.table.cell>

<x-livewire-tables::bs5.table.cell>
    <p style="max-width: 320px;">{{ Str::limit($row->content, 191, '...') }}</p>
</x-livewire-tables::bs5.table.cell>

<x-livewire-tables::bs5.table.cell>
    @include('backend.social.cards.includes.platform', ['cards' => $row])
</x-livewire-tables::bs5.table.cell>

<x-livewire-tables::bs5.table.cell style="max-width: 360px;">
    {{-- IP Address、User Agent --}}
    <a href="{{ route('frontend.social.cards.show', ['id' => $row->id]) }}">
        <p class="mb-2">IP Address: <br />{{ $row->ip_address ?? 'None' }}</p>

        @php
            $parsedData = app('App\Http\Livewire\Frontend\SocialCardsReviewTable')->getParsedUserAgent($row->user_agent);
        @endphp

        <p class="mb-2">Platform: <br />{{ $parsedData['platform'] ?? 'None' }}</p>
        <p class="mb-2">OS Version: <br />{{ $parsedData['os_version'] ?? 'None' }}</p>
        <p class="mb-2">Device: <br />{{ $parsedData['device'] ?? 'None' }}</p>
        <p class="mb-2">Browser: <br />{{ $parsedData['browser'] ?? 'None' }}</p>
        <p class="mb-0">Browser Version: <br />{{ $parsedData['browser_version'] ?? 'None' }}</p>
        <p class="mb-0">Check Code: <br />{{ $row->config['check_code'] ?? 'None' }}</p>
    </a>
</x-livewire-tables::bs5.table.cell>

<x-livewire-tables::bs5.table.cell>
    <div style="position: inherit;">
        <strong style="font-weight: 600; font-size: 16px; color: #597a96; display: inherit;">{{ $row->created_at->toDateString() }}</strong>
        <span style="font-size: 12px; font-weight: 400; color: #aab8c2;">{{ $row->created_at->diffForHumans() }}</span>
    </div>
</x-livewire-tables::bs5.table.cell>

<x-livewire-tables::bs5.table.cell>
    @include('backend.social.cards.includes.actions', ['cards' => $row])
</x-livewire-tables::bs5.table.cell>
