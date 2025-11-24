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
}

