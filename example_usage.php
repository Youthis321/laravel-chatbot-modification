<?php

/**
 * Contoh penggunaan Laravel ChatBot dengan GROQ API
 *
 * File ini menunjukkan cara menggunakan package setelah diinstall di Laravel
 */

// Contoh Controller
class ChatController extends Controller
{
    public function index()
    {
        // List semua thread
        $threads = ChatBot::listThreads();

        return view('chat.index', compact('threads'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        // Buat thread baru
        $thread = ChatBot::createThread($request->message, auth()->id());

        return response()->json([
            'success' => true,
            'thread' => $thread,
            'response' => $thread->threadMessages->last()->content,
        ]);
    }

    public function show($id)
    {
        // Ambil thread dengan messages
        $thread = ChatBot::thread($id, auth()->id());

        return view('chat.show', compact('thread'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        // Lanjutkan percakapan
        $response = ChatBot::updateThread($request->message, $id, auth()->id());

        return response()->json([
            'success' => true,
            'response' => $response->content,
        ]);
    }

    public function destroy($id)
    {
        // Hapus thread
        ChatBot::deleteThread($id, auth()->id());

        return response()->json(['success' => true]);
    }
}

// Contoh Artisan Command
class ChatCommand extends Command
{
    protected $signature = 'chat:test {message}';

    protected $description = 'Test GROQ API dengan message';

    public function handle()
    {
        $message = $this->argument('message');

        $this->info("Mengirim pesan: {$message}");

        // Buat thread baru
        $thread = ChatBot::createThread($message);

        $this->info('Response: '.$thread->threadMessages->last()->content);

        return 0;
    }
}

// Contoh Service Class
class ChatService
{
    public function createThreadWithSystemPrompt(string $userMessage, ?string $systemPrompt = null): Model
    {
        $fullMessage = $systemPrompt ? "{$systemPrompt}\n\n{$userMessage}" : $userMessage;

        return ChatBot::createThread($fullMessage);
    }

    public function getThreadHistory(int $threadId): Collection
    {
        $thread = ChatBot::thread($threadId);

        return $thread->threadMessages->map(function ($message) {
            return [
                'role' => $message->role,
                'content' => $message->content,
                'created_at' => $message->created_at,
            ];
        });
    }

    public function searchThreads(string $query, ?int $userId = null): LengthAwarePaginator
    {
        return ChatBot::listThreads($userId, $query);
    }
}

// Contoh Blade View (resources/views/chat/index.blade.php)
/*
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <h2>Chat History</h2>

            @foreach($threads as $thread)
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">{{ $thread->subject }}</h5>
                        <p class="card-text">
                            <small class="text-muted">
                                {{ $thread->created_at->diffForHumans() }}
                            </small>
                        </p>
                        <a href="{{ route('chat.show', $thread->id) }}" class="btn btn-primary">
                            Lihat Percakapan
                        </a>
                    </div>
                </div>
            @endforeach

            {{ $threads->links() }}
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Mulai Chat Baru</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('chat.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <textarea name="message" class="form-control" rows="3"
                                placeholder="Tulis pesan Anda..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-success">Kirim</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
*/

// Contoh Routes (routes/web.php)
/*
Route::middleware('auth')->group(function () {
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat', [ChatController::class, 'create'])->name('chat.store');
    Route::get('/chat/{id}', [ChatController::class, 'show'])->name('chat.show');
    Route::put('/chat/{id}', [ChatController::class, 'update'])->name('chat.update');
    Route::delete('/chat/{id}', [ChatController::class, 'destroy'])->name('chat.destroy');
});
*/

// Contoh .env configuration
/*
# ChatBot Configuration (Modified Version)
CHATBOT_PROVIDER=groq

# GROQ Configuration
GROQ_API_KEY=gsk_your_actual_groq_api_key_here
GROQ_MODEL=llama-3.1-8b-instant

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_chatbot
DB_USERNAME=root
DB_PASSWORD=
*/

// Contoh Migration untuk custom fields
/*
Schema::table('threads', function (Blueprint $table) {
    $table->string('category')->nullable()->after('subject');
    $table->json('metadata')->nullable()->after('category');
});

Schema::table('thread_messages', function (Blueprint $table) {
    $table->integer('token_count')->nullable()->after('content');
    $table->decimal('cost', 8, 6)->nullable()->after('token_count');
});
*/
