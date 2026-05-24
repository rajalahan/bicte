<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::orderBy('sort_order')->paginate(50);
        return view('admin.departments.index', compact('departments'));
    }

    public function create()
    {
        return view('admin.departments.edit', ['department' => new Department()]);
    }

    public function store(Request $r)
    {
        Department::create($this->validated($r));
        return redirect()->route('admin.departments.index')->with('ok', 'शाखा थपियो।');
    }

    public function edit(Department $department)
    {
        return view('admin.departments.edit', compact('department'));
    }

    public function update(Request $r, Department $department)
    {
        $department->update($this->validated($r, $department->id));
        return redirect()->route('admin.departments.index')->with('ok', 'शाखा अद्यावधिक भयो।');
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return redirect()->route('admin.departments.index')->with('ok', 'शाखा हटाइयो।');
    }

    private function validated(Request $r, ?int $id = null): array
    {
        $data = $r->validate([
            'slug'                => 'required|string|max:80|unique:departments,slug' . ($id ? ",{$id}" : ''),
            'name'                => 'required|string|max:200',
            'name_en'             => 'nullable|string|max:200',
            'summary'             => 'nullable|string',
            'icon'                => 'nullable|string|max:80',
            'room'                => 'nullable|string|max:80',
            'floor'               => 'nullable|string|max:80',
            'contact_person'      => 'nullable|string|max:200',
            'contact_designation' => 'nullable|string|max:200',
            'phone'               => 'nullable|string|max:40',
            'email'               => 'nullable|email|max:200',
            'timings'             => 'nullable|string|max:200',
            'charter'             => 'nullable|string',
            'public_url'          => 'nullable|url',
            'services_text'       => 'nullable|string',  // textarea, one per line
            'documents_text'      => 'nullable|string',
            'process_text'        => 'nullable|string',
            'fees_text'           => 'nullable|string',  // "service|amount" lines
            'is_active'           => 'boolean',
            'sort_order'          => 'nullable|integer',
        ]);

        $data['services']           = $this->lines($data['services_text']  ?? '');
        $data['required_documents'] = $this->lines($data['documents_text'] ?? '');
        $data['process']            = $this->lines($data['process_text']   ?? '');
        $data['fees']               = $this->fees($data['fees_text']       ?? '');
        $data['is_active']          = (bool) ($data['is_active'] ?? false);

        unset($data['services_text'], $data['documents_text'], $data['process_text'], $data['fees_text']);
        return $data;
    }

    private function lines(string $s): array
    {
        return collect(preg_split('/\r?\n/', $s))->map(fn ($x) => trim($x))->filter()->values()->all();
    }

    private function fees(string $s): array
    {
        return collect(preg_split('/\r?\n/', $s))->map(function ($x) {
            $x = trim($x); if ($x === '') return null;
            $parts = array_map('trim', explode('|', $x, 2));
            return ['service' => $parts[0] ?? '—', 'amount' => $parts[1] ?? '—'];
        })->filter()->values()->all();
    }
}
