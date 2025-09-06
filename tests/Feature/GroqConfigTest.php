<?php

it('configures GROQ provider correctly', function () {
    config(['chatbot.provider' => 'groq']);

    expect(config('chatbot.provider'))->toBe('groq');
});

it('configures GROQ API key', function () {
    config(['chatbot.groq_api_key' => 'test_groq_key']);

    expect(config('chatbot.groq_api_key'))->toBe('test_groq_key');
});

it('configures GROQ model', function () {
    config(['chatbot.groq_model' => 'llama-3.1-8b-instant']);

    expect(config('chatbot.groq_model'))->toBe('llama-3.1-8b-instant');
});

it('has correct default GROQ model', function () {
    $defaultModel = config('chatbot.groq_model', 'llama-3.1-8b-instant');

    expect($defaultModel)->toBe('llama-3.1-8b-instant');
});

it('supports multiple GROQ models', function () {
    $models = [
        'llama-3.1-8b-instant',
        'llama-3.3-70b-versatile',
        'openai/gpt-oss-120b',
        'openai/gpt-oss-20b',
    ];

    foreach ($models as $model) {
        config(['chatbot.groq_model' => $model]);
        expect(config('chatbot.groq_model'))->toBe($model);
    }
});

it('can switch between providers', function () {
    // Test OpenAI provider
    config(['chatbot.provider' => 'openai']);
    expect(config('chatbot.provider'))->toBe('openai');

    // Test GROQ provider
    config(['chatbot.provider' => 'groq']);
    expect(config('chatbot.provider'))->toBe('groq');
});
