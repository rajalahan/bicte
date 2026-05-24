@extends('admin.layouts.app')
@section('title', 'ड्यासबोर्ड')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="stat"><div class="stat__num">{{ $stats['departments_active'] }}</div><div class="stat__label">सक्रिय शाखा (कुल {{ $stats['departments'] }})</div></div></div>
    <div class="col-md-3"><div class="stat"><div class="stat__num">{{ $stats['notices_active'] }}</div><div class="stat__label">सक्रिय सूचना (कुल {{ $stats['notices'] }})</div></div></div>
    <div class="col-md-3"><div class="stat"><div class="stat__num">{{ $stats['feedback_new'] }}</div><div class="stat__label">नयाँ गुनासो (कुल {{ $stats['feedback_total'] }})</div></div></div>
    <div class="col-md-3"><div class="stat"><div class="stat__num">{{ $stats['tokens_today'] }}</div><div class="stat__label">आजका टोकन ({{ $stats['tokens_waiting'] }} पर्खाइमा)</div></div></div>
</div>

<div class="row g-3">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-white"><strong><i class="bi bi-chat-left-text-fill text-danger"></i> भर्खरका गुनासो / सुझाव</strong></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>टिकट</th><th>विषय</th><th>शाखा</th><th>स्थिति</th><th>मिति</th></tr></thead>
                    <tbody>
                        @forelse ($recentFeedback as $f)
                            <tr>
                                <td><a href="{{ route('admin.feedback.show', $f) }}">{{ $f->ticket }}</a></td>
                                <td>{{ \Illuminate\Support\Str::limit($f->subject, 40) }}</td>
                                <td>{{ optional($f->department)->name ?? '—' }}</td>
                                <td><span class="badge bg-{{ $f->status === 'new' ? 'new' : ($f->status === 'in_progress' ? 'progress' : ($f->status === 'resolved' ? 'resolved' : 'closed')) }}">{{ $f->status }}</span></td>
                                <td>{{ optional($f->submitted_at)->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">अभिलेख छैन</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-white"><strong><i class="bi bi-ticket-detailed-fill text-primary"></i> आजका टोकन (शाखा अनुसार)</strong></div>
            <ul class="list-group list-group-flush">
                @forelse ($tokensByDept as $row)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>{{ optional($row->department)->name ?? '—' }}</span>
                        <strong>{{ $row->c }}</strong>
                    </li>
                @empty
                    <li class="list-group-item text-muted">आज कुनै टोकन जारी भएको छैन।</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
