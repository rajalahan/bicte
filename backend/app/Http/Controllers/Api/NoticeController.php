<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function index(Request $r)
    {
        $limit = (int) $r->query('limit', 20);
        return response()->json([
            'data' => Notice::published()->limit(min($limit, 100))->get(),
        ]);
    }

    public function show(Notice $notice)
    {
        abort_unless($notice->is_active, 404);
        return response()->json(['data' => $notice]);
    }

    /* ---- admin ---- */

    public function store(Request $r)
    {
        return response()->json(['data' => Notice::create($this->validated($r))], 201);
    }

    public function update(Request $r, Notice $notice)
    {
        $notice->update($this->validated($r));
        return response()->json(['data' => $notice->fresh()]);
    }

    public function destroy(Notice $notice)
    {
        $notice->delete();
        return response()->noContent();
    }

    private function validated(Request $r): array
    {
        return $r->validate([
            'department_id'  => 'nullable|exists:departments,id',
            'title'          => 'required|string|max:255',
            'summary'        => 'nullable|string|max:500',
            'body'           => 'nullable|string',
            'category'       => 'nullable|string|max:80',
            'attachment_url' => 'nullable|url',
            'is_active'      => 'boolean',
            'is_urgent'      => 'boolean',
            'published_at'   => 'nullable|date',
            'expires_at'     => 'nullable|date|after_or_equal:published_at',
        ]);
    }
}
