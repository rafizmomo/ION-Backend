<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\NewsSubTopicsResource;
use App\Models\SubTopics;
use App\Models\Topics;
use Illuminate\Auth\Events\Validated;
use Symfony\Component\HttpFoundation\JsonResponse;

class SubTopicController extends Controller
{
    private function SubTopicWithCondition(string $column, $input)
    {
        return SubTopics::where($column, $input)->first();
    }

    function index()
    {
        $subtopics = SubTopics::all();
        return response()->json(["sub_topics" => $subtopics, "status" => "Success", "message" => "List of sub topics"], 202);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function show(SubTopics $sub_topics)
    {
        return response()->json(["sub_topics" => $sub_topics, "status" => "Success", "message" => "Successfully sub topic"], 202);
    }

    /**
     * @param \Illuminate\Http\Request @request
     * @return \Illuminate\Http\Response 
     */
    public function store(Request $request): JsonResponse
    {
        $lower_str = strtolower($request->input("sub_topic_title"));
        $no_whitespace = preg_replace("/\s+/", "-", $lower_str);
        $added_at = round(microtime(true) * 1000);
        $sub_topic_title = ucwords($request->input("sub_topic_title"));
        $topic_id = intval($request->input("topic_id"));
        $data["sub_topic_title"] = $sub_topic_title;
        $data["sub_topic_slug"] = (string)$no_whitespace;
        $data["added_at"] = $added_at;
        $data["updated_at"] = 0;
        $data["topic_id"] = $topic_id;
        $get_topic_id = Topics::where("id", $topic_id)->first();
        $response = null;

        try {

            if (SubTopics::where("sub_topic_title", $sub_topic_title)->first() != null) {
                $response = response()->json(["data" => $data, "status" => "Failed", "message" => "Sub topic: " . $sub_topic_title . " is exist"], 409);
            } else {
                SubTopics::create($data);
                $response = response()->json(["data" => $data, "status" => "Succcess", "message" => "Succedd to create a sub topic"], 200);
            }
        } catch (\Illuminate\Database\QueryException $th) {
            $response = response()->json(["message" => $th->getMessage()], 404);
        }
        return $response;
    }

    /**
     * @param \Illuminate\Http\Request @request 
     *@return \Illuminate\Http\Response 
     */
    public function update(Request $request, $id)
    {
        $sub_topic_title = $request->sub_topic_title;
        $sub_topic_title_capitalize = ucwords($sub_topic_title);
        $sub_topic_slug = preg_replace("/\s+/", "-", strtolower($sub_topic_title_capitalize));
        $updated_at = round(microtime(true) * 1000);
        $topic_id = intval($request->topic_id);
        $sub_topic_update = SubTopics::findOrFail($id);
        $array_update = array(
            "sub_topic_title" => $sub_topic_title_capitalize,
            "sub_topic_slug" => $sub_topic_slug,
            "updated_at" => $updated_at,
            "topic_id" => $topic_id
        );
        $response = null;
        if ($this->SubTopicWithCondition("sub_topic_title", $sub_topic_title_capitalize) != null) {
            $json_encode = json_encode(SubTopics::where("sub_topic_title", $sub_topic_title_capitalize)->select("id", "sub_topic_title")->get());
            $json_decode_id =  json_decode($json_encode)[0]->id;
            $json_decode_sub_topic_title =  json_decode($json_encode)[0]->sub_topic_title;
            $json_encode_sub_topic_byid = json_encode(SubTopics::where("id", $id)->select("id", "sub_topic_title")->get());
            $json_decode_sub_topic_byid =  json_decode($json_encode_sub_topic_byid)[0]->id;
            if ($id == $json_decode_id && $sub_topic_title_capitalize == $json_decode_sub_topic_title) {
                $sub_topic_update->update($array_update);
                $response = response()->json(["sub_topics" => $array_update, "status" => "Success", "message" => "Successfully update sub topic"], 200);
            } else if ($id == $json_decode_sub_topic_byid && $this->SubTopicWithCondition("sub_topic_title", $sub_topic_title_capitalize)->get() != null) {
                $response =  response()->json(["sub_topics" => $array_update, "status" => "Failed", "message" => "Failed update sub topic"], 409);
            }
        } else {
            $sub_topic_update->update($array_update);
            $response = response()->json(["sub_topics" => $sub_topic_update, "status" => "Success", "message" => "Successfully update sub topic"], 200);
        }
        return $response;
    }

    public function delete($id)
    {
        $subtopics = SubTopics::findOrFail($id);
        if ($subtopics) {
            $subtopics->delete();
            return response(["sub_topics" => $subtopics, "status" => "Successfully", "message" => "Successfully update sub topic"], 202);
        }
    }
}
