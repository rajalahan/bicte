<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Token;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    public function store(Request $r)
    {
        $data = $r->validate([
            'department' => 'required|string|max:80',
            'name'       => 'nullable|string|max:120',
            'phone'      => 'nullable|string|max:20',
        ]);

        $dept = Department::where('slug', $data['department'])->firstOrFail();
        $token = Token::issue($dept);
        if (!empty($data['name']))  $token->name  = $data['name'];
        if (!empty($data['phone'])) $token->phone = $data['phone'];
        $token->save();

        $ahead = Token::where('department_id', $dept->id)
            ->whereDate('issued_at', today())
            ->where('status', 'waiting')
            ->where('id', '<', $token->id)
            ->count();

        return response()->json([
            'data' => [
                'id'       => $token->id,
                'code'     => $token->code,
                'sequence' => $token->sequence,
                'ahead'    => $ahead,
                'eta'      => ($ahead * 5) . '–' . (($ahead + 1) * 5) . ' मिनेट',
                'issued_at'=> $token->issued_at,
                'department' => $dept->only('slug', 'name', 'room', 'floor'),
            ],
        ], 201);
    }

    public function show(Token $token)
    {
        return response()->json(['data' => $token->load('department:id,slug,name,room,floor')]);
    }

    /* ---- admin ---- */

    public function adminIndex(Request $r)
    {
        $q = Token::with('department:id,slug,name')->latest('issued_at');
        if ($s = $r->query('status'))     $q->where('status', $s);
        if ($d = $r->query('department')) $q->whereHas('department', fn ($qq) => $qq->where('slug', $d));
        return response()->json(['data' => $q->paginate(50)]);
    }

    public function updateStatus(Request $r, Token $token)
    {
        $token->status = $r->validate(['status' => 'required|in:waiting,serving,done,cancelled'])['status'];
        if ($token->status === 'serving' && !$token->served_at) $token->served_at = now();
        if (in_array($token->status, ['done', 'cancelled']))    $token->closed_at = now();
        $token->save();
        return response()->json(['data' => $token]);
    }
}
