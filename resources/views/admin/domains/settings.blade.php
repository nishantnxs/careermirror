@extends('admin.layouts.app')

@section('title', 'Website Settings')
@section('heading', 'Website Settings')
@section('subheading', 'Settings for '.$domain->host.' — branding, contact, SEO, mail and system.')

@section('actions')
    <a href="{{ route('admin.domains.edit', $domain) }}" class="btn btn-light">Edit domain</a>
    <a href="{{ route('admin.domains.index') }}" class="btn btn-light">All domains</a>
@endsection

@section('content')
    @php($activeTab = request('tab', array_key_first($groups)))

    <form method="POST" action="{{ route('admin.domains.settings.update', $domain) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="active_tab" id="activeTabInput" value="{{ $activeTab }}">

        <div class="card">
            <div class="card-header p-0 border-bottom-0">
                <ul class="nav nav-tabs card-header-tabs m-0 px-2 pt-2" role="tablist">
                    @foreach ($groups as $groupKey => $group)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link @if ($groupKey === $activeTab) active @endif"
                                    id="tab-{{ $groupKey }}" data-bs-toggle="tab"
                                    data-bs-target="#pane-{{ $groupKey }}" data-tab-key="{{ $groupKey }}"
                                    type="button" role="tab">
                                <i class="bi {{ $group['icon'] ?? 'bi-dot' }} me-1"></i>
                                {{ $group['label'] ?? ucfirst($groupKey) }}
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content">
                    @foreach ($groups as $groupKey => $group)
                        <div class="tab-pane fade @if ($groupKey === $activeTab) show active @endif"
                             id="pane-{{ $groupKey }}" role="tabpanel">
                            <div class="row g-3">
                                @foreach ($group['fields'] ?? [] as $key => $field)
                                    @include('admin.settings.partials.field', [
                                        'key' => $key,
                                        'field' => $field,
                                        'value' => $settings->value($key),
                                    ])
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card-footer bg-white d-flex justify-content-end gap-2 py-3">
                <a href="{{ route('admin.domains.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-brand px-4">
                    <i class="bi bi-save me-1"></i> Save settings
                </button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-tab-key]').forEach(function (button) {
        button.addEventListener('shown.bs.tab', function () {
            document.getElementById('activeTabInput').value = button.dataset.tabKey;
        });
    });
</script>
@endpush
