# Setup GROQ API untuk Laravel ChatBot (Modified Version)

## Langkah-langkah Setup

### 1. Dapatkan API Key GROQ

1. Kunjungi [GROQ Console](https://console.groq.com/login)
2. Buat akun atau login
3. Navigasi ke bagian "API Keys"
4. Klik "Create API Key"
5. Beri nama dan salin API key yang ditampilkan

### 2. Konfigurasi Environment

Tambahkan konfigurasi berikut ke file `.env`:

```env
# ChatBot Configuration
CHATBOT_PROVIDER=groq

# GROQ Configuration
GROQ_API_KEY=your_groq_api_key_here
GROQ_MODEL=llama-3.1-8b-instant

# Common Configuration
OPENAI_TIMEOUT=30
OPENAI_SLEEP_SECONDS=0.1
```

### 3. Model GROQ Production yang Tersedia

#### Chat Models (untuk percakapan):

-   `llama-3.1-8b-instant` (default, cepat dan efisien)

    -   Context Window: 131,072 tokens
    -   Max Completion: 131,072 tokens
    -   Cocok untuk: Chatbot umum, response cepat

-   `llama-3.3-70b-versatile` (lebih powerful, context window besar)

    -   Context Window: 131,072 tokens
    -   Max Completion: 32,768 tokens
    -   Cocok untuk: Analisis kompleks, reasoning yang lebih dalam

-   `openai/gpt-oss-120b` (model besar OpenAI)

    -   Context Window: 131,072 tokens
    -   Max Completion: 65,536 tokens
    -   Cocok untuk: Tugas kompleks, analisis mendalam

-   `openai/gpt-oss-20b` (model medium OpenAI)
    -   Context Window: 131,072 tokens
    -   Max Completion: 65,536 tokens
    -   Cocok untuk: Balance antara kecepatan dan kualitas

#### Specialized Models:

-   `meta-llama/llama-guard-4-12b` (untuk content moderation)

    -   Context Window: 131,072 tokens
    -   Max Completion: 1,024 tokens
    -   Cocok untuk: Filter konten, safety checks

-   `whisper-large-v3` (untuk speech-to-text)

    -   Max File Size: 100 MB
    -   Cocok untuk: Transkripsi audio

-   `whisper-large-v3-turbo` (whisper versi turbo)
    -   Max File Size: 100 MB
    -   Cocok untuk: Transkripsi audio cepat

### 4. Penggunaan

```php
use HalilCosdu\ChatBot\Facades\ChatBot;

// Membuat thread baru
$thread = ChatBot::createThread('Halo, bagaimana kabar?');

// Melanjutkan percakapan
$response = ChatBot::updateThread('Terima kasih!', $thread->id);

// Melihat semua thread
$threads = ChatBot::listThreads();

// Melihat thread tertentu
$thread = ChatBot::thread($threadId);

// Menghapus thread
ChatBot::deleteThread($threadId);
```

### 5. Perbedaan dengan OpenAI

-   GROQ menggunakan Chat Completions API, bukan Assistants API
-   Tidak memerlukan `assistant_id`
-   Lebih cepat dalam response time
-   Mendukung model yang berbeda

### 6. Testing

Untuk menguji konfigurasi:

```php
// Test basic functionality
$thread = ChatBot::createThread('Test message');
echo $thread->threadMessages->last()->content;
```

## Troubleshooting

### Error: "GROQ API Key is missing"

-   Pastikan `GROQ_API_KEY` sudah di-set di file `.env`
-   Pastikan `CHATBOT_PROVIDER=groq` sudah di-set

### Error: "Model not found"

-   Pastikan model yang digunakan tersedia di GROQ
-   Cek daftar model yang tersedia di [GROQ Documentation](https://console.groq.com/docs)

### Error: "Rate limit exceeded"

-   GROQ memiliki rate limit yang berbeda dari OpenAI
-   Tunggu beberapa saat sebelum mencoba lagi
