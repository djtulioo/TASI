<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'team_id' => 'required|integer|exists:teams,id',
            'name' => 'required|string|max:255',
            'official_whatsapp_number' => 'required|string|max:255',
            'app_id' => 'required|string|max:255',
            'app_secret' => 'required|string|max:255',
            'access_token' => 'required|string',
            'phone_number_id' => 'required|string|max:255',
            'other_api_params' => 'nullable|string',
            'chatbot_config' => 'nullable|string',
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

        if (!empty($validated['chatbot_config'])) {
            $validated['chatbot_config'] = json_decode($validated['chatbot_config'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw ValidationException::withMessages([
                    'chatbot_config' => 'Formato JSON inválido',
                ]);
            }
        }

        $channel = Channel::create($validated);

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
            'official_whatsapp_number' => 'sometimes|required|string|max:255',
            'app_id' => 'sometimes|required|string|max:255',
            'app_secret' => 'sometimes|required|string|max:255',
            'access_token' => 'sometimes|required|string',
            'phone_number_id' => 'sometimes|required|string|max:255',
            'other_api_params' => 'nullable|string',
            'chatbot_config' => 'nullable|string',
        ]);

        // Converter strings JSON para arrays
        if (isset($validated['other_api_params']) && !empty($validated['other_api_params'])) {
            $validated['other_api_params'] = json_decode($validated['other_api_params'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw ValidationException::withMessages([
                    'other_api_params' => 'Formato JSON inválido',
                ]);
            }
        }

        if (isset($validated['chatbot_config']) && !empty($validated['chatbot_config'])) {
            $validated['chatbot_config'] = json_decode($validated['chatbot_config'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw ValidationException::withMessages([
                    'chatbot_config' => 'Formato JSON inválido',
                ]);
            }
        }

        $channel->update($validated);

        return Redirect::back()->with('success', 'Canal atualizado com sucesso!');
    }

    /**
     * Remove the specified channel from storage.
     */
    public function destroy(Channel $channel)
    {
        $channel->delete();

        return Redirect::back()->with('success', 'Canal excluído com sucesso!');
    }
}
