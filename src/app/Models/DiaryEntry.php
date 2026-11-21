<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiaryEntry extends Model
{
    use HasFactory;

    // weather は 'sunny' / 'cloudy' / 'rainy' のどれか(空もOK)
    protected $fillable = ['book_id', 'user_id', 'date', 'weather', 'body'];

    // date は 'YYYY-MM-DD' の文字列のまま扱う。
    // cast で Carbon にすると、画面に渡すキーが '2026-11-21 00:00:00' になってしまうため。

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    // 日記を書いた人
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(DiaryComment::class);
    }
}