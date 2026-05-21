<?php

namespace App\Http\Controllers;

use App\Models\AuthorizedFace;
use App\Models\FaceChallenge;
use App\Models\FaceVerificationSession;
use App\Models\KycProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FaceVerificationController extends Controller
{
    /* ============================================================
     | Helpers
     |============================================================ */

    private function aiUrl(string $path): string
    {
        return rtrim(config('services.face_ai.url', 'http://127.0.0.1:9000'), '/') . $path;
    }

    private function httpClient()
    {
        return Http::timeout(config('services.face_ai.timeout', 120))
            ->asMultipart();
    }

    private function cosineSimilarity(array $a, array $b): float
    {
        $dot = $normA = $normB = 0.0;

        foreach ($a as $i => $v) {
            $va = (float) $v;
            $vb = (float) ($b[$i] ?? 0.0);
            $dot   += $va * $vb;
            $normA += $va * $va;
            $normB += $vb * $vb;
        }

        return $dot / (sqrt($normA) * sqrt($normB) + 1e-12);
    }

    private function normalizeAiPayload(?array $payload): array
    {
        return is_array($payload) ? $payload : [];
    }

    /**
     * تحويل أخطاء/حالات AI إلى Reason موحد يستهلكه React
     */
    private function mapAiErrorToReason(array $payload, string $fallback = 'enroll_failed'): string
    {
        $err = (string) ($payload['error'] ?? '');

        return match ($err) {
            'video_too_short'   => 'video_too_short',
            'face_unstable'     => 'face_unstable',
            'no_face'           => 'face_not_clear',
            'multiple_faces'    => 'multiple_faces',
            'embedding_failed'  => 'face_not_clear',
            'already_enrolled'  => 'already_enrolled',
            default             => $fallback,
        };
    }

    /* ============================================================
     | 1) Start Session (اختياري)
     |============================================================ */

    public function start()
    {
        $session = FaceVerificationSession::create([
            'session_id' => (string) Str::uuid(),
            'status'     => 'created',
        ]);

        return response()->json([
            'session_id' => $session->session_id,
        ]);
    }

    /* ============================================================
     | 2) Generate Challenge
     |============================================================ */

    public function challenge()
    {
        $pool = ['blink', 'smile', 'turn_left', 'turn_right'];
        $count   = random_int(2, 3);
        $actions = [];

        while (count($actions) < $count) {
            $pick = $pool[array_rand($pool)];
            if ($pick === 'blink' || !in_array($pick, $actions, true)) {
                $actions[] = $pick;
            }
        }

        $challenge = FaceChallenge::create([
            'challenge_id' => (string) Str::uuid(),
            'actions'      => $actions,
            'time_limit'   => 8,
            'expires_at'   => now()->addMinutes(2),
        ]);

        return response()->json([
            'challenge_id' => $challenge->challenge_id,
            'actions'      => $challenge->actions,
            'time_limit'   => $challenge->time_limit,
        ]);
    }

    /* ============================================================
     | 3) ENROLL
     |============================================================ */

    public function enroll(Request $request)
    {
        $userId = auth()->id();

        // إذا المستخدم مسجل مسبقًا
        if (AuthorizedFace::where('user_id', $userId)->exists()) {
            return response()->json([
                'success' => false,
                'reason'  => 'already_enrolled',
            ], 200);
        }

        // 422 هنا فقط للـ request validation
        $request->validate([
            'challenge_id' => ['required', 'uuid'],
            'video'        => ['required', 'file', 'mimes:webm,mp4,mov,avi'],
        ]);

        $challenge = FaceChallenge::where('challenge_id', $request->challenge_id)->firstOrFail();

        if ($challenge->isExpired() || $challenge->isUsed()) {
            return response()->json([
                'success' => false,
                'reason'  => 'challenge_invalid',
            ], 200);
        }

        // ارسل للـ AI
        $res = $this->httpClient()
            ->attach(
                'video',
                file_get_contents($request->file('video')->getRealPath()),
                'enroll.webm'
            )
            ->post($this->aiUrl('/liveness/check'), [
                ['name' => 'mode',         'contents' => 'enroll'],
                ['name' => 'challenge_id', 'contents' => $challenge->challenge_id],
                ['name' => 'actions',      'contents' => json_encode($challenge->actions)],
                ['name' => 'time_limit',   'contents' => (string) $challenge->time_limit],
            ]);

        $payload = $this->normalizeAiPayload($res->json());

        // نعتبر التحدي مستهلكًا (حتى لو فشل) لتقليل إعادة الاستخدام
        $challenge->update(['used_at' => now()]);

        // لو AI غير متاح أو رد غير صالح
        if (!$res->successful()) {
            return response()->json([
                'success' => false,
                'reason'  => 'network_error',
                'details' => $payload,
            ], 200);
        }

        // لو AI قال ok=false (خطأ بنيوي/منطقي في التحليل)
        if (($payload['ok'] ?? null) === false) {
            return response()->json([
                'success' => false,
                'reason'  => $this->mapAiErrorToReason($payload, 'enroll_failed'),
                'details' => $payload,
            ], 200);
        }

        // لازم يكون عندنا liveness key
        if (!array_key_exists('liveness', $payload)) {
            return response()->json([
                'success' => false,
                'reason'  => 'enroll_failed',
                'details' => $payload,
            ], 200);
        }

        // replay_suspected
        if (!empty($payload['replay_suspected'])) {
            return response()->json([
                'success' => false,
                'reason'  => 'replay_suspected',
                'details' => $payload,
            ], 200);
        }

        // قرار liveness
        if ($payload['liveness'] !== true) {
            return response()->json([
                'success' => false,
                'reason'  => 'liveness_failed',
                'details' => $payload,
            ], 200);
        }

        // embedding إلزامي بعد نجاح liveness
        if (!is_array($payload['embedding'] ?? null) || count($payload['embedding']) < 64) {
            return response()->json([
                'success' => false,
                'reason'  => 'face_not_clear',
                'details' => $payload,
            ], 200);
        }

        AuthorizedFace::create([
            'user_id'   => $userId,
            'embedding' => $payload['embedding'],
        ]);

        // تحديث حالة KYC (عدّل الحالات حسب نظامك)
        KycProfile::where('user_id', $userId)
            ->whereIn('status', ['face_required', 'verified']) // حسب تدفّقك
            ->update([
                'status' => 'face_enrolled',
            ]);

        return response()->json([
            'success' => true,
        ]);
    }

    /* ============================================================
     | 4) VERIFY
     |============================================================ */

    public function verify(Request $request)
    {
        $userId = auth()->id();

        $request->validate([
            'challenge_id' => ['required', 'uuid'],
            'video'        => ['required', 'file', 'mimes:webm,mp4,mov,avi'],
        ]);

        $challenge = FaceChallenge::where('challenge_id', $request->challenge_id)->firstOrFail();

        if ($challenge->isExpired() || $challenge->isUsed()) {
            return response()->json([
                'authorized' => false,
                'reason'     => 'challenge_invalid',
            ], 200);
        }

        $face = AuthorizedFace::where('user_id', $userId)->first();

        if (!$face) {
            return response()->json([
                'authorized' => false,
                'reason'     => 'not_enrolled',
            ], 200);
        }

        $res = $this->httpClient()
            ->attach(
                'video',
                file_get_contents($request->file('video')->getRealPath()),
                'verify.webm'
            )
            ->post($this->aiUrl('/liveness/check'), [
                ['name' => 'mode',         'contents' => 'verify'],
                ['name' => 'challenge_id', 'contents' => $challenge->challenge_id],
                ['name' => 'actions',      'contents' => json_encode($challenge->actions)],
                ['name' => 'time_limit',   'contents' => (string) $challenge->time_limit],
            ]);

        $payload = $this->normalizeAiPayload($res->json());
        $challenge->update(['used_at' => now()]);

        if (!$res->successful()) {
            return response()->json([
                'authorized' => false,
                'reason'     => 'network_error',
                'details'    => $payload,
            ], 200);
        }

        if (($payload['ok'] ?? null) === false) {
            return response()->json([
                'authorized' => false,
                'reason'     => $this->mapAiErrorToReason($payload, 'verify_failed'),
                'details'    => $payload,
            ], 200);
        }

        if (!array_key_exists('liveness', $payload)) {
            return response()->json([
                'authorized' => false,
                'reason'     => 'verify_failed',
                'details'    => $payload,
            ], 200);
        }

        if (!empty($payload['replay_suspected'])) {
            return response()->json([
                'authorized' => false,
                'reason'     => 'replay_suspected',
                'details'    => $payload,
            ], 200);
        }

        if ($payload['liveness'] !== true) {
            return response()->json([
                'authorized' => false,
                'reason'     => 'liveness_failed',
                'details'    => $payload,
            ], 200);
        }

        if (!is_array($payload['embedding'] ?? null) || count($payload['embedding']) < 64) {
            return response()->json([
                'authorized' => false,
                'reason'     => 'face_not_clear',
                'details'    => $payload,
            ], 200);
        }

        $score = $this->cosineSimilarity($payload['embedding'], $face->embedding);
        $threshold = (float) config('services.face_ai.match_threshold', 0.75);

        if ($score < $threshold) {
            return response()->json([
                'authorized'  => false,
                'reason'      => 'not_match',
                'match_score' => round($score, 4),
                'threshold'   => $threshold,
            ], 200);
        }

        KycProfile::where('user_id', $userId)
            ->where('status', 'face_enrolled')
            ->update(['status' => 'active']);

        return response()->json([
            'authorized'  => true,
            'match_score' => round($score, 4),
            'threshold'   => $threshold,
        ]);
    }
}
