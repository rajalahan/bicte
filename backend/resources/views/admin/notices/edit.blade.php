@extends('admin.layouts.app')
@section('title', $notice->exists ? 'सूचना सम्पादन' : 'नयाँ सूचना')

@section('content')
<form method="post" action="{{ $notice->exists ? route('admin.notices.update', $notice) : route('admin.notices.store') }}">
    @csrf @if ($notice->exists) @method('PUT') @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card p-4 mb-3">
                <div class="mb-3">
                    <label class="form-label">शीर्षक *</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $notice->title) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">सारांश</label>
                    <textarea name="summary" class="form-control" rows="2">{{ old('summary', $notice->summary) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">मुख्य सामग्री</label>
                    <textarea name="body" class="form-control" rows="10">{{ old('body', $notice->body) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Attachment URL (PDF)</label>
                    <input type="url" name="attachment_url" class="form-control" value="{{ old('attachment_url', $notice->attachment_url) }}">
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-4 mb-3">
                <div class="mb-2"><label class="form-label">श्रेणी</label>
                    <input type="text" name="category" class="form-control" value="{{ old('category', $notice->category) }}"></div>
                <div class="mb-2"><label class="form-label">शाखा</label>
                    <select name="department_id" class="form-select">
                        <option value="">— सबै —</option>
                        @foreach ($departments as $d)
                            <option value="{{ $d->id }}" @selected(old('department_id', $notice->department_id) == $d->id)>{{ $d->name }}</option>
                        @endforeach
                    </select></div>
                <div class="mb-2"><label class="form-label">प्रकाशन मिति</label>
                    <input type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at', optional($notice->published_at)->format('Y-m-d\TH:i')) }}"></div>
                <div class="mb-2"><label class="form-label">समाप्ति मिति</label>
                    <input type="datetime-local" name="expires_at" class="form-control" value="{{ old('expires_at', optional($notice->expires_at)->format('Y-m-d\TH:i')) }}"></div>

                <div class="form-check mt-3">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="active" {{ old('is_active', $notice->is_active) ? 'checked' : '' }}>
                    <label for="active" class="form-check-label">सक्रिय</label>
                </div>
                <div class="form-check">
                    <input type="hidden" name="is_urgent" value="0">
                    <input class="form-check-input" type="checkbox" name="is_urgent" value="1" id="urgent" {{ old('is_urgent', $notice->is_urgent) ? 'checked' : '' }}>
                    <label for="urgent" class="form-check-label">अति महत्त्वपूर्ण</label>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button class="btn btn-primary"><i class="bi bi-save-fill"></i> सेभ गर्नुहोस्</button>
                <a href="{{ route('admin.notices.index') }}" class="btn btn-outline-secondary">रद्द गर्नुहोस्</a>
            </div>
        </div>
    </div>
</form>
@endsection
