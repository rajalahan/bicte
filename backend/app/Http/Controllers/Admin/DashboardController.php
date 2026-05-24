<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Feedback;
use App\Models\Notice;
use App\Models\Token;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'departments'        => Department::count(),
            'departments_active' => Department::where('is_active', true)->count(),
            'notices'            => Notice::count(),
            'notices_active'     => Notice::where('is_active', true)->count(),
            'feedback_new'       => Feedback::where('status', 'new')->count(),
            'feedback_total'     => Feedback::count(),
            'tokens_today'       => Token::whereDate('issued_at', today())->count(),
            'tokens_waiting'     => Token::where('status', 'waiting')->count(),
        ];

        $recentFeedback = Feedback::with('department:id,slug,name')
            ->latest('submitted_at')->limit(8)->get();

        $tokensByDept = Token::whereDate('issued_at', today())
            ->selectRaw('department_id, count(*) as c')
            ->groupBy('department_id')
            ->with('department:id,slug,name')
            ->orderByDesc('c')
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentFeedback', 'tokensByDept'));
    }
}
