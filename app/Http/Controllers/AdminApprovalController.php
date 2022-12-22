<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use App\Models\AdminApproval;
use Illuminate\Support\Facades\File;

class AdminApprovalController extends Controller
{
    public function makeApproval(Request $request, $id)
    {
        $date_now = round(microtime(true) * 1000);
        $response = null;
        $image_file = $request->file("image_file");
        $file_name = $image_file->getClientOriginalName();
        $direct_file = $image_file->getClientOriginalName();
        $file_path_info = pathinfo($file_name);
        $base_name = $file_path_info["filename"];
        $directory = "storage";
        $author_description = $request->author_description;
        $validator = Validator::make($request->all(), [
            "image_file" => "required|image:jpeg,png,jpg|max:5500",
            "author_description" => "required",
        ]);
        $author_data["author_description"] = $author_description;
        $author_data["join_at"] = $date_now;
        $author_data["user_id"] = intval($id);
        if ($validator->fails()) {
            $response = response()->json(["status" => "Fail", "status_code" => 422, "message" => $validator->errors()], 422);
        } else {
            if (AdminApproval::where("user_id", $id)->first() == null) {
                $counter = 1;
                $extension_test = File::extension($file_name);
                $current_counter_file = $base_name . "_" . $counter  . "." . $extension_test;
                if (File::exists($directory . "/" . "photo_profile/" . $file_name)) {
                    if (File::exists($directory . "/" . "photo_profile/" . $current_counter_file)) {
                        do {
                            $next_counter_file = $base_name . "_" . $counter  . "." . $extension_test;
                            $current_counter_file = $next_counter_file;
                            $counter++;
                        } while (File::exists($directory . "/" . "photo_profile/" . $current_counter_file));
                        $file_name =  $current_counter_file;
                    } else {
                        $file_name = $current_counter_file;
                    }
                } else {
                    $file_name = $direct_file;
                }
                $image_url_directory = stripslashes($request->schemeAndHttpHost() . "/" . $directory . "/photo_profile" . "/" . $file_name);
                $author_data["photo_profile_link"] = $image_url_directory;
                $author_data["photo_profile_name"] = $file_name;
                $author_data["photo_profile_path"] = preg_replace("/\s+/", "", strtolower("storage/photo_profile"));
                File::move($image_file, $directory . "/" . "photo_profile/" . $file_name);
                AdminApproval::create($author_data);
                $response = response()->json(["authors" => $author_data, "status" => "Success", "message" => "You have signigned to join as author. Please wait until you are approved"], 200);
            } else {
                $response = response()->json(["authors" => $author_data, "status" => "Fail", "status_code" => 409, "message" => "You have joined as an author before"], 409);
            }
        }
        return $response;
    }

    public function listAdminApproval()
    {
        $listdmin = DB::table("admin_approval")->join("users", "users.id", "=", "admin_approval.user_id")
            ->select("admin_approval.*", "users.name")->get();
        return response()->json(["admin_approval" => $listdmin], 200);
    }
}
