<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index(Request $r)
    {
        $q = Feedback::with('department:id,name')->latest('submitted_at');
        if ($s = $r->query('status')) $q->where('status', $s);
        if ($t = $r->query('type'))   $q->where('type',   $t);
        return view('admin.feedback.index', ['rows' => $q->paginate(30), 'filter' => $r->query()]);
    }

    public function show(Feedback $feedback)
    {
        return view('admin.feedback.show', ['fb' => $feedback->load('department')]);
    }

    public function update(Request $r, Feedback $feedback)
    {
        $feedback->status = $r->validate(['status' => 'required|in:new,in_progress,resolved,closed'])['status'];
        if ($feedback->status === 'resolved') $feedback->resolved_at = now();
        $feedback->save();
        return back()->with('ok', 'स्थिति अद्यावधिक भयो।');
    }

    public function destroy(Feedback $feedback)
    {
        $feedback->delete();
        return redirect()->route('admin.feedback.index')->with('ok', 'मेटाइयो।');
    }
}
