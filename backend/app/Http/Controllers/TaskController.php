<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Task::query();

        // Filter berdasarkan pencarian judul
        if ($request->filled('search')) {
            $query->where('judul', 'like', '%'.$request->string('search').'%');
        }

        // Filter berdasarkan status ('belum' atau 'selesai')
        if ($request->filled('status') && in_array($request->status, ['belum', 'selesai'], true)) {
            $query->where('status', $request->status);
        }

        // Paginasi (default 10 data per halaman)
        $perPage = min(max((int) $request->get('per_page', 10), 1), 100);
        $tasks = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar tugas berhasil dimuat.',
            'data' => $tasks,
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TaskRequest $request)
    {
        $this->authorize('create', Task::class);

        $task = Task::create([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'tenggat' => $request->tenggat,
            'kelas' => $request->kelas,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil dibuat.',
            'data' => $task,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $task->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil diperbarui.',
            'data' => $task,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil dihapus.',
        ], 200);
    }

    public function toggleComplete(Task $task)
    {
        $this->authorize('complete', $task);

        // Toggle status
        $task->status = $task->status === 'selesai' ? 'belum' : 'selesai';
        $task->save();

        return response()->json([
            'success' => true,
            'message' => 'Status tugas berhasil diperbarui.',
            'data' => $task,
        ], 200);
    }
}
