<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class ActivityLogger
{
    public static function log(string $action, string $context = null, string $message = null, array $meta = [], string $actorType = null, $actorId = null): void
    {
        try {
            $actorType = $actorType ?? (auth()->check() ? 'user' : (session('student_id') ? 'student' : 'system'));
            $actorId = $actorId ?? (auth()->id() ?? session('student_id'));

            DB::table('activity_logs')->insert([
                'time' => now(),
                'actor_type' => $actorType,
                'actor_id' => $actorId,
                'action' => $action,
                'context' => $context,
                'message' => $message,
                'meta' => empty($meta) ? null : json_encode($meta),
                'ip' => request()->ip(),
                'user_agent' => substr(request()->userAgent() ?? '', 0, 255),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // swallow to avoid breaking main flow
        }
    }
}


