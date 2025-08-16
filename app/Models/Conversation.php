<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_one',
        'user_two',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function userOne()
    {
        return $this->belongsTo(User::class, 'user_one');
    }

    public function userTwo()
    {
        return $this->belongsTo(User::class, 'user_two');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latest();
    }

    public function getOtherUser($userId)
    {
        return $this->user_one == $userId ? $this->userTwo : $this->userOne;
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_one', $userId)->orWhere('user_two', $userId);
    }

    public static function findBetweenUsers($userOne, $userTwo)
    {
        return self::where(function ($query) use ($userOne, $userTwo) {
            $query->where('user_one', $userOne)->where('user_two', $userTwo);
        })->orWhere(function ($query) use ($userOne, $userTwo) {
            $query->where('user_one', $userTwo)->where('user_two', $userOne);
        })->first();
    }

    public function unreadMessagesCount($userId)
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->count();
    }
}
