<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Http\Resources\TaskResource;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) // Request অবজেক্ট প্যারামিটার হিসেবে নেওয়া হলো
    {
        // শুধুমাত্র বর্তমান লগইন করা ইউজারের টাস্কগুলো নিয়ে আসবে
        $tasks = $request->user()->tasks()->with('category')->get();
        
        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // ইনপুট ভ্যালিডেশন
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_completed' => 'nullable|boolean',
            'due_date' => 'nullable|date',
        ]);

        // লগইন করা ইউজারের রিলেশন ব্যবহার করে টাস্ক তৈরি (এতে user_id অটোমেটিক বসে যাবে)
        $task = $request->user()->tasks()->create($validated);

        $task->load('category');

        return new TaskResource($task);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Task $task)
    {
        // টাস্কটি বর্তমান ইউজারের কিনা যাচাই করা (অন্য ইউজারের হলে ৪0৩ এরর দেবে)
        abort_if($task->user_id !== $request->user()->id, 403, 'Unauthorized.');

        $task->load('category');
        return new TaskResource($task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        // টাস্কটি বর্তমান ইউজারের কিনা যাচাই করা
        abort_if($task->user_id !== $request->user()->id, 403, 'Unauthorized.');

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_completed' => 'required|boolean',
            'due_date' => 'nullable|date',
        ]);

        $task->update($validated);
        $task->load('category');

        return new TaskResource($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Task $task)
    {
        // টাস্কটি বর্তমান ইউজারের কিনা যাচাই করা
        abort_if($task->user_id !== $request->user()->id, 403, 'Unauthorized.');

        $task->delete();

        return response()->noContent();
    }
}
