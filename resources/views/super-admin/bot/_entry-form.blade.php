{{-- Shared add/edit form for a knowledge-base entry. $entry is null when adding. --}}
@php
    $isEdit = !is_null($entry);
    $action = $isEdit ? route('super-admin.bot.kb.update', $entry->id) : route('super-admin.bot.kb.store');
    $links  = $isEdit ? ($entry->links ?? []) : [];
    // pad to 2 link rows
    for ($i = count($links); $i < 2; $i++) { $links[] = ['label' => '', 'url' => '']; }
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="row g-2">
        <div class="col-md-6">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control form-control-sm" required
                   value="{{ $isEdit ? $entry->title : '' }}" placeholder="e.g. How do I reset the projector booking?">
        </div>
        <div class="col-md-3">
            <label class="form-label">Category</label>
            <input type="text" name="category" class="form-control form-control-sm"
                   value="{{ $isEdit ? $entry->category : 'general' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Priority</label>
            <input type="number" name="priority" min="0" max="1000" class="form-control form-control-sm"
                   value="{{ $isEdit ? $entry->priority : 0 }}">
        </div>

        <div class="col-12">
            <label class="form-label">Keywords / trigger phrases</label>
            <input type="text" name="keywords" class="form-control form-control-sm" required
                   value="{{ $isEdit ? $entry->keywords : '' }}"
                   placeholder="space or comma separated — e.g. projector booking book classroom av equipment">
        </div>

        <div class="col-12">
            <label class="form-label">Answer (markdown supported)</label>
            <textarea name="answer" rows="3" class="form-control form-control-sm" required>{{ $isEdit ? $entry->answer : '' }}</textarea>
        </div>

        <div class="col-12">
            <label class="form-label">Deep links (optional)</label>
            @foreach($links as $i => $l)
                <div class="row g-2 mb-1">
                    <div class="col-md-4"><input type="text" name="link_labels[]" class="form-control form-control-sm" placeholder="Label" value="{{ $l['label'] ?? '' }}"></div>
                    <div class="col-md-8"><input type="text" name="link_urls[]" class="form-control form-control-sm" placeholder="/dashboard/... or https://..." value="{{ $l['url'] ?? '' }}"></div>
                </div>
            @endforeach
        </div>

        <div class="col-12 d-flex align-items-center justify-content-between mt-1">
            <label class="d-flex align-items-center gap-2" style="font-size:13px;cursor:pointer;">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ (!$isEdit || $entry->is_active) ? 'checked' : '' }}>
                <span>Active</span>
            </label>
            <button class="btn btn-sm btn-primary">{{ $isEdit ? 'Update entry' : 'Add entry' }}</button>
        </div>
    </div>
</form>
