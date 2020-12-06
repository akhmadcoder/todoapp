<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Task;
// use Auth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TasksController extends Controller
{
    public function __construct()
    {
        $this->middleware('api');
        // $this->middleware('auth:api')->except(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $tasks = Task::where('user_id', $request['user_id'])->orderBy("id", "desc")->get();
        return response()->json([
            'tasks' => $tasks,
        ]);
    }

    public function clearList(Request $request)
    {
        if(Task::where('user_id', 'like', $request['user_id'])->delete())
        {
            return response()->json(['status' => 'success']);
        }
        
    }

    public function changeStatus(Request $request)
    {
        $task = Task::findOrFail($request['id']);

        if($task->status == 1)
        {   
            $task->update(['status' => 0]);
            return response()->json(['status' => 'success']);
        } else {
            $task->update(['status' => 1]);
            return response()->json(['status' => 'success']);
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'content' => 'required|min:3|unique:tasks',
        ]);
        
        if(Task::create([
            'content' => $request['content'],
            'user_id' => $request['user_id'],
        ])){
                return response()->json(['status' => 'success']); 
           }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $this->validate($request,[
            'content' => 'required|min:3',
        ]);

        $task->update($request->all());
        
        return response()->json([
            'status' => 'success'
        ]);
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);

        //delete task
        try {

            if($task->delete())
            {
                return response()->json([
                    'status' => 'success',
                ]); 
            }
            
        } catch(\Illuminate\Database\QueryException $ex){ 

            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
                'cod' => 400
            ]);
        }
    }
}
