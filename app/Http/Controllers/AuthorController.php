<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Authors;
use App\Models\AdminApproval;
use App\Models\User;

class AuthorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $users = DB::table('users')
            ->leftJoin('authors', 'users.id', '=', 'authors.user_id')->select(["users.name", "users.created_at", "authors.join_at"])
            ->get();

        return $this->sendRespond($users, "Succcess", 200);
    }
    /**
     * First, get admin approval id by user id in admin approval table
     * Second, delete a record by admin approval id in admin approval table
     * Last, create a new athor account 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function approve(Request $request, $id)
    {
        $date_now = round(microtime(true) * 1000);
        $admin_approval_user_id = AdminApproval::where("user_id", $id)->select("id", "join_at")->get();
        $json_decode_approval = json_decode($admin_approval_user_id);
        $user_id_from_admin_approval = $json_decode_approval[0]->id;

        $data_author = array(
            "author_description" => $request->author_description,
            "created_at" => $date_now,
            "user_id" => $id

        );
        $delete_admin_approval = AdminApproval::findOrFail($user_id_from_admin_approval);
        $delete_admin_approval->dlete();
        $create_author_role = User::findOrFail($id);
        $create_author_role->update();
        return response()->json(["authors", "status" => "Success", "message" => "Author has created"], 201);
    }

    public function reject(Request $request)
    {
        $admin_approval_user_id = AdminApproval::where("user_id", intval($request->user_id))
            ->select("id")->get();
        $json_decode_approval = json_decode($admin_approval_user_id);
        $user_id_json_decode_approval = $json_decode_approval[0]->id;
        $delete_admin_approval = AdminApproval::findOrFail($user_id_json_decode_approval);
        $delete_admin_approval->delete();
        return response()->json(["admin_approval" => $delete_admin_approval, "status" => "Success", "You have rejected to create an author account"], 202);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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
        //
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
