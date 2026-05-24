<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Token;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    /** Live "now-serving" board – auto-refreshing public-friendly view. */
    public function index(Request $r)
    {
        $today = today();
        $rows = Token::with('department:id,slug,name')
            ->whereDate('issued_at', $today)
            ->orderBy('department_id')
            ->orderBy('sequence')
            ->paginate(100);

        $serving = Token::with('department:id,slug,name')
            ->where('status', 'serving')
            ->whereDate('issued_at', $today)
            ->get();

        $departments = Department::active()->get();

        return view('admin.tokens.index', compact('rows', 'serving', 'departments'));
    }

    public function show(Token $token)
    {
        return view('admin.tokens.show', ['token' => $token->load('department')]);
    }

    public function update(Request $r, Token $token)
    {
        $token->status = $r->validate(['status' => 'required|in:waiting,serving,done,cancelled'])['status'];
        if ($token->status === 'serving' && !$token->served_at) $token->served_at = now();
        if (in_array($token->status, ['done', 'cancelled']))    $token->closed_at = now();
        $token->save();
        return back()->with('ok', 'टोकन अद्यावधिक भयो।');
    }
}
