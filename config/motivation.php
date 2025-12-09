<?php

return [
    'provider' => env('MOTIVATION_PROVIDER', 'ollama'),

    'ollama' => [
        'url' => env('OLLAMA_URL', 'http://ollama:11434'),
        'model' => env('OLLAMA_MODEL', 'llama3.2'),
    ],
];

