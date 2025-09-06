<?php

/**
 * Test script untuk GROQ API integration.
 *
 * Jalankan dengan: php test_groq.php
 */

require_once 'vendor/autoload.php';

// Load environment variables from .env file
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Set default values if not set
$_ENV['CHATBOT_PROVIDER'] = $_ENV['CHATBOT_PROVIDER'] ?? 'groq';
$_ENV['GROQ_API_KEY'] = $_ENV['GROQ_API_KEY'] ?? 'your_groq_api_key_here';
$_ENV['GROQ_MODEL'] = $_ENV['GROQ_MODEL'] ?? 'llama-3.1-8b-instant';

// Simulasi Laravel config
function config($key, $default = null)
{
    $config = [
        'chatbot.provider' => $_ENV['CHATBOT_PROVIDER'] ?? 'openai',
        'chatbot.groq_api_key' => $_ENV['GROQ_API_KEY'] ?? null,
        'chatbot.groq_model' => $_ENV['GROQ_MODEL'] ?? 'llama-3.1-8b-instant',
        'chatbot.request_timeout' => 30,
        'chatbot.models.thread' => 'TestThread',
    ];

    return $config[$key] ?? $default;
}

// Test GROQ API connection
function testGroqConnection()
{
    echo "🧪 Testing GROQ API Connection...\n";

    $apiKey = config('chatbot.groq_api_key');
    $model = config('chatbot.groq_model');

    if (! $apiKey || $apiKey === 'your_groq_api_key_here') {
        echo "❌ GROQ API Key belum di-set!\n";
        echo "   Silakan set GROQ_API_KEY di environment atau ganti di file ini.\n";

        return false;
    }

    try {
        $client = OpenAI::factory()
            ->withApiKey($apiKey)
            ->withBaseUri('https://api.groq.com/openai/v1')
            ->withHttpClient(new GuzzleHttp\Client(['timeout' => 30]))
            ->make();

        $response = $client->chat()->create([
            'model' => $model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => 'Hello! Can you respond in Indonesian?',
                ],
            ],
            'max_tokens' => 100,
        ]);

        echo "✅ GROQ API Connection berhasil!\n";
        echo "📝 Model: {$model}\n";
        echo '🤖 Response: '.$response->choices[0]->message->content."\n";

        return true;
    } catch (Exception $e) {
        echo '❌ Error: '.$e->getMessage()."\n";

        return false;
    }
}

// Test model availability
function testModelAvailability()
{
    echo "\n🧪 Testing Model Availability...\n";

    $models = [
        'llama-3.1-8b-instant',
        'llama-3.3-70b-versatile',
        'openai/gpt-oss-120b',
        'openai/gpt-oss-20b',
    ];

    $apiKey = config('chatbot.groq_api_key');

    if (! $apiKey || $apiKey === 'your_groq_api_key_here') {
        echo "❌ GROQ API Key belum di-set!\n";

        return false;
    }

    try {
        $client = OpenAI::factory()
            ->withApiKey($apiKey)
            ->withBaseUri('https://api.groq.com/openai/v1')
            ->withHttpClient(new GuzzleHttp\Client(['timeout' => 30]))
            ->make();

        foreach ($models as $model) {
            try {
                $response = $client->chat()->create([
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => 'Test',
                        ],
                    ],
                    'max_tokens' => 10,
                ]);

                echo "✅ Model {$model} tersedia\n";
            } catch (Exception $e) {
                echo "❌ Model {$model} tidak tersedia: ".$e->getMessage()."\n";
            }
        }
    } catch (Exception $e) {
        echo '❌ Error testing models: '.$e->getMessage()."\n";
    }
}

// Main test function
function runTests()
{
    echo "🚀 GROQ API Integration Test\n";
    echo "============================\n\n";

    try {
        // Test 1: Basic connection
        $connectionOk = testGroqConnection();

        if ($connectionOk) {
            // Test 2: Model availability
            testModelAvailability();

            echo "\n✅ Semua test berhasil!\n";
            echo "🎉 GROQ API siap digunakan dengan Laravel ChatBot package.\n";
        } else {
            echo "\n❌ Test gagal. Silakan periksa konfigurasi API key.\n";
        }
    } catch (Exception $e) {
        echo '❌ Error: '.$e->getMessage()."\n";
        echo 'Stack trace: '.$e->getTraceAsString()."\n";
    }
}

// Run tests
runTests();
