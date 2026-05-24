<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Official;
use Illuminate\Http\Request;

class OfficialController extends Controller
{
    public function index()
    {
        $list = Official::where('is_active', true)->orderBy('sort_order')->get();

        $byRole = $list->keyBy('role');

        return response()->json([
            'office' => [
                'name'    => config('app.name'),
                'phone'   => config('app.office_phone',   '+977-33-561234'),
                'email'   => config('app.office_email',   'info@lahanmun.gov.np'),
                'website' => config('app.office_website', 'https://lahanmun.gov.np'),
                'office_hours' => 'आइतबार – शुक्रबार: १०:०० AM – ५:०० PM',
            ],
            'mayor'  => $byRole->get('mayor'),
            'deputy' => $byRole->get('deputy'),
            'cao'    => $byRole->get('cao'),
        ]);
    }

    public function store(Request $r)   { return response()->json(['data' => Official::create($this->validated($r))], 201); }
    public function update(Request $r, Official $official) { $official->update($this->validated($r)); return response()->json(['data' => $official->fresh()]); }
    public function destroy(Official $official) { $official->delete(); return response()->noContent(); }

    private function validated(Request $r): array
    {
        return $r->validate([
            'role'        => 'required|in:mayor,deputy,cao,other',
            'name'        => 'required|string|max:200',
            'designation' => 'nullable|string|max:200',
            'photo'       => 'nullable|string|max:500',
            'phone'       => 'nullable|string|max:40',
            'email'       => 'nullable|email|max:200',
            'message'     => 'nullable|string',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'boolean',
        ]);
    }
}
