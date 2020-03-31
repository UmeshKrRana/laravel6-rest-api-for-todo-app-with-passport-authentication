<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\User;

class UserController extends Controller
{
    private $success_status = 200;

    public function registerUser(Request $request)
    {
        // --------------------- [ User Registration ] ---------------------------

        $validator  =   Validator::make($request->all(),
            [
                'name'              =>      'required',
                'email'             =>      'required',
                'password'          =>      'required'
            ]
        );

        // if validation fails
        if($validator->fails()) {
            return response()->json(["validation errors" => $validator->errors()]);
        }

        $input              =       array(
            'name'              =>        $request->name,
            'email'             =>        $request->email,
            'password'          =>        bcrypt($request->password),
            'type'              =>        "admin",
            'role'              =>        "admin"
        );

        $user               =           User::create($input);
        return response()->json(["success" => true, "status" => "success", "user" => $user]);

    }

    // --------------------------- [ User Login ] ------------------------------

    public function loginUser(Request $request) {

        $validator = Validator::make($request->all(),
            [
                'email'            =>         'required',
                'password'         =>         'required',
            ]
        );

        // check if validation fails
        if($validator->fails()) {
            return response()->json(["validation errors" => $validator->errors()]);
        }

        $email  =   $request->email;
        $password = $request->password;

        $user   =   DB::table("users")->where("email", "=", $email)->first();

        if(is_null($user)) {

            return response()->json(["success" => false, "message" => "Email doesn't exist"]);

        }

        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])) {

            $user = Auth::user();

            $token                  =       $user->createToken('token')->accessToken;
            $success['success']     =       true;
            $success['message']     =       "Success! you are logged in successfully";
            $success['token']       =       $token;

            return response()->json(['success' => $success ], $this->success_status);

        }

        else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }


    // ---------------------------- [ Use Detail ] -------------------------------
    public function userDetail() {
        $user       =       Auth::user();
        return response()->json(['success' => $user], $this->success_status);

    }



    // -------------------------- [ Edit Using Passport Auth ]--------------------
    public function update(Request $request) {
        $user           =             Auth::user();

        $validator      =            Validator::make($request->all(),
            [
                'name'             =>         'required',
                'email'            =>         'required',
                'password'         =>         'required',
            ]
        );

        // if validation fails
        if($validator->fails()) {
            return response()->json(["validation errors" => $validator->errors()]);
        }

        $userDataArray      =     array(
            'name'              =>          $request->name,
            'email'             =>          $request->email,
            'password'          =>          bcrypt($request->password)
        );

        $user           =       User::where('id', $user->id)->update($userDataArray);

        return response()->json(['success' => true, 'message' => 'User updated successfully']);

    }


// ----------------------------- [ Delete User ] -----------------------------
    public function deleteUser($id) {
        $user           =           Auth::user();

        if(!is_null($user)) {

            // check if user type is super admin
            if(($user->is_admin == 1) && ($user->user_type == 1) && ($user->role == "admin")) {
                $typeUser           =           User::findOrFail($id);

                if(is_null($typeUser)) {
                    return response()->json(["success" => false, "status" => "failed", "message" => "Whoops! user doesn't exist"]);
                }

                // if enter user is not admin
                if($typeUser->is_admin == 0 && $typeUser->role == "user") {
                    $typeUser->delete();
                    return response()->json(['success' => true, "status" => "success", "message" => "User deleted successfully"]);
                }

                else {
                    return response()->json(["success" => false, "status" => "warning", "message" => "Sorry! you are not allowed to delete other admin user"]);
                }
            }

            else{
                return response()->json(['error' => 'Unauthorised user'], 401);
            }
        }
    }
}
