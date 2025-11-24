<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Log;

echo "Listando modelos do Gemini...\n";

$apiKey = env('GEMINI_API_KEY');

if (!$apiKey) {
    die("ERRO: GEMINI_API_KEY não definida no .env\n");
}
echo "Usando chave iniciando com: " . substr($apiKey, 0, 5) . "\n";

try {
    $guzzle = new \GuzzleHttp\Client(['verify' => false]);
    $client = \Gemini::factory()
        ->withApiKey($apiKey)
        ->withHttpClient($guzzle)
        ->make();

    $response = $client->models()->list();
    
    foreach ($response->models as $model) {
        if (in_array('generateContent', $model->supportedGenerationMethods)) {
            echo "- " . $model->name . " (Supported methods: " . implode(', ', $model->supportedGenerationMethods) . ")\n";
        }
    }

    echo "\nTestando geração de conteúdo com gemini-2.0-flash...\n";
    $result = $client->generativeModel(model: 'gemini-2.0-flash')
        ->generateContent('Olá, isso é um teste.');
    echo "Resposta: " . $result->text() . "\n";

} catch (\Exception $e) {
    echo "Erro ao listar modelos: " . $e->getMessage() . "\n";
}
