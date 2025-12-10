<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'team_id',
        'name',
        'avatar_path',
        'type',
        'telegram_bot_token',
        'official_whatsapp_number',
        'app_id',
        'app_secret',
        'access_token',
        'phone_number_id',
        'other_api_params',
        'chatbot_config',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'other_api_params' => 'array',
        'chatbot_config' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the team that owns the channel.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the conversations for the channel.
     */
    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Get the feedback entries for the channel.
     */
    public function feedbackEntries()
    {
        return $this->hasMany(FeedbackEntry::class);
    }

    /**
     * Get the reports for the channel.
     * Uncomment when Report model is created
     */
    // public function reports()
    // {
    //     return $this->hasMany(Report::class);
    // }

    /**
     * Get the avatar URL.
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar_path) {
            return asset('storage/' . $this->avatar_path);
        }
        return null;
    }
    /**
     * Get IDs of all channels that share the same Bot Identity.
     */
    public function sameBotChannelIds()
    {
        // Se for WhatsApp e tiver ID do telefone
        if ($this->phone_number_id) {
            return self::where('phone_number_id', $this->phone_number_id)
                ->pluck('id')
                ->toArray();
        }

        // Se for Telegram e tiver token
        if ($this->telegram_bot_token) {
            return self::where('telegram_bot_token', $this->telegram_bot_token)
                ->pluck('id')
                ->toArray();
        }

        // Fallback: retorna apenas o próprio ID
        return [$this->id];
    }
}

