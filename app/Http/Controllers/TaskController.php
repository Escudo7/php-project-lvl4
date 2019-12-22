<?php

namespace App\Http\Controllers;

use App\Task;
use App\User;
use App\Tag;
use App\TaskStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    const NEW_TASK_STATUS_NUMBER = 1;
    const WORKING_TASK_STATUS_NUMBER = 2;
    
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $users = User::all();
        $tags = Tag::all();
        $statuses = TaskStatus::all();

        $query = Task::orderBy('id');
        $data = $request->all();
        if (isset($data['filter']['myTasks'])) {
            $query = $query->myTasks($request->user()->id);
        } else {
            $query = array_reduce(array_keys($data), function ($acc, $key) use ($data) {
                if (!isset($data[$key])) {
                    return $acc;
                }
                switch ($key) {
                    case 'creator':
                        return $acc->creator($data[$key]);
                    case 'executor':
                        return $acc->executor($data[$key]);
                    case 'status':
                        return $acc->status($data[$key]);
                    case 'tag':
                        return $acc->tag($data[$key]);
                    default:
                        return $acc;
                }
            }, $query);
        }
        $tasks = $query->paginate(10);
        return view('task.index', compact('tasks', 'users', 'tags', 'statuses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $task = new Task();
        $users = User::all();
        $tags = Tag::all();
        return view('task.create', compact('task', 'users', 'tags'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!\Auth::check()) {
            session()->flash('error', __('You must be logged in to perform this action'));
            return redirect()->route('home.index');
        }
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'assignedTo_id' => ['exists:users,id', 'nullable'],
            'tags' => ['exists:tags,id', 'nullable']
        ]);

        $statusNewTask = TaskStatus::find(self::NEW_TASK_STATUS_NUMBER);
        $task = new Task();
        $task->fill($request->except('tags', 'newTag'));
        $task->creator()->associate($request->user());
        $task->status()->associate($statusNewTask);
        $task->save();

        if ($request['tags']) {
            $task->tags()->sync($request['tags']);
        }

        if ($request['newTag']) {
            $dataNewTag = ['name' => $request['newTag']];
            $newTag = Tag::create($dataNewTag);
            $task->tags()->attach($newTag);
        }

        session()->flash('success', __('Task was created successfully'));
        return redirect()->route('tasks.show', $task);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Task $task)
    {
        $message = [
            'success' => session('success'),
            'warning' => session('warning'),
            'error' => session('error')
        ];
        $user = $request->user();
        $comment = new \App\Comment();
        return view('task.show', compact('task', 'message', 'user', 'comment'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Task $task)
    {
        $user = $request->user();
        if (Gate::forUser($user)->denies('edit-task', $task)) {
            session()->flash('error', __('You do not have enough authority to perform these actions'));
            return redirect()->route('tasks.show', $task);
        }

        $users = User::all();
        $statuses = TaskStatus::all();
        $tags = Tag::all();
        return view('task.edit', compact('task', 'users', 'statuses', 'tags'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Task $task)
    {
        $user = $request->user();

        $typeUpdate = $request['type'];
        switch ($typeUpdate) {
            case 'globalUpdate':
                
                if (Gate::forUser($user)->denies('edit-task', $task)) {
                    session()->flash('error', __('You do not have enough authority to perform these actions'));
                    return redirect()->route('tasks.show', $task);
                }
                $request->validate([
                    'name' => ['required', 'string', 'max:255'],
                    'status_id' => ['required', 'exists:task_statuses,id'],
                    'assignedTo_id' => ['exists:users,id', 'nullable'],
                    'tags' => ['exists:tags,id', 'nullable']
                ]);

                $task->fill($request->except('type', 'dropTags', 'tags', 'newTag'));
                if ($request['dropTags']) {
                    $task->tags()->sync([]);
                }
                if ($request['tags']) {
                    $task->tags()->sync($request['tags']);
                }
                if ($request['newTag']) {
                    $newTag = Tag::create(['name' => $request['newTag']]);
                    $task->tags()->attach($newTag);
                }
                $task->save();

                session()->flash('success', __('Task has been changed'));
                break;

            case 'getTask':
                if (!\Auth::check()) {
                    session()->flash('error', __('You must be logged in to perform this action'));
                    return redirect()->route('home.index');
                }
                if ($task->executor != null) {
                    session()->flash('error', __('Task already has executor'));
                    return redirect()->route('tasks.show', $task);
                }

                $task->executor()->associate($user);
                $statusWorkingTask = TaskStatus::find(self::WORKING_TASK_STATUS_NUMBER);
                $task->status()->associate($statusWorkingTask);
                $task->save();

                session()->flash('success', __('You have successfully taken the task!'));
                break;

            case 'abandonTask':
                if (Gate::forUser($user)->denies('abandon-task', $task)) {
                    session()->flash('error', __('You do not have enough authority to perform these actions'));
                    return redirect()->route('tasks.show', $task);
                }

                $task->executor()->dissociate();
                $notWorkingTaskStatus = TaskStatus::find(self::NEW_TASK_STATUS_NUMBER);
                $task->status()->associate($notWorkingTaskStatus);
                $task->save();
                
                session()->flash('warning', __('You abandoned task'));
                break;
        }
        return redirect()->route('tasks.show', $task);
    }
}
