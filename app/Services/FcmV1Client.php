<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class FcmV1Client
{
    protected string $projectId;
    protected array $credentials;
    protected string $cacheKey;

    public function __construct(?string $projectId = null, ?string $credentialsPath = null)
    {
        $credentialsPath = $credentialsPath ?: Config::get('fcm.credentials_path');
        // Permitir credenciales inline via env FIREBASE_CREDENTIALS_JSON (Base64 del JSON)
        $inline = env('FIREBASE_CREDENTIALS_JSON');
        if ($inline) {
            $decoded = base64_decode($inline, true);
            if ($decoded === false) {
                throw new RuntimeException('FIREBASE_CREDENTIALS_JSON inválido (Base64 no decodificable)');
            }
            $json = $decoded;
        } else {
            // Normalizar ruta relativa -> absoluta
            if ($credentialsPath && !preg_match('/^([A-Za-z]:\\\\|\/)/', $credentialsPath)) { // no absolute windows ni unix
                $possible = [
                    base_path($credentialsPath),
                    storage_path(trim($credentialsPath, '\\/')),
                ];
                foreach ($possible as $p) {
                    if (is_file($p)) { $credentialsPath = $p; break; }
                }
            }
            if (!is_file($credentialsPath)) {
                throw new RuntimeException("No se encontró el archivo de credenciales en: {$credentialsPath}");
            }
            $json = file_get_contents($credentialsPath);
        }
        $this->credentials = json_decode($json, true) ?: [];
        if (empty($this->credentials)) {
            throw new RuntimeException('No se pudieron leer las credenciales JSON.');
        }
        $this->projectId = $projectId ?: (Config::get('fcm.project_id') ?: ($this->credentials['project_id'] ?? ''));
        if (!$this->projectId) {
            throw new RuntimeException('FIREBASE_PROJECT_ID no configurado y project_id faltante en credenciales.');
        }
        $kid = $this->credentials['private_key_id'] ?? Str::random(8);
        $this->cacheKey = 'fcm_v1_token_' . $kid;
    }

    public function sendToToken(string $token, string $title, string $body, array $data = [], array $options = []): array
    {
        $message = [
            'token' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => array_map('strval', $data),
        ];
        if (!empty($options['android'])) {
            $message['android'] = $options['android'];
        }
        if (!empty($options['apns'])) {
            $message['apns'] = $options['apns'];
        }
        return $this->sendMessage($message);
    }

    public function sendToTopic(string $topic, string $title, string $body, array $data = [], array $options = []): array
    {
        $message = [
            'topic' => $topic,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => array_map('strval', $data),
        ];
        if (!empty($options['android'])) {
            $message['android'] = $options['android'];
        }
        if (!empty($options['apns'])) {
            $message['apns'] = $options['apns'];
        }
        return $this->sendMessage($message);
    }

    public function sendMessage(array $message): array
    {
        $url = sprintf('https://fcm.googleapis.com/v1/projects/%s/messages:send', $this->projectId);
        $token = $this->getAccessToken();

        $response = Http::withToken($token)
            ->acceptJson()
            ->post($url, ['message' => $message]);

        return [
            'ok' => $response->successful(),
            'status' => $response->status(),
            'body' => $response->json() ?? $response->body(),
        ];
    }

    protected function getAccessToken(): string
    {
        $cached = Cache::get($this->cacheKey);
        if (is_array($cached) && isset($cached['access_token'], $cached['expires_at']) && $cached['expires_at'] > time()) {
            return $cached['access_token'];
        }

        $jwt = $this->createJwtAssertion();
        $resp = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);
        if (!$resp->successful()) {
            throw new RuntimeException('No se pudo obtener el access token de Google: ' . $resp->body());
        }
        $data = $resp->json();
        $accessToken = $data['access_token'] ?? null;
        $expiresIn = $data['expires_in'] ?? 3600;
        if (!$accessToken) {
            throw new RuntimeException('Respuesta inválida de token, falta access_token.');
        }
        $cushion = (int) Config::get('fcm.token_cushion', 60);
        $expiresAt = time() + max(60, (int)$expiresIn - $cushion);
        Cache::put($this->cacheKey, [
            'access_token' => $accessToken,
            'expires_at' => $expiresAt,
        ], $expiresIn - $cushion);
        return $accessToken;
    }

    protected function createJwtAssertion(): string
    {
        $now = time();
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
            'kid' => $this->credentials['private_key_id'] ?? null,
        ];
        $claims = [
            'iss' => $this->credentials['client_email'] ?? '',
            'sub' => $this->credentials['client_email'] ?? '',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
            'scope' => implode(' ', Config::get('fcm.scopes', [])),
        ];
        $segments = [
            $this->base64url(json_encode($header)),
            $this->base64url(json_encode($claims)),
        ];
        $signingInput = implode('.', $segments);
        $privateKey = $this->credentials['private_key'] ?? '';
        $signature = '';
        $ok = openssl_sign($signingInput, $signature, openssl_pkey_get_private($privateKey), OPENSSL_ALGO_SHA256);
        if (!$ok) {
            throw new RuntimeException('No se pudo firmar el JWT con la clave privada.');
        }
        $segments[] = $this->base64url($signature);
        return implode('.', $segments);
    }

    protected function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
