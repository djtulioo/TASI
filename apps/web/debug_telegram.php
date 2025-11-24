<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Configuração
$botToken = '123456789:ABCdefGHIjklMNOpqrsTUVwxyz'; // Token fictício para teste
$chatId = '987654321';

// Cria um canal de teste se não existir
$channel = \App\Models\Channel::firstOrCreate(
    ['telegram_bot_token' => $botToken],
    [
        'team_id' => 1, // Assumindo que existe um time com ID 1
        'name' => 'Canal de Teste Telegram',
        'type' => 'telegram',
        'official_whatsapp_number' => null,
        'app_id' => null,
        'app_secret' => null,
        'access_token' => null,
        'phone_number_id' => null,
    ]
);

echo "Canal de teste garantido: {$channel->name} (ID: {$channel->id})\n";

// Payload simulando uma mensagem do Telegram
$payload = [
    'update_id' => 123456789,
    'message' => [
        'message_id' => rand(1000, 9999),
        'from' => [
            'id' => $chatId,
            'is_bot' => false,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'johndoe',
            'language_code' => 'en',
        ],
        'chat' => [
            'id' => $chatId,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'johndoe',
            'type' => 'private',
        ],
        'date' => time(),
        'text' => 'Olá, isso é um teste de integração com Telegram via script interno!',
    ],
];

echo "Simulando webhook do Telegram internamente...\n";
echo "Rota: /api/webhook/telegram/{$botToken}\n";

// Cria a requisição manualmente
$request = Illuminate\Http\Request::create(
    "/api/webhook/telegram/{$botToken}",
    'POST',
    [],
    [],
    [],
    ['CONTENT_TYPE' => 'application/json'],
    json_encode($payload)
);

// Processa a requisição
$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $httpKernel->handle($request);

echo "Status Code: " . $response->getStatusCode() . "\n";
echo "Response Content: " . $response->getContent() . "\n";

// Verifica se a conversa foi criada
$conversation = \App\Models\Conversation::where('channel_id', $channel->id)
    ->latest()
    ->first();

if ($conversation) {
    echo "\nSucesso! Conversa criada no banco de dados:\n";
    echo "ID: {$conversation->id}\n";
    echo "Mensagem: {$conversation->message_body}\n";
    echo "Direção: {$conversation->direction}\n";
} else {
    echo "\nAviso: Nenhuma conversa encontrada no banco de dados.\n";
}
