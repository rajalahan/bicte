<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Notice;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function index()
    {
        $notices = Notice::with('department:id,name')->latest('published_at')->paginate(30);
        return view('admin.notices.index', compact('notices'));
    }

    public function create()
    {
        return view('admin.notices.edit', [
            'notice' => new Notice(['is_active' => true, 'published_at' => now()]),
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function store(Request $r)
    {
        Notice::create($this->validated($r));
        return redirect()->route('admin.notices.index')->with('ok', 'सूचना थपियो।');
    }

    public function edit(Notice $notice)
    {
        return view('admin.notices.edit', [
            'notice' => $notice,
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function update(Request $r, Notice $notice)
    {
        $notice->update($this->validated($r));
        return redirect()->route('admin.notices.index')->with('ok', 'सूचना अद्यावधिक भयो।');
    }

    public function destroy(Notice $notice)
    {
        $notice->delete();
        return redirect()->route('admin.notices.index')->with('ok', 'सूचना हटाइयो।');
    }

    private function validated(Request $r): array
    {
        $data = $r->validate([
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
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['is_urgent'] = (bool) ($data['is_urgent'] ?? false);
        return $data;
    }
}
