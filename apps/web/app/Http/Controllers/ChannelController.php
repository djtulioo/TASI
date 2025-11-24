<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use App\Services\TelegramService;

class ChannelController extends Controller
{
    /**
     * Display a listing of the channels.
     */
    public function index()
    {
        $channels = Channel::with('team')->get();

        return Inertia::render('Channels/Index', [
            'channels' => $channels,
        ]);
    }

    /**
     * Store a newly created channel in storage.
     */
    public function store(Request $request, TelegramService $telegramService)
    {
        $validated = $request->validate([
            'team_id' => 'required|integer|exists:teams,id',
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:whatsapp,telegram',
            // WhatsApp fields (required if type is whatsapp)
            'official_whatsapp_number' => 'required_if:type,whatsapp|nullable|string|max:255',
            'app_id' => 'required_if:type,whatsapp|nullable|string|max:255',
            'app_secret' => 'required_if:type,whatsapp|nullable|string|max:255',
            'access_token' => 'required_if:type,whatsapp|nullable|string',
            'phone_number_id' => 'required_if:type,whatsapp|nullable|string|max:255',
            // Telegram fields (required if type is telegram)
            'telegram_bot_token' => 'required_if:type,telegram|nullable|string|max:255',
            
            'other_api_params' => 'nullable|string',
            'chatbot_config' => 'nullable', // Aceita array ou string JSON
        ]);

        // Converter strings JSON para arrays
        if (!empty($validated['other_api_params'])) {
            $validated['other_api_params'] = json_decode($validated['other_api_params'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw ValidationException::withMessages([
                    'other_api_params' => 'Formato JSON inválido',
                ]);
            }
        }

        // Se chatbot_config vier como string JSON (caso de form-data), decodifica. Se for array (JSON request), mantém.
        if (isset($validated['chatbot_config']) && is_string($validated['chatbot_config'])) {
            $validated['chatbot_config'] = json_decode($validated['chatbot_config'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw ValidationException::withMessages([
                    'chatbot_config' => 'Formato JSON inválido',
                ]);
            }
        }



        $channel = Channel::create($validated);

        // Se for Telegram, configura o webhook automaticamente
        if ($validated['type'] === 'telegram') {
            try {
                // URL do webhook: https://SEU_DOMINIO/api/webhook/telegram/{token}
                // Usando route() para gerar a URL completa
                // Como a rota é /webhook/telegram/{bot_token}, passamos o token como parâmetro
                // Mas a rota definida em api.php é 'webhook/telegram/{bot_token}'
                // Precisamos garantir que a URL seja acessível publicamente (HTTPS)
                
                $webhookUrl = route('api.webhook.telegram', ['bot_token' => $validated['telegram_bot_token']]);
                
                // Se estiver em ambiente local com ngrok, o route() pode gerar localhost
                // O usuário deve garantir que APP_URL no .env esteja correto (ex: ngrok)
                
                $telegramService->setWebhook($validated['telegram_bot_token'], $webhookUrl);
                
                // Tentar baixar o avatar do bot automaticamente
                $avatarPath = $telegramService->downloadBotAvatar($validated['telegram_bot_token']);
                if ($avatarPath) {
                    $channel->update(['avatar_path' => $avatarPath]);
                }
                
            } catch (\Exception $e) {
                // Se falhar o webhook, não impedimos a criação do canal, mas avisamos
                // Ou poderíamos deletar o canal e retornar erro.
                // Vamos apenas adicionar um aviso na sessão flash se possível, ou logar.
                // Como estamos redirecionando com 'success', podemos concatenar um aviso.
                return Redirect::route('dashboard')->with('success', 'Canal criado, mas falha ao configurar Webhook Telegram: ' . $e->getMessage());
            }
        }

        // Selecionar automaticamente o canal criado, se pertencer ao team atual
        $team = $request->user()->currentTeam;
        if ($channel->team_id === $team->id) {
            $team->update([
                'last_selected_channel_id' => $channel->id,
            ]);
        }

        return Redirect::route('dashboard')->with('success', 'Canal criado com sucesso!');
    }

    /**
     * Display the specified channel.
     */
    public function show(Channel $channel)
    {
        return Inertia::render('Channels/Show', [
            'channel' => $channel->load('team'),
        ]);
    }

    /**
     * Update the specified channel in storage.
     */
    public function update(Request $request, Channel $channel)
    {
        $validated = $request->validate([
            'team_id' => 'sometimes|required|integer|exists:teams,id',
            'name' => 'sometimes|required|string|max:255',
            'avatar' => 'nullable|image|max:2048', // 2MB max
            'official_whatsapp_number' => 'sometimes|required|string|max:255',
            'app_id' => 'sometimes|required|string|max:255',
            'app_secret' => 'sometimes|required|string|max:255',
            'access_token' => 'sometimes|required|string',
            'phone_number_id' => 'sometimes|required|string|max:255',
            'other_api_params' => 'nullable|string',
            'chatbot_config' => 'nullable',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($channel->avatar_path) {
                \Storage::disk('public')->delete($channel->avatar_path);
            }
            
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar_path'] = $path;
        }

        // Converter strings JSON para arrays
        if (isset($validated['other_api_params']) && !empty($validated['other_api_params'])) {
            $validated['other_api_params'] = json_decode($validated['other_api_params'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw ValidationException::withMessages([
                    'other_api_params' => 'Formato JSON inválido',
                ]);
            }
        }

        if (isset($validated['chatbot_config']) && is_string($validated['chatbot_config'])) {
            $validated['chatbot_config'] = json_decode($validated['chatbot_config'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw ValidationException::withMessages([
                    'chatbot_config' => 'Formato JSON inválido',
                ]);
            }
        }

        // Process URLs and files for context (simple implementation)
        if (isset($validated['chatbot_config'])) {
            $config = $validated['chatbot_config'];
            $additionalContext = '';

            // Extract content from URLs (basic implementation)
            if (isset($config['urls']) && is_array($config['urls'])) {
                foreach ($config['urls'] as $url) {
                    if (!empty($url)) {
                        try {
                            $content = @file_get_contents($url);
                            if ($content) {
                                $additionalContext .= "\n\n--- Conteúdo de $url ---\n" . strip_tags($content);
                            }
                        } catch (\Exception $e) {
                            // Silently fail for now
                        }
                    }
                }
            }

            // Append additional context to main context
            if (!empty($additionalContext)) {
                $config['context'] = ($config['context'] ?? '') . $additionalContext;
            }

            $validated['chatbot_config'] = $config;
        }

        $channel->update($validated);

        return Redirect::back()->with('success', 'Canal atualizado com sucesso!');
    }

    /**
     * Remove the specified channel from storage.
     */
    public function destroy(Channel $channel)
    {
        $team = $channel->team;

        // Se o canal sendo excluído é o selecionado atualmente
        if ($team->last_selected_channel_id === $channel->id) {
            // Tenta encontrar outro canal
            $otherChannel = $team->channels()->where('id', '!=', $channel->id)->first();
            
            $team->update([
                'last_selected_channel_id' => $otherChannel ? $otherChannel->id : null,
            ]);
        }

        $channel->delete();

        return Redirect::route('dashboard')->with('success', 'Canal excluído com sucesso!');
    }
}
