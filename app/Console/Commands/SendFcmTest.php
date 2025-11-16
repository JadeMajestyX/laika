<?php

namespace App\Console\Commands;

use App\Services\FcmV1Client;
use Illuminate\Console\Command;

class SendFcmTest extends Command
{
    protected $signature = 'fcm:send {--token=} {--topic=} {--title=} {--body=} {--data=*}';
    protected $description = 'Enviar un mensaje de prueba via FCM HTTP v1';

    public function handle(): int
    {
        $token = $this->option('token');
        $topic = $this->option('topic');
        $title = (string) ($this->option('title') ?? 'Prueba');
        $body = (string) ($this->option('body') ?? 'Mensaje de prueba');
        $dataPairs = (array) $this->option('data');
        $data = [];
        foreach ($dataPairs as $pair) {
            if (strpos($pair, '=') !== false) {
                [$k, $v] = explode('=', $pair, 2);
                $data[$k] = $v;
            }
        }

        if (!$token && !$topic) {
            $this->error('Debe especificar --token o --topic');
            return self::FAILURE;
        }

        $client = new FcmV1Client();

        if ($token) {
            $res = $client->sendToToken($token, $title, $body, $data);
        } else {
            $res = $client->sendToTopic($topic, $title, $body, $data);
        }

        $this->info('Status: ' . ($res['status'] ?? 'n/a'));
        $this->line('Respuesta: ' . (is_string($res['body']) ? $res['body'] : json_encode($res['body'])));
        return ($res['ok'] ?? false) ? self::SUCCESS : self::FAILURE;
    }
}
