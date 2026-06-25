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
    public function index()
    {
        // Eager load category to prevent N+1 query issue
        $tasks = Task::with('category')->get();
        return response()->json([
            'success' => true,
            'message' => 'Tasks fetched successfully',
            'data' => TaskResource::collection($tasks)->resolve(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // ইনপুট ভ্যালিডেশন
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id', // ক্যাটাগরি আইডিটি অবশ্যই categories টেবিলে থাকতে হবে
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_completed' => 'nullable|boolean',
            'due_date' => 'nullable|date',
        ]);

        // ডাটাবেজে সেভ
        $task = Task::create($validated);

        // সেভ করার পর ক্যাটাগরি রিলেশন লোড করে নেওয়া
        $task->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'data' => (new TaskResource($task))->resolve(),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        // টাস্ক দেখানোর সময় তার ক্যাটাগরি লোড করে নেওয়া
        $task->load('category');
        return response()->json([
            'success' => true,
            'message' => 'Task fetched successfully',
            'data' => (new TaskResource($task))->resolve(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        // ইনপুট ভ্যালিডেশন
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_completed' => 'required|boolean',
            'due_date' => 'nullable|date',
        ]);

        $task->update($validated);

        // আপডেট করার পর ক্যাটাগরি রিলেশন লোড করা
        $task->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'data' => (new TaskResource($task))->resolve(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully',
        ]);
    }
}
