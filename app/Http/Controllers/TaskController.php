<?php

namespace App\Http\Controllers;

use App\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use function GuzzleHttp\Promise\all;

class TaskController extends Controller
{

    private $success_status = 200;

    // ------------- [ Create New Task ] ----------------
    public function createTask(Request $request)
    {
        $user   =   Auth::user();



        $validator = Validator::make($request->all(),
            [
                'task_title' => 'required',
                'category'   => 'required',
                'description'   => 'required',
            ]
        );

        if($validator->fails()) {
            return response()->json(["validation_errors" => $validator->errors()]);
        }

        $taskInput      =       array(
            'task_title'    =>      $request->task_title,
            'category'      =>      $request->category,
            'description'   =>      $request->description,
            'user_id'       =>      $user->id
        );

        $task   =  Task::create($taskInput);

        if(!is_null($task)) {
            $success['status']  =   "success";
            $success['data']    =   $task;

        }

        return response()->json(['success' => $success], $this->success_status);
    }


    // --------------- [ Task Listing Based on User Auth Token ] ------------
    public function taskListing()
    {
        $user       =   Auth::user();
        $tasks       =   Task::where('user_id', $user->id)->get();
        
        $success['status']  =   "success";
        $success['data']    =   $tasks;
    

        return response()->json(['success' => $success]);
    }

    
    
// ---------------------- [ Task Detail ] -----------------------
    
    public function taskDetail($task_id)
    {
        $user           =       Auth::user();

        $task           =       Task::where("id",$task_id)->where('user_id', $user->id)->first();
        
        if(!is_null($task)) {

            $success['status']  =   "success";
            $success['data']    =   $task;
            return response()->json(['success' => $success]);
        }

        else {
            $success['status']  =   "failed";
            $success['message'] =   "Whoops! no detail found";

            return response()->json(['success' => $success]);
        }
    }

    
// ------------------ [ Update Task ] --------------
    
    public function updateTask(Request $request)
    {
        $user       =       Auth::user();

        $validator      =            Validator::make($request->all(),
            [
                'task_id'             =>         'required',
                'task_title'          =>         'required',
                'category'            =>         'required',
                'description'         =>         'required'
            ]
        );

        // if validation fails

        if($validator->fails()) {
            return response()->json(["validation errors" => $validator->errors()]);
        }

        $inputData      =       array(
            'task_title'        =>      $request->task_title,
            'category'          =>      $request->category,  
            'description'       =>      $request->description
        );

        $task       =   Task::where('id', $request->task_id)->where('user_id', $user->id)->update($inputData);
        
        if($task == 1) {
            $success['status']      =       "success";
            $success['message']     =       "Task has been updated successfully";
        }

        else {
            $success['status']      =       "failed";
            $success['message']     =       "Failed to update the task please try again";
        }
        
        return response()->json(['success' => $success], $this->success_status);       
        
    }


    // ---------------------- [ Delete Task ] --------------------------
    public function deleteTask($id) {

        $user       =       Auth::user();
        $task       =       Task::findOrFail($id);

        if(!is_null($task)) {
            $response   =   Task::where('id', $id)->delete();
            if($response == 1) {
                $success['status']  =   'success';
                $success['message'] =   'Task has been deleted successfully';
                return response()->json(['success' => $success], $this->success_status);
            }
        }
    }
}
