<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function signup(Request $request)
    {
        $response = null;
        $validator = Validator::make($request->all(), [
            "name" => "required|min:5",
            "email" => "required|unique:users",
            "password" => "required|min:8",
        ]);
        $user_data = array(
            "name" => ucwords($request->name),
            "email" => $request->email,
            "password" => $request->password,
        );
        if ($validator->fails()) {
            $response =  response()->json(["status" => "Fail", "message" => $validator->errors()], 422);
        } else {
            User::create($user_data);
            $response = response()->json(["users" => $user_data, "status" => "Success", "message" => "User has been created"], 202);
        }
        return $response;
    }
}
