<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class FeedbackController extends Controller
{
    public function store(Request $r)
    {
        $data = $r->validate([
            'name'       => 'nullable|string|max:120',
            'phone'      => 'nullable|string|max:20',
            'email'      => 'nullable|email|max:200',
            'department' => 'nullable|string|max:80',
            'type'       => 'required|in:complaint,suggestion,appreciation,info',
            'subject'    => 'required|string|max:200',
            'message'    => 'required|string|max:2000',
        ]);

        // light spam guard – max 5 submissions / 10 min / IP
        $key = 'fb:' . $r->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['message' => 'धेरै निवेदन प्राप्त भयो, केहीबेरमा प्रयास गर्नुहोस्।'], 429);
        }
        RateLimiter::hit($key, 600);

        $deptId = null;
        if (!empty($data['department'])) {
            $deptId = Department::where('slug', $data['department'])->value('id');
        }

        $fb = Feedback::create([
            'ticket'        => Feedback::generateTicket(),
            'name'          => $data['name']    ?? null,
            'phone'         => $data['phone']   ?? null,
            'email'         => $data['email']   ?? null,
            'department_id' => $deptId,
            'type'          => $data['type'],
            'subject'       => $data['subject'],
            'message'       => $data['message'],
            'status'        => 'new',
            'ip_address'    => $r->ip(),
            'submitted_at'  => now(),
        ]);

        return response()->json(['data' => ['ticket' => $fb->ticket, 'id' => $fb->id]], 201);
    }

    /* ---- admin ---- */

    public function adminIndex(Request $r)
    {
        $q = Feedback::query()->with('department:id,slug,name')->latest('submitted_at');
        if ($status = $r->query('status'))    $q->where('status', $status);
        if ($type   = $r->query('type'))      $q->where('type',   $type);
        return response()->json(['data' => $q->paginate(20)]);
    }

    public function updateStatus(Request $r, int $id)
    {
        $fb = Feedback::findOrFail($id);
        $fb->status = $r->validate(['status' => 'required|in:new,in_progress,resolved,closed'])['status'];
        if ($fb->status === 'resolved') $fb->resolved_at = now();
        $fb->save();
        return response()->json(['data' => $fb]);
    }
}
