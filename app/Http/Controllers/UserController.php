<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use App\Models\User;

class UserController extends Controller
{

    public function showAllAuthors()
    {
        $authors = User::where("role", "author")->get();
        return response()->json($authors, 200);
    }

    public function registerAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $user = User::create(["name" => $request->name, "email" => $request->email, 'password' => $request->password, "role" => "author"]);
        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $user = User::create(["name" => $request->name, "email" => $request->email, 'password' => $request->password]);
        return response()->json(
            [
                'message' => 'User successfully registered',
                'user' => $user
            ],
            201
        );
    }

    // public function approve(Request $request, $user_id)
    // {
    //     DB::table('users')
    //         ->where('id', $user_id)
    //         ->update([
    //             'role' => "author"
    //         ]);
    // }

    public function loginAdmin(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        $user = User::where(['email' => $email, 'password' => $password])->first();
        if ($user) {
            if ($user->role == 'admin') {
                return response()->json(['status' => 'sukses', "user" => $user->id]);
            } else {
                return response()->json(['status' => 'user tidak ditemukan']);
            }
        } else {
            return response()->json(['status' => 'user tidak ditemukan']);
        }
    }

    public function loginUser(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');
        $user = User::where(['email' => $email, 'password' => $password])->first();
        if ($user) {
            return response()->json(['status' => 'sukses', "user" => $user->id]);
        } else {
            return response()->json(['status' => 'user tidak ditemukan']);
        }
    }

    public function updateUser(Request $request, int $user_id)
    {
        $user = User::find($user_id);
        $user_name = $request->name;
        $image_file = $request->file("image_file");
        $author_description = $request->author_desc;
        $file_name = $image_file->getClientOriginalName();
        $file_info = pathinfo($file_name);
        $base_name = $file_info["filename"];
        $extension = $image_file->getClientOriginalExtension();
        $count = 1;
        $current_count_file = $base_name . "_" . $count . $extension;
        if (File::exists($user->photo_profile_path . "/" . $user->photo_profile_name)) {
            File::delete($user->photo_profile_path . "/" . $user->photo_profile_name);
        }
        if (File::exists($user->photo_profile_path . "/" . $file_name)) {
            if (File::exists($user->photo_profile_name . "/" . $current_count_file)) {
                do {
                    $next_count_file = $base_name . "_" . $count . $extension;
                    $current_count_file = $next_count_file;
                    $count++;
                } while (File::exists($user->photo_profile_path . "/" . $current_count_file));
                $file_name = $current_count_file;
            } else {
                $file_name = $current_count_file;
            }
        }
        File::copy($image_file, $user->photo_profile_path . "/" . $file_name);
        $validator = Validator::make($request->input("image_file"), [
            "image_file" => "image:jpg,jpeg,png|max:5500"
        ]);
        $data = array(
            "name" => $user_name,
            "author_description" => $author_description,
            "photo_profile_link" => $request->schemeAndHttpHost() . $user->photo_profile_path . "/" . $file_name,
            "photo_profile_name" => $file_name,
            "photo_profile_path" => "storage/photo_profile",
        );
        $user->update($data);
        return $validator->fails() ? $validator->errors()->toJson() : "";
    }

    public function userProfile($id)
    {
        $author = User::find(intval($id));
        return response()->json([$author]);
    }
}
