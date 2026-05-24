@extends('admin.layouts.app')
@section('title', 'शाखा व्यवस्थापन')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4 class="mb-0">शाखा सूची</h4>
    <a href="{{ route('admin.departments.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> नयाँ शाखा</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>#</th><th>नाम</th><th>Slug</th><th>कोठा/तला</th><th>सम्पर्क</th><th>स्थिति</th><th></th></tr></thead>
            <tbody>
                @foreach ($departments as $d)
                    <tr>
                        <td>{{ $d->sort_order }}</td>
                        <td><strong>{{ $d->name }}</strong><br><small class="text-muted">{{ $d->name_en }}</small></td>
                        <td><code>{{ $d->slug }}</code></td>
                        <td>{{ $d->room }} · {{ $d->floor }}</td>
                        <td>{{ $d->phone }}</td>
                        <td>
                            @if ($d->is_active) <span class="badge bg-success">सक्रिय</span>
                            @else <span class="badge bg-secondary">निष्क्रिय</span> @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.departments.edit', $d) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form method="post" action="{{ route('admin.departments.destroy', $d) }}" class="d-inline" onsubmit="return confirm('मेटाउने?')">
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
{{ $departments->links() }}
@endsection
