<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function signupAdmin(Request $request)
    {
        $response = null;
        $validator = Validator::make($request->all(), [
            "name" => "required|min:5",
            "email" => "required|unique:users",
            "password" => "required|min:8",
        ]);
        $user_data = array(
            "name" => $request->name,
            "email" => $request->email,
            "password" => $request->password,
            "role" => "admin"
        );
        if ($validator->fails()) {
            $response =  response()->json(["status" => "Fail", "message" => $validator->errors()], 422);
        } else {
            User::create($user_data);
            $response = response()->json(["users" => $user_data, "status" => "Success", "message" => "User has been created"], 202);
        }
        return $response;
    }

    public function signupAuthor(Request $request){
        $response = null;
        $validator = Validator::make($request->all(), [
            "name" => "required|min:5",
            "email" => "required|unique:users",
            "password" => "required|min:8",
        ]);
        $user_data = array(
            "name" => $request->name,
            "email" => $request->email,
            "password" => $request->password,
            "balance" => 0,
        );
        if ($validator->fails()) {
            $response =  response()->json(["status" => "Fail", "message" => $validator->errors()], 422);
        } else {
            User::create($user_data);
            $response = response()->json(["users" => $user_data, "status" => "Success", "message" => "User has been created"], 202);
        }
        return $response;
    }

    public function approve(Request $request, $user_id){
        DB::table('users')
        ->where('id', $user_id)
        ->update([
            'role' => "author"
        ]);
    }

    public function loginAdmin(Request $request){
        $email = $request->input('email');
    	$password = $request->input('password');

    	$user = User::where(['email'=>$email, 'password'=>$password])->first();
        if($user)
        {
            if($user->role=='admin'){
                return response()->json(['status'=>'sukses']);
            }else{
                return response()->json(['status'=>'user tidak ditemukan']);
            }
        }else{
            return response()->json(['status'=>'user tidak ditemukan']);
        }
    }

    public function loginAuthor(Request $request){
        $email = $request->input('email');
    	$password = $request->input('password');

    	$user = User::where(['email'=>$email, 'password'=>$password])->first();
        if($user)
        {
            if($user->role=='author'){
                return response()->json(['status'=>'sukses']);
            }else{
                return response()->json(['status'=>'user tidak ditemukan']);
            }
        }else{
            return response()->json(['status'=>'user tidak ditemukan']);
        }
    }
}
