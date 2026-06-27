<?php

namespace App\Models;

use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
            // যদি টাস্ক সম্পন্ন না হয়ে থাকে, due_date থাকে এবং তা আজকের দিনের চেয়ে ছোট হয়
            if ($task->status !== 'completed' && $task->due_date && $task->due_date < now()->toDateString()) {
                // স্ট্যাটাস যদি আগে থেকে 'due' না হয়ে থাকে, তবে তা 'due' করে ডাটাবেজে সেভ করবে
                if ($task->status !== 'due') {
                    $task->status = 'due';
                    $task->saveQuietly(); // saveQuietly() ব্যবহার করলে পুনরায় এই ইভেন্ট লুপে পড়বে না
                }
            }
        });
    }
}
