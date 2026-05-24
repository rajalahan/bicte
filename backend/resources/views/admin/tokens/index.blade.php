@extends('admin.layouts.app')
@section('title', 'टोकन / Queue Board (आज)')

@push('scripts')
<script>setTimeout(() => location.reload(), 30000);</script>
@endpush

@section('content')
<div class="row g-3 mb-4">
    @foreach ($serving as $t)
        <div class="col-md-3">
            <div class="card p-4 text-center" style="background:#0b3d91; color:#fff">
                <div style="font-size:14px; opacity:.85">अहिले सेवा गरिँदै</div>
                <div style="font-family:'Inter'; font-size:48px; font-weight:800;">{{ $t->code }}</div>
                <div>{{ optional($t->department)->name }}</div>
            </div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>टोकन</th><th>शाखा</th><th>क्रम</th><th>स्थिति</th><th>जारी</th><th>सेवा</th><th></th></tr></thead>
            <tbody>
                @forelse ($rows as $t)
                    <tr>
                        <td><code style="font-size:18px">{{ $t->code }}</code></td>
                        <td>{{ optional($t->department)->name }}</td>
                        <td>{{ $t->sequence }}</td>
                        <td>
                            <form method="post" action="{{ route('admin.tokens.update', $t) }}" class="d-flex gap-1">
                                @csrf @method('PUT')
                                <select name="status" class="form-select form-select-sm">
                                    @foreach (['waiting','serving','done','cancelled'] as $s)
                                        <option value="{{ $s }}" @selected($t->status === $s)>{{ $s }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-sm btn-outline-primary"><i class="bi bi-check"></i></button>
                            </form>
                        </td>
                        <td>{{ optional($t->issued_at)->format('H:i:s') }}</td>
                        <td>{{ optional($t->served_at)->format('H:i:s') ?? '—' }}</td>
                        <td></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">आज कुनै टोकन जारी भएको छैन।</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $rows->links() }}
@endsection
