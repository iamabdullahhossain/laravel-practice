<?php

namespace App\Models;

use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'status',
        'due_date',
        'due_time',
    ];

    /**
     * Get the category that owns the task.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    protected static function booted(): void
    {
        static::retrieved(function (Task $task) {
            if ($task->status !== 'completed' && $task->due_date) {
                // ডেট এবং টাইম জোড়া দিয়ে কার্বন অবজেক্ট তৈরি করছি (টাইম না থাকলে দিনের শেষ সময় ধরা হবে)
                $dueDateTimeString = $task->due_date.($task->due_time ? ' '.$task->due_time : ' 23:59:59');
                $dueDateTime = Carbon::parse($dueDateTimeString);

                // যদি সময় পার হয়ে গিয়ে থাকে এবং স্ট্যাটাস due না থাকে
                if ($dueDateTime->isPast() && $task->status !== 'due') {
                    $task->status = 'due';
                    $task->saveQuietly();
                }
            }
        });
    }
}
