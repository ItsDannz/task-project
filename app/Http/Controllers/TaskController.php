<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Task;
use App\Models\TaskList;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    try {
        \Log::info('=== TASKS INDEX DEBUG ===');
        \Log::info('User ID:', [auth()->id()]);
        
        $query = Task::with('list')
            ->whereHas('list', function($q) {
                $q->where('user_id', auth()->id());
            })
            ->orderBy('created_at', 'desc');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by completion status
        if ($request->has('filter') && $request->filter !== 'all') {
            $query->where('is_completed', $request->filter === 'completed');
        }

        $tasks = $query->paginate(10);
        
        // Ubah ini - gunakan 'lists' bukan 'task_lists'
        $lists = \DB::table('lists')->where('user_id', auth()->id())->get();

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'lists' => $lists,
            'filters' => [
                'search' => $request->input('search', ''),
                'filter' => $request->input('filter', 'all'),
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error')
            ]
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Tasks Index Error:', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return back()->with('error', 'Error loading tasks: ' . $e->getMessage());
    }
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'due_date' => 'nullable|date',
        'list_id' => 'required|exists:lists,id',  // ← UBAH task_lists jadi lists
        'is_completed' => 'nullable|boolean'
    ]);

    $validated['is_completed'] = $validated['is_completed'] ?? false;

    Task::create($validated);

    return redirect()->route('tasks.index')
        ->with('success', 'Task Created Successfully');
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        // Check ownership
        if (!$task->list || $task->list->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'list_id' => 'required|exists:lists,id', // ← ubah di sini
            'is_completed' => 'boolean'
        ]);

        $task->update($validated);

        return redirect()->route('tasks.index')
            ->with('success', 'Task Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        // Check ownership
        if ($task->list->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $task->delete();

        return redirect()->route('tasks.index')
            ->with('success', 'Task Deleted Successfully');
    }
}