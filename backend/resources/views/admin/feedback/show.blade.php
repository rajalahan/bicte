@extends('admin.layouts.app')
@section('title', 'गुनासो विवरण · ' . $fb->ticket)

@section('content')
<a href="{{ route('admin.feedback.index') }}" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> फिर्ता</a>

<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">{{ $fb->subject }}</h4>
        <span class="badge bg-{{ $fb->status === 'new' ? 'new' : ($fb->status === 'in_progress' ? 'progress' : ($fb->status === 'resolved' ? 'resolved' : 'closed')) }}">{{ $fb->status }}</span>
    </div>
    <dl class="row">
        <dt class="col-sm-3">टिकट</dt>          <dd class="col-sm-9"><code>{{ $fb->ticket }}</code></dd>
        <dt class="col-sm-3">किसिम</dt>          <dd class="col-sm-9">{{ $fb->type }}</dd>
        <dt class="col-sm-3">शाखा</dt>           <dd class="col-sm-9">{{ optional($fb->department)->name ?? '—' }}</dd>
        <dt class="col-sm-3">सम्पर्क</dt>         <dd class="col-sm-9">{{ $fb->name ?? '—' }} · {{ $fb->phone ?? '—' }} · {{ $fb->email ?? '—' }}</dd>
        <dt class="col-sm-3">पेश मिति</dt>        <dd class="col-sm-9">{{ optional($fb->submitted_at)->format('Y-m-d H:i') }}</dd>
        <dt class="col-sm-3">IP</dt>             <dd class="col-sm-9"><code>{{ $fb->ip_address }}</code></dd>
    </dl>
    <hr>
    <p style="white-space: pre-wrap">{{ $fb->message }}</p>
</div>
@endsection
