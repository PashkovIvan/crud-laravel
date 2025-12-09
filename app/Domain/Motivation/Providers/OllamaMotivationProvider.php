<?php

namespace App\Domain\Motivation\Providers;

use App\Domain\Motivation\Contracts\MotivationProviderInterface;
use App\Domain\Motivation\Enums\MotivationType;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaMotivationProvider implements MotivationProviderInterface
{
    private string $baseUrl;
    private string $model;

    public function __construct()
    {
        $this->baseUrl = config('motivation.ollama.url', 'http://ollama:11434');
        $this->model = config('motivation.ollama.model', 'llama3.2');
    }

    public function generate(MotivationType $type): ?string
    {
        try {
            $response = Http::timeout(10)
                ->post("{$this->baseUrl}/api/generate", [
                    'model' => $this->model,
                    'prompt' => $type->prompt(),
                    'stream' => false,
                    'options' => [
                        'temperature' => 1.2,
                        'max_tokens' => 120,
                        'top_p' => 0.95,
                        'repeat_penalty' => 1.1,
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $message = trim($data['response'] ?? '');

                if (empty($message)) {
                    return null;
                }

                return $this->cleanMessage($message);
            }

            Log::warning('Ollama API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Throwable $e) {
            Log::error('Ollama motivation generation failed', [
                'error' => $e->getMessage(),
                'type' => $type->value,
            ]);

            return null;
        }
    }

    private function cleanMessage(string $message): string
    {
        $message = strip_tags($message);
        $message = preg_replace('/\s+/', ' ', $message);
        $message = trim($message);

        if (mb_strlen($message) > 150) {
            $message = mb_substr($message, 0, 147) . '...';
        }

        return $message;
    }
}

