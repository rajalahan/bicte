@extends('admin.layouts.app')
@section('title', 'सूचना व्यवस्थापन')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4 class="mb-0">सूचना सूची</h4>
    <a href="{{ route('admin.notices.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> नयाँ सूचना</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>शीर्षक</th><th>श्रेणी</th><th>शाखा</th><th>प्रकाशन</th><th>स्थिति</th><th></th></tr></thead>
            <tbody>
                @foreach ($notices as $n)
                    <tr>
                        <td>
                            @if ($n->is_urgent) <span class="badge bg-danger me-1">URGENT</span> @endif
                            <strong>{{ $n->title }}</strong><br>
                            <small class="text-muted">{{ \Illuminate\Support\Str::limit($n->summary, 80) }}</small>
                        </td>
                        <td>{{ $n->category ?? '—' }}</td>
                        <td>{{ optional($n->department)->name ?? '—' }}</td>
                        <td>{{ optional($n->published_at)->format('Y-m-d') ?? '—' }}</td>
                        <td>
                            @if ($n->is_active) <span class="badge bg-success">सक्रिय</span>
                            @else <span class="badge bg-secondary">निष्क्रिय</span> @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.notices.edit', $n) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form method="post" action="{{ route('admin.notices.destroy', $n) }}" class="d-inline" onsubmit="return confirm('मेटाउने?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
{{ $notices->links() }}
@endsection
