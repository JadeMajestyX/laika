<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\FcmV1Client;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('fcm:send {--token=} {--topic=} {--title=} {--body=} {--data=*}', function () {
    $token = (string) ($this->option('token') ?? '');
    $topic = (string) ($this->option('topic') ?? '');
    $title = (string) ($this->option('title') ?? 'Prueba');
    $body = (string) ($this->option('body') ?? 'Mensaje de prueba');
    $pairs = (array) $this->option('data');
    $data = [];
    foreach ($pairs as $pair) {
        if (str_contains($pair, '=')) {
            [$k, $v] = explode('=', $pair, 2);
            $data[$k] = $v;
        }
    }
    if (!$token && !$topic) {
        $this->error('Debe especificar --token o --topic');
        return 1;
    }
    $client = app(FcmV1Client::class);
    $res = $token
        ? $client->sendToToken($token, $title, $body, $data)
        : $client->sendToTopic($topic, $title, $body, $data);
    $this->info('Status: '.($res['status'] ?? 'n/a'));
    $this->line('Respuesta: '.(is_string($res['body']) ? $res['body'] : json_encode($res['body'])));
    return ($res['ok'] ?? false) ? 0 : 1;
})->purpose('Enviar un mensaje FCM HTTP v1');
