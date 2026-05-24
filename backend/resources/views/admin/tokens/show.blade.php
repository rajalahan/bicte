@extends('admin.layouts.app')
@section('title', 'टोकन · ' . $token->code)

@section('content')
<a href="{{ route('admin.tokens.index') }}" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> फिर्ता</a>

<div class="card p-4">
    <h2 style="font-family:'Inter';font-weight:800">{{ $token->code }}</h2>
    <p>{{ optional($token->department)->name }} · क्रम {{ $token->sequence }}</p>
    <p>स्थिति: <strong>{{ $token->status }}</strong></p>
    <p>जारी: {{ optional($token->issued_at)->format('Y-m-d H:i:s') }}</p>
    <p>सेवा: {{ optional($token->served_at)->format('Y-m-d H:i:s') ?? '—' }}</p>
    <p>बन्द: {{ optional($token->closed_at)->format('Y-m-d H:i:s') ?? '—' }}</p>
</div>
@endsection
