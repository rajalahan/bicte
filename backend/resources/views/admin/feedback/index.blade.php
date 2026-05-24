@extends('admin.layouts.app')
@section('title', 'गुनासो / सुझाव')

@section('content')
<form method="get" class="mb-3 d-flex gap-2">
    <select name="status" class="form-select" style="max-width:200px">
        <option value="">सबै स्थिति</option>
        @foreach (['new'=>'नयाँ','in_progress'=>'प्रक्रियामा','resolved'=>'समाधान','closed'=>'बन्द'] as $k => $v)
            <option value="{{ $k }}" @selected(($filter['status'] ?? '') === $k)>{{ $v }}</option>
        @endforeach
    </select>
    <select name="type" class="form-select" style="max-width:200px">
        <option value="">सबै किसिम</option>
        @foreach (['complaint'=>'गुनासो','suggestion'=>'सुझाव','appreciation'=>'प्रशंसा','info'=>'सूचना अनुरोध'] as $k => $v)
            <option value="{{ $k }}" @selected(($filter['type'] ?? '') === $k)>{{ $v }}</option>
        @endforeach
    </select>
    <button class="btn btn-outline-primary"><i class="bi bi-funnel"></i> फिल्टर</button>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>टिकट</th><th>विषय</th><th>किसिम</th><th>शाखा</th><th>स्थिति</th><th>मिति</th><th></th></tr></thead>
            <tbody>
                @forelse ($rows as $f)
                    <tr>
                        <td><strong>{{ $f->ticket }}</strong></td>
                        <td>{{ $f->subject }}<br><small class="text-muted">{{ $f->name ?? '—' }} · {{ $f->phone ?? '' }}</small></td>
                        <td>{{ $f->type }}</td>
                        <td>{{ optional($f->department)->name ?? '—' }}</td>
                        <td>
                            <form method="post" action="{{ route('admin.feedback.update', $f) }}" class="d-flex gap-1">
                                @csrf @method('PUT')
                                <select name="status" class="form-select form-select-sm">
                                    @foreach (['new','in_progress','resolved','closed'] as $s)
                                        <option value="{{ $s }}" @selected($f->status === $s)>{{ $s }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-sm btn-outline-primary"><i class="bi bi-check"></i></button>
                            </form>
                        </td>
                        <td>{{ optional($f->submitted_at)->format('Y-m-d H:i') }}</td>
                        <td><a href="{{ route('admin.feedback.show', $f) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">अभिलेख छैन</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $rows->links() }}
@endsection
