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

<x-livewire-tables::bs5.table.cell>
    <div style="position: inherit;">
        <strong style="font-weight: 600; font-size: 16px; color: #597a96; display: inherit;">{{ $row->created_at->toDateString() }}</strong>
        <span style="font-size: 12px; font-weight: 400; color: #aab8c2;">{{ $row->created_at->diffForHumans() }}</span>
    </div>
</x-livewire-tables::bs5.table.cell>

<x-livewire-tables::bs5.table.cell>
    @include('backend.social.cards.includes.actions', ['cards' => $row])
</x-livewire-tables::bs5.table.cell>
