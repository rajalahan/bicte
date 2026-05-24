<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Faq;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * AssistantController – AI / FAQ-backed citizen helper.
 *
 * Pipeline:
 *   1. Rate-limit per IP (kiosk abuse guard).
 *   2. Local intent matcher over Faq + Department service text.
 *   3. If ASSISTANT_PROVIDER is configured, augment the answer with an
 *      LLM (OpenAI-compatible or Ollama) using retrieved chunks as
 *      context. Falls back gracefully if the LLM call fails.
 */
class AssistantController extends Controller
{
    public function ask(Request $r)
    {
        $q = trim((string) $r->input('q', ''));
        if ($q === '') {
            return response()->json(['answer' => 'कृपया प्रश्न लेख्नुहोस्।'], 422);
        }

        // Rate limit – 30 questions / minute / IP
        $key = 'asst:' . $r->ip();
        if (RateLimiter::tooManyAttempts($key, 30)) {
            return response()->json(['answer' => 'धेरै प्रश्न पठाइयो, केहीबेरमा प्रयास गर्नुहोस्।'], 429);
        }
        RateLimiter::hit($key, 60);

        $hit = $this->localMatch($q);

        $provider = config('services.assistant.provider', env('ASSISTANT_PROVIDER', 'local'));
        if ($provider !== 'local') {
            try {
                $hit = $this->llmAugment($q, $hit, $provider);
            } catch (\Throwable $e) {
                // log and fall through to local hit
                logger()->warning('assistant.llm_failed', ['err' => $e->getMessage()]);
            }
        }

        return response()->json($hit);
    }

    /** Score Faq + Department text against the question, return best match. */
    private function localMatch(string $q): array
    {
        $qNorm = $this->normalize($q);

        $best = null; $bestScore = 0;
        foreach (Faq::where('is_active', true)->with('department:id,slug,name')->get() as $f) {
            $score = 0;
            $hay = collect([$f->intent, $f->question])
                ->merge((array) $f->keywords)
                ->map(fn ($k) => $this->normalize($k));
            foreach ($hay as $k) {
                if ($k && Str::contains($qNorm, $k)) $score += Str::length($k);
            }
            if ($score > $bestScore) { $bestScore = $score; $best = $f; }
        }

        if ($best && $bestScore >= 2) {
            $tail = $best->department
                ? "<div class='msg__hint'>सम्बन्धित शाखा: <a href='department.html?slug={$best->department->slug}'>{$best->department->name}</a></div>"
                : '';
            return [
                'answer'      => strip_tags($best->answer_ne ?: $best->answer_en ?: ''),
                'answer_html' => nl2br($best->answer_ne ?: $best->answer_en ?: '') . $tail,
                'source'      => 'faq',
                'faq_id'      => $best->id,
            ];
        }

        // Department fuzzy fallback
        $hits = Department::active()
            ->get()
            ->filter(function (Department $d) use ($qNorm) {
                $hay = $this->normalize(implode(' ',
                    array_merge([$d->name, $d->name_en, $d->summary],
                        $d->services ?? [], $d->required_documents ?? [])));
                foreach (preg_split('/\s+/', $qNorm) as $tok) {
                    if (Str::length($tok) >= 2 && Str::contains($hay, $tok)) return true;
                }
                return false;
            })
            ->take(5);

        if ($hits->isNotEmpty()) {
            $list = $hits->map(fn ($d) => "<li><a href='department.html?slug={$d->slug}'>{$d->name}</a> — " . e($d->summary) . '</li>')->implode('');
            return [
                'answer'      => 'सम्बन्धित हुन सक्ने शाखाहरू मिले।',
                'answer_html' => "सम्बन्धित हुन सक्ने शाखाहरू:<br><ul>{$list}</ul>",
                'source'      => 'departments',
            ];
        }

        return [
            'answer'      => 'माफ गर्नुहोस्, यो प्रश्नको जवाफ अहिले मसँग छैन।',
            'answer_html' => 'माफ गर्नुहोस्, यो प्रश्नको जवाफ अहिले मसँग छैन। 🙇<br>कृपया अरू शब्दमा सोध्नुहोस्।',
            'source'      => 'fallback',
        ];
    }

    /** Augment the answer with an LLM using the local match as context. */
    private function llmAugment(string $q, array $base, string $provider): array
    {
        $context = strip_tags($base['answer_html'] ?? $base['answer'] ?? '');

        $cacheKey = 'asst:' . md5($provider . '|' . $q . '|' . $context);
        return Cache::remember($cacheKey, 3600, function () use ($q, $context, $provider, $base) {
            $sys = "तपाईं लहान नगरपालिकाको AI नागरिक सहायक हुनुहुन्छ। नागरिकलाई औपचारिक नेपालीमा छोटो, स्पष्ट, र विनम्र जवाफ दिनुहोस्। दिइएको context प्रयोग गर्नुहोस्; नभए 'कृपया सम्बन्धित शाखामा सम्पर्क गर्नुहोस्' भन्नुहोस्।";

            if ($provider === 'openai') {
                $r = Http::timeout(10)
                    ->withToken(env('ASSISTANT_API_KEY'))
                    ->post(env('ASSISTANT_ENDPOINT', 'https://api.openai.com/v1/chat/completions'), [
                        'model'    => env('ASSISTANT_MODEL', 'gpt-4o-mini'),
                        'messages' => [
                            ['role' => 'system', 'content' => $sys],
                            ['role' => 'user',   'content' => "Context:\n{$context}\n\nQ: {$q}"],
                        ],
                        'temperature' => 0.2,
                    ])->throw()->json();
                $text = $r['choices'][0]['message']['content'] ?? '';
            } elseif ($provider === 'ollama') {
                $r = Http::timeout(20)
                    ->post(rtrim(env('ASSISTANT_ENDPOINT', 'http://127.0.0.1:11434'), '/') . '/api/generate', [
                        'model'  => env('ASSISTANT_MODEL', 'llama3'),
                        'prompt' => $sys . "\n\nContext:\n{$context}\n\nQ: {$q}\nA:",
                        'stream' => false,
                    ])->throw()->json();
                $text = $r['response'] ?? '';
            } else {
                $text = '';
            }

            if ($text === '') return $base;
            return array_merge($base, [
                'answer'      => $text,
                'answer_html' => nl2br(e($text)),
                'source'      => 'llm:' . $provider,
            ]);
        });
    }

    private function normalize(string $s): string
    {
        $s = mb_strtolower($s);
        $s = preg_replace('/[।,.?!\-_\/()]+/u', ' ', $s);
        $s = preg_replace('/\s+/u', ' ', $s);
        return trim($s);
    }
}
