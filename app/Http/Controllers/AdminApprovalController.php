<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AdminApproval;
use App\Models\User;

class AdminApprovalController extends Controller
{
    public function makeApproval(Request $request, $id)
    {
        $date_now = round(microtime(true) * 1000);
        $response = null;
        // $author_description = ucwords($request->input("author_description"));
        $author_description = $request->author_description;
        $author_data = array(
            "author_description" => $author_description,
            "join_at" => $date_now,
            "user_id" => $id,
        );
        if (AdminApproval::where("user_id", $id)->first() == null  && User::where("id", $id)->first() == null) {
            AdminApproval::create($author_data);
            $response = response()->json(["authors" => $author_data, "status" => "Success", "message" => "You have signigned to join as author. Please wait until approved"], 201);
        } else {
            $response = response()->json(["authors" => $author_data, "status" => "Fail", "message" => "Failed to create author account"], 409);
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
