<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    use HasFactory;

    protected $fillable = ['book_id', 'assignee_id', 'created_by', 'title', 'due_date', 'is_done'];

    protected $casts = [
        'due_date' => 'date:Y-m-d',
        'is_done'  => 'boolean',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    // 画面(Alpine.js)に渡す形に変換する
    public function toFront(): array
    {
        return [
            'id'       => $this->id,
            'title'    => $this->title,
            'assignee' => $this->assignee_id ? 'member-' . $this->assignee_id : 'all',
            'due'      => $this->due_date?->format('Y-m-d'),
            'done'     => $this->is_done,
        ];
    }
}