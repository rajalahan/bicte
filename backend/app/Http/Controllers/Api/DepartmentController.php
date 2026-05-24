<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Department::active()->get(),
        ]);
    }

    public function show(string $slug)
    {
        $dept = Department::where('slug', $slug)->active()->firstOrFail();
        return response()->json(['data' => $dept]);
    }

    /* ---- admin write ---- */

    public function store(Request $request)
    {
        $data = $this->validated($request);
        return response()->json(['data' => Department::create($data)], 201);
    }

    public function update(Request $request, Department $department)
    {
        $department->update($this->validated($request, $department->id));
        return response()->json(['data' => $department->fresh()]);
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return response()->noContent();
    }

    private function validated(Request $r, ?int $id = null): array
    {
        return $r->validate([
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
            'services'            => 'nullable|array',
            'required_documents'  => 'nullable|array',
            'process'             => 'nullable|array',
            'fees'                => 'nullable|array',
            'forms'               => 'nullable|array',
            'sort_order'          => 'nullable|integer',
            'is_active'           => 'boolean',
        ]);
    }
}
