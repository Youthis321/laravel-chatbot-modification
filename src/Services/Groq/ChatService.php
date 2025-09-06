<?php

namespace HalilCosdu\ChatBot\Services\Groq;

use HalilCosdu\ChatBot\Models\Thread;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ChatService
{
    protected string $model;

    public function __construct(public $client)
    {
        $this->model = config('chatbot.models.thread', Thread::class);
    }

    public function index(mixed $ownerId = null, mixed $search = null, mixed $appends = null): LengthAwarePaginator
    {
        return (new $this->model)::query()
            ->when($search, fn ($query) => $query->where('subject', 'like', "%{$search}%"))
            ->when($ownerId, fn ($query) => $query->where('owner_id', $ownerId))
            ->latest()
            ->when($appends, fn ($query) => $query->paginate()->appends($appends), fn ($query) => $query->paginate());
    }

    public function create(string $subject, mixed $ownerId = null): Model|Builder
    {
        // Create a new thread in database
        $thread = (new $this->model)::query()->create([
            'owner_id' => $ownerId,
            'subject' => Str::words($subject, 10),
            'remote_thread_id' => 'groq_'.uniqid(), // Generate unique ID for GROQ
        ]);

        // Get response from GROQ API
        $response = $this->client->chat()->completions()->create([
            'model' => config('chatbot.groq_model', 'llama-3.1-8b-instant'),
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $subject,
                ],
            ],
        ]);

        // Save user message
        $thread->threadMessages()->create([
            'role' => 'user',
            'content' => $subject,
        ]);

        // Save assistant response
        $thread->threadMessages()->create([
            'role' => 'assistant',
            'content' => $response->choices[0]->message->content,
        ]);

        $thread->load('threadMessages');

        return $thread;
    }

    public function show(int $id, mixed $ownerId = null): Model|Builder
    {
        return (new $this->model)::query()
            ->with('threadMessages')
            ->when($ownerId, fn ($query) => $query->where('owner_id', $ownerId))
            ->findOrFail($id);
    }

    public function update(string $message, int $id, mixed $ownerId = null)
    {
        $thread = (new $this->model)::query()
            ->with('threadMessages')
            ->when($ownerId, fn ($query) => $query->where('owner_id', $ownerId))
            ->findOrFail($id);

        // Save user message
        $thread->threadMessages()->create([
            'role' => 'user',
            'content' => $message,
        ]);

        // Prepare conversation history for GROQ API
        $messages = $thread->threadMessages->map(function ($msg) {
            return [
                'role' => $msg->role,
                'content' => $msg->content,
            ];
        })->toArray();

        // Add the new user message
        $messages[] = [
            'role' => 'user',
            'content' => $message,
        ];

        // Get response from GROQ API
        $response = $this->client->chat()->completions()->create([
            'model' => config('chatbot.groq_model', 'llama-3.1-8b-instant'),
            'messages' => $messages,
        ]);

        // Save assistant response
        $assistantMessage = $thread->threadMessages()->create([
            'role' => 'assistant',
            'content' => $response->choices[0]->message->content,
        ]);

        $thread->load('threadMessages');

        return $assistantMessage;
    }

    public function delete(int $id, mixed $ownerId = null): void
    {
        $thread = (new $this->model)::query()
            ->when($ownerId, fn ($query) => $query->where('owner_id', $ownerId))
            ->findOrFail($id);

        $thread->delete();
    }
}
