<?php

namespace App\Support;

use App\Models\NotificationLog;

class NotificationLogger
{
    /**
     * Registra una notificación enviada.
     */
    public static function log(?int $userId, string $title, string $body, array $data, array $tokens, array $resultsSummary): void
    {
        try {
            NotificationLog::create([
                'user_id' => $userId,
                'title' => $title,
                'body' => $body,
                'data_json' => !empty($data) ? json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                'tokens_json' => !empty($tokens) ? json_encode($tokens, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                'success' => $resultsSummary['success'] ?? 0,
                'fail' => $resultsSummary['fail'] ?? 0,
                'total' => $resultsSummary['sent'] ?? count($tokens),
                'results_json' => isset($resultsSummary['results']) ? json_encode($resultsSummary['results'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            ]);
        } catch (\Throwable $e) {
            // Silenciar errores de logging
        }
    }
}
