<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Achievement::where('is_active', true)
                ->orderByDesc('year')
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    public function store(Request $r)   { return response()->json(['data' => Achievement::create($this->validated($r))], 201); }
    public function update(Request $r, Achievement $achievement) { $achievement->update($this->validated($r)); return response()->json(['data' => $achievement->fresh()]); }
    public function destroy(Achievement $achievement) { $achievement->delete(); return response()->noContent(); }

    private function validated(Request $r): array
    {
        return $r->validate([
            'year'        => 'required|string|max:20',
            'title'       => 'required|string|max:200',
            'description' => 'nullable|string',
            'image_url'   => 'nullable|url',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'boolean',
        ]);
    }
}
