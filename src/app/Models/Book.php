<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Book extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'member_limit', 'code', 'owner_id'];

    protected $casts = [
        'last_activity_at' => 'datetime',
    ];

    public function members()
    {
        return $this->belongsToMany(User::class)->withPivot('last_read_at')->withTimestamps();
    }

    public function todos()
    {
        return $this->hasMany(Todo::class);
    }

    // 本の中で動きがあったことを記録する(操作した本人は既読にする)
    public function touchActivity(?int $userId = null): void
    {
        $now = now();

        $this->forceFill(['last_activity_at' => $now])->save();

        if ($userId) {
            $this->members()->updateExistingPivot($userId, ['last_read_at' => $now]);
        }
    }

    // この本の日記(メンバー全員の分)
    public function diaryEntries()
    {
        return $this->hasMany(DiaryEntry::class);
    }

    // 重複しない8文字のコードを作る
    public static function generateCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (static::where('code', $code)->exists());

        return $code;
    }
}