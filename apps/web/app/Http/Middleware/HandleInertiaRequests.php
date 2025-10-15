<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'channels' => function () use ($request) {
                if (!$request->user()) {
                    return [];
                }

                $currentTeam = $request->user()->currentTeam;

                if (!$currentTeam) {
                    return [];
                }

                return $currentTeam->channels()
                    ->select('id', 'name', 'team_id')
                    ->get()
                    ->map(function ($channel) {
                        return [
                            'id' => $channel->id,
                            'name' => $channel->name,
                        ];
                    });
            },
            'currentChannel' => function () use ($request) {
                if (!$request->user()) {
                    return null;
                }

                $currentTeam = $request->user()->currentTeam;

                if (!$currentTeam || !$currentTeam->last_selected_channel_id) {
                    return null;
                }

                return $currentTeam->lastSelectedChannel()
                    ->select('id', 'name')
                    ->first();
            },
        ];
    }
}
