<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Faq::query()->where('is_active', true)
                ->orderBy('sort_order')
                ->with('department:id,slug,name')
                ->get()
                ->map(fn (Faq $f) => [
                    'id'         => $f->id,
                    'intent'     => $f->intent,
                    'keywords'   => $f->keywords,
                    'department' => optional($f->department)->slug,
                    'question'   => $f->question,
                    'answer_ne'  => $f->answer_ne,
                    'answer_en'  => $f->answer_en,
                ]),
        ]);
    }

    public function store(Request $r)
    {
        return response()->json(['data' => Faq::create($this->validated($r))], 201);
    }

    public function update(Request $r, Faq $faq)
    {
        $faq->update($this->validated($r));
        return response()->json(['data' => $faq->fresh()]);
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();
        return response()->noContent();
    }

    private function validated(Request $r): array
    {
        return $r->validate([
            'department_id' => 'nullable|exists:departments,id',
            'intent'        => 'required|string|max:80',
            'keywords'      => 'nullable|array',
            'question'      => 'required|string|max:300',
            'answer_ne'     => 'required|string',
            'answer_en'     => 'nullable|string',
            'sort_order'    => 'nullable|integer',
            'is_active'     => 'boolean',
        ]);
    }
}
