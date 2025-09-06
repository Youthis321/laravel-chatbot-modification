<?php

namespace HalilCosdu\ChatBot;

use HalilCosdu\ChatBot\Services\ChatBotService;
use InvalidArgumentException;
use OpenAI as OpenAIFactory;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ChatBotServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-chatbot-modification')
            ->hasConfigFile()
            ->hasMigrations(
                [
                    'create_threads_table',
                    'create_thread_messages_table',
                ]
            );
    }

    public function packageRegistered(): void
    {
        $this->registerServices();
        $this->registerChatBot();
    }

    private function registerServices(): void
    {
        $provider = config('chatbot.provider', 'openai');

        if ($provider === 'groq') {
            $this->registerGroqServices();
        } else {
            $this->registerOpenAIServices();
        }
    }

    private function registerOpenAIServices(): void
    {
        $services = [
            ChatBotService::class,
            \HalilCosdu\ChatBot\Services\OpenAI\RawService::class,
        ];

        foreach ($services as $service) {
            $this->app->singleton($service, function () use ($service) {
                $apiKey = config('chatbot.api_key');
                $organization = config('chatbot.organization');

                if (! is_string($apiKey) || ($organization !== null && ! is_string($organization))) {
                    throw new InvalidArgumentException(
                        'The OpenAI API Key is missing. Please publish the [chatbot.php] configuration file and set the [api_key].'
                    );
                }

                $openAI = OpenAIFactory::factory()
                    ->withApiKey($apiKey)
                    ->withOrganization($organization)
                    ->withHttpHeader('OpenAI-Beta', 'assistants=v2')
                    ->withHttpClient(new \GuzzleHttp\Client(['timeout' => config('chatbot.request_timeout', 30)]))
                    ->make();

                return new $service($openAI);
            });
        }
    }

    private function registerGroqServices(): void
    {
        $services = [
            \HalilCosdu\ChatBot\Services\Groq\ChatService::class,
            \HalilCosdu\ChatBot\Services\Groq\RawService::class,
        ];

        foreach ($services as $service) {
            $this->app->singleton($service, function () use ($service) {
                $apiKey = config('chatbot.groq_api_key');

                if (! is_string($apiKey)) {
                    throw new InvalidArgumentException(
                        'The GROQ API Key is missing. Please publish the [chatbot.php] configuration file and set the [groq_api_key].'
                    );
                }

                $groqClient = OpenAIFactory::factory()
                    ->withApiKey($apiKey)
                    ->withBaseUri('https://api.groq.com/openai/v1')
                    ->withHttpClient(new \GuzzleHttp\Client(['timeout' => config('chatbot.request_timeout', 30)]))
                    ->make();

                return new $service($groqClient);
            });
        }
    }

    private function registerChatBot(): void
    {
        $this->app->singleton(ChatBot::class, function () {
            $provider = config('chatbot.provider', 'openai');

            if ($provider === 'groq') {
                return new ChatBot(
                    chatBotService: $this->app->make(\HalilCosdu\ChatBot\Services\ChatBotService::class),
                    rawService: $this->app->make(\HalilCosdu\ChatBot\Services\OpenAI\RawService::class),
                    groqChatService: $this->app->make(\HalilCosdu\ChatBot\Services\Groq\ChatService::class),
                    groqRawService: $this->app->make(\HalilCosdu\ChatBot\Services\Groq\RawService::class)
                );
            }

            return new ChatBot(
                chatBotService: $this->app->make(\HalilCosdu\ChatBot\Services\ChatBotService::class),
                rawService: $this->app->make(\HalilCosdu\ChatBot\Services\OpenAI\RawService::class)
            );
        });
    }
}
