<?php

use HalilCosdu\ChatBot\Models\Thread;
use HalilCosdu\ChatBot\Models\ThreadMessage;

// config for HalilCosdu/ChatBot

return [
    // API Provider: 'openai' or 'groq'
    'provider' => env('CHATBOT_PROVIDER', 'openai'),
    
    // OpenAI Configuration
    'assistant_id' => env('OPENAI_API_ASSISTANT_ID'),
    'api_key' => env('OPENAI_API_KEY'),
    'organization' => env('OPENAI_ORGANIZATION'),
    
    // GROQ Configuration
    'groq_api_key' => env('GROQ_API_KEY'),
    'groq_model' => env('GROQ_MODEL', 'llama-3.1-8b-instant'),
    
    // Common Configuration
    'request_timeout' => env('OPENAI_TIMEOUT', 30),
    'sleep_seconds' => env('OPENAI_SLEEP_SECONDS', 0.1),
    'models' => [
        'thread' => env('CHATBOT_THREAD_MODEL', Thread::class),
        'thread_messages' => env('CHATBOT_THREAD_MESSAGE_MODEL', ThreadMessage::class),
    ],
];
