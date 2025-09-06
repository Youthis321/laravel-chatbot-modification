<?php

namespace HalilCosdu\ChatBot;

use HalilCosdu\ChatBot\Services\ChatBotService;
use HalilCosdu\ChatBot\Services\Groq\ChatService;
use HalilCosdu\ChatBot\Services\Groq\RawService as GroqRawService;
use HalilCosdu\ChatBot\Services\OpenAI\RawService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use OpenAI\Responses\Threads\Messages\ThreadMessageListResponse;
use OpenAI\Responses\Threads\Messages\ThreadMessageResponse;
use OpenAI\Responses\Threads\ThreadDeleteResponse;
use OpenAI\Responses\Threads\ThreadResponse;

readonly class ChatBot
{
    public function __construct(
        private ChatBotService $chatBotService,
        private RawService $rawService,
        private ?ChatService $groqChatService = null,
        private ?GroqRawService $groqRawService = null
    ) {
        //
    }

    public function listThreads(mixed $ownerId = null, mixed $search = null, mixed $appends = null): LengthAwarePaginator
    {
        $service = $this->getService();

        return $service->index($ownerId, $search, $appends);
    }

    public function createThread(string $subject, mixed $ownerId = null): Model|Builder
    {
        $service = $this->getService();

        return $service->create($subject, $ownerId);
    }

    public function thread(int $id, mixed $ownerId = null): Model|Builder
    {
        $service = $this->getService();

        return $service->show($id, $ownerId);
    }

    public function updateThread(string $message, int $id, mixed $ownerId = null): Model|Builder
    {
        $service = $this->getService();

        return $service->update($message, $id, $ownerId);
    }

    public function deleteThread(int $id, mixed $ownerId = null): void
    {
        $service = $this->getService();
        $service->delete($id, $ownerId);
    }

    private function getService(): ChatBotService|ChatService
    {
        $provider = config('chatbot.provider', 'openai');

        if ($provider === 'groq' && $this->groqChatService) {
            return $this->groqChatService;
        }

        return $this->chatBotService;
    }

    public function createThreadAsRaw(string $subject): ThreadResponse
    {
        $rawService = $this->getRawService();

        return $rawService->createThreadAsRaw($subject);
    }

    public function threadAsRaw(string $threadId): ThreadResponse
    {
        $rawService = $this->getRawService();

        return $rawService->threadAsRaw($threadId);
    }

    public function messageAsRaw($threadId, $messageId): ThreadMessageResponse
    {
        $rawService = $this->getRawService();

        return $rawService->messageAsRaw($threadId, $messageId);
    }

    public function modifyMessageAsRaw(string $threadId, string $messageId, array $parameters): ThreadMessageResponse
    {
        $rawService = $this->getRawService();

        return $rawService->modifyMessageAsRaw($threadId, $messageId, $parameters);
    }

    public function listThreadMessagesAsRaw(string $remoteThreadId): ThreadMessageListResponse
    {
        $rawService = $this->getRawService();

        return $rawService->listThreadMessagesAsRaw($remoteThreadId);
    }

    public function updateThreadAsRaw(string $remoteThreadId, array $data): ThreadResponse
    {
        $rawService = $this->getRawService();

        return $rawService->updateThreadAsRaw($remoteThreadId, $data);
    }

    public function deleteThreadAsRaw(string $remoteThreadId): ThreadDeleteResponse
    {
        $rawService = $this->getRawService();

        return $rawService->deleteThreadAsRaw($remoteThreadId);
    }

    private function getRawService(): RawService|GroqRawService
    {
        $provider = config('chatbot.provider', 'openai');

        if ($provider === 'groq' && $this->groqRawService) {
            return $this->groqRawService;
        }

        return $this->rawService;
    }
}
