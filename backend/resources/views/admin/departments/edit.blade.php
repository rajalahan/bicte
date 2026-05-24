@extends('admin.layouts.app')
@section('title', $department->exists ? 'शाखा सम्पादन' : 'नयाँ शाखा')

@section('content')
@php
  $servicesText  = collect($department->services           ?? [])->implode("\n");
  $documentsText = collect($department->required_documents ?? [])->implode("\n");
  $processText   = collect($department->process            ?? [])->implode("\n");
  $feesText      = collect($department->fees ?? [])->map(fn($f) => ($f['service'] ?? '') . ' | ' . ($f['amount'] ?? ''))->implode("\n");
@endphp

<form method="post" action="{{ $department->exists ? route('admin.departments.update', $department) : route('admin.departments.store') }}">
    @csrf
    @if ($department->exists) @method('PUT') @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card p-4 mb-3">
                <h5>आधारभूत विवरण</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">नाम (नेपाली) *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $department->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Name (English)</label>
                        <input type="text" name="name_en" class="form-control" value="{{ old('name_en', $department->name_en) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Slug *</label>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug', $department->slug) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">कोठा</label>
                        <input type="text" name="room" class="form-control" value="{{ old('room', $department->room) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">तला</label>
                        <input type="text" name="floor" class="form-control" value="{{ old('floor', $department->floor) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">सारांश</label>
                        <textarea name="summary" class="form-control" rows="2">{{ old('summary', $department->summary) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card p-4 mb-3">
                <h5>सेवा / कागजात / प्रक्रिया / शुल्क</h5>
                <p class="text-muted small">प्रत्येक लाइनमा एउटा बुँदा। शुल्कमा <code>सेवा | रकम</code> ढाँचा प्रयोग गर्नुहोस्।</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">उपलब्ध सेवाहरू</label>
                        <textarea name="services_text" class="form-control" rows="6">{{ old('services_text', $servicesText) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">आवश्यक कागजातहरू</label>
                        <textarea name="documents_text" class="form-control" rows="6">{{ old('documents_text', $documentsText) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">सेवा प्रक्रिया (क्रमबद्ध)</label>
                        <textarea name="process_text" class="form-control" rows="6">{{ old('process_text', $processText) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">शुल्क (एक लाइनमा एक)</label>
                        <textarea name="fees_text" class="form-control" rows="6" placeholder="जन्म दर्ता | निःशुल्क">{{ old('fees_text', $feesText) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card p-4">
                <h5>नागरिक बडापत्र</h5>
                <textarea name="charter" class="form-control" rows="4">{{ old('charter', $department->charter) }}</textarea>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-4 mb-3">
                <h5>सम्पर्क</h5>
                <div class="mb-2"><label class="form-label">सम्पर्क व्यक्ति</label>
                    <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person', $department->contact_person) }}"></div>
                <div class="mb-2"><label class="form-label">पद</label>
                    <input type="text" name="contact_designation" class="form-control" value="{{ old('contact_designation', $department->contact_designation) }}"></div>
                <div class="mb-2"><label class="form-label">फोन</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $department->phone) }}"></div>
                <div class="mb-2"><label class="form-label">इमेल</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $department->email) }}"></div>
                <div class="mb-2"><label class="form-label">कार्यालय समय</label>
                    <input type="text" name="timings" class="form-control" value="{{ old('timings', $department->timings) }}"></div>
            </div>

            <div class="card p-4 mb-3">
                <h5>थप</h5>
                <div class="mb-2"><label class="form-label">Public URL (lahanmun.gov.np)</label>
                    <input type="url" name="public_url" class="form-control" value="{{ old('public_url', $department->public_url) }}"></div>
                <div class="mb-2"><label class="form-label">क्रम</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $department->sort_order) }}"></div>
                <div class="form-check mt-3">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="active" {{ old('is_active', $department->is_active) ? 'checked' : '' }}>
                    <label for="active" class="form-check-label">सक्रिय</label>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button class="btn btn-primary"><i class="bi bi-save-fill"></i> सेभ गर्नुहोस्</button>
                <a href="{{ route('admin.departments.index') }}" class="btn btn-outline-secondary">रद्द गर्नुहोस्</a>
            </div>
        </div>
    </div>
</form>
@endsection
