<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CurrentChannelController extends Controller
{
    /**
     * Update the current channel for the current team.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $team = $request->user()->currentTeam;

        $request->validate([
            'channel_id' => ['required', 'integer', 'exists:channels,id'],
        ]);

        // Verificar se o canal pertence à equipe atual
        $channel = $team->channels()->find($request->channel_id);

        if (!$channel) {
            return back()->withErrors([
                'channel_id' => 'O canal selecionado não pertence a esta equipe.',
            ]);
        }

        $team->update([
            'last_selected_channel_id' => $request->channel_id,
        ]);

        return back(fallback: route('dashboard'));
    }
}

