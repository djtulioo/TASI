<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChannelController extends Controller
{
    /**
     * Display a listing of the channels.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $channels = Channel::with('team')->get();

        return response()->json([
            'success' => true,
            'data' => $channels
        ], 200);
    }

    /**
     * Store a newly created channel in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
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

        if ($validator->fails()) {
            // Se for requisição Inertia, retorna redirect com erros
            if ($request->header('X-Inertia')) {
                return back()->withErrors($validator->errors(), 'createChannel');
            }

            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Converter strings JSON para arrays
        if (isset($data['other_api_params']) && !empty($data['other_api_params'])) {
            $data['other_api_params'] = json_decode($data['other_api_params'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                if ($request->header('X-Inertia')) {
                    return back()->withErrors(['other_api_params' => 'Formato JSON inválido'], 'createChannel');
                }
                return response()->json([
                    'success' => false,
                    'errors' => ['other_api_params' => 'Formato JSON inválido']
                ], 422);
            }
        }

        if (isset($data['chatbot_config']) && !empty($data['chatbot_config'])) {
            $data['chatbot_config'] = json_decode($data['chatbot_config'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                if ($request->header('X-Inertia')) {
                    return back()->withErrors(['chatbot_config' => 'Formato JSON inválido'], 'createChannel');
                }
                return response()->json([
                    'success' => false,
                    'errors' => ['chatbot_config' => 'Formato JSON inválido']
                ], 422);
            }
        }

        $channel = Channel::create($data);

        // Se for requisição Inertia, retorna redirect
        if ($request->header('X-Inertia')) {
            return redirect()->route('dashboard')->with('success', 'Canal criado com sucesso!');
        }

        return response()->json([
            'success' => true,
            'message' => 'Canal criado com sucesso',
            'data' => $channel
        ], 201);
    }

    /**
     * Display the specified channel.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $channel = Channel::with('team')->find($id);

        if (!$channel) {
            return response()->json([
                'success' => false,
                'message' => 'Canal não encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $channel
        ], 200);
    }

    /**
     * Update the specified channel in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $channel = Channel::find($id);

        if (!$channel) {
            return response()->json([
                'success' => false,
                'message' => 'Canal não encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'team_id' => 'sometimes|required|integer|exists:teams,id',
            'name' => 'sometimes|required|string|max:255',
            'official_whatsapp_number' => 'sometimes|required|string|max:255',
            'app_id' => 'sometimes|required|string|max:255',
            'app_secret' => 'sometimes|required|string|max:255',
            'access_token' => 'sometimes|required|string',
            'phone_number_id' => 'sometimes|required|string|max:255',
            'other_api_params' => 'nullable|array',
            'chatbot_config' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $channel->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Canal atualizado com sucesso',
            'data' => $channel
        ], 200);
    }

    /**
     * Remove the specified channel from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $channel = Channel::find($id);

        if (!$channel) {
            return response()->json([
                'success' => false,
                'message' => 'Canal não encontrado'
            ], 404);
        }

        $channel->delete();

        return response()->json([
            'success' => true,
            'message' => 'Canal excluído com sucesso'
        ], 200);
    }
}

