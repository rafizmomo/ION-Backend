<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\News;
use App\Models\Topics;
use App\Models\SubTopics;
use App\Models\AdminNewsApproval;
use App\Models\User;
use App\Http\Controllers\Controller as Controller;
use App\Models\AdminApproval;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

// Eager: Join
// Lazy: Not Join
class NewsController extends Controller
{

    // convert this 1670906391017 to date on php where the date format is '%yyy-%m-%d'
    // $timestamp = 1670906391017;
    // $date = date('%yyy-%m-%d', $timestamp);

    // On javascript
    //     const timestamp = 1670906391017;
    // const date = new Date(timestamp).toLocaleDateString('en-US', {
    //   year: 'numeric',
    //   month: '2-digit',
    //   day: '2-digit'
    // });

    // function index(){
    //     \DB::enableQueryLog();
    //     $show_news = DB::select("SELECT `title`, `news_content` FROM `news`");
    //     return $this->sendRespond($show_news, "News are showed", 200);
    //     dd(DB::getQueryLog());

    //     $test = NewsObject::query()->get();
    //     return $test;
    //     dd(\DB::getQueryLog());
    // }

    //Admin 
    public function index()
    {
        $news = News::join("users", "users.id", "=", "news.user_id")->select("news.*", "users.id", "users.name", "users.photo_profile_link")->where("role", "author")->get();
        return response()->json($news, 200);
    }
    public function searchNewsByNewsTitle()
    {
        $news = News::join("users", "users.id", "=", "news.user");
    }
    /**
     * @param \Illuminate\Http\Request $request
     */
    public function approve(int $approval_id)
    {
        $date_now = round(microtime(true) * 1000);
        $admin_approval = AdminNewsApproval::where("id", $approval_id)->get();
        $json_decode = json_decode($admin_approval);
        $news_new = null;
        $news_content = null;
        $news_slug = null;
        $news_picture_link = null;
        $news_picture_name = null;
        $news_picture_path = null;
        $news_sub_topic_id = null;
        $user_id = null;
        $response = null;
        if (AdminNewsApproval::where("id", $approval_id)->first() != null) {
            $news_new = $json_decode[0]->news_title;
            $news_content = $json_decode[0]->news_content;
            $news_slug = $json_decode[0]->news_slug;
            $news_picture_link = $json_decode[0]->news_picture_link;
            $news_picture_name = $json_decode[0]->news_picture_name;
            $news_picture_path = $json_decode[0]->news_picture_path;
            $news_sub_topic_id = $json_decode[0]->sub_topic_id;
            $user_id = $json_decode[0]->user_id;
        }
        if ($news_new != null) {
            try {
                $data["news_title"] = $news_new;
                $data["news_content"] = $news_content;
                $data["news_slug"] = $news_slug;
                $data["news_picture_link"] = $news_picture_link;
                $data["news_picture_name"] = $news_picture_name;
                $data["news_picture_path"] = $news_picture_path;
                $data["added_at"] = $date_now;
                $data["news_status"] = "Paid";
                $data["sub_topic_id"] = $news_sub_topic_id;
                $data["user_id"] = $user_id;
                $delete_news_approval = AdminNewsApproval::find($approval_id);
                $delete_news_approval->delete();
                News::create($data);
                $response = response()->json(["approve_news" => $data, "status" => "Succes", "status_code" => 200], 200);
            } catch (Exception $excption) {
                $response = response()->json(["status" => "Failed", $excption->getMessage()]);
            }
        }
        return $response;
    }
    public function reject($news_title)
    {
        $admin_approval_news_title = AdminNewsApproval::where("news_title", $news_title)
            ->select("id", "news_picture_name", "news_picture_path")->get();
        $json_decode_approval = json_decode($admin_approval_news_title);
        $id = $json_decode_approval[0]->id;
        $news_picture_name = $json_decode_approval[0]->news_picture_name;
        $news_picture_path = $json_decode_approval[0]->news_picture_path;
        $delete_admin_approval = AdminNewsApproval::findOrFail($id);
        $delete_admin_approval->delete();
        if (File::exists($news_picture_path . "/" . $news_picture_name)) {
            File::delete($news_picture_path . "/" . $news_picture_name);
        }
        return response()->json(["admin_approval" => $delete_admin_approval, "status" => "Success", "status_code" => 200, "You have rejected to create an author account"], 200);
    }
    public function getTopicIdByTopicSlug(string $topic_slug)
    {
        $string_topic_slug = strval($topic_slug);
        $id = Topics::where("topic_slug", $string_topic_slug)->select("id")->get();
        $json_encode = json_decode($id);
        return response(["id" => $json_encode[0]->id, "status" => "success"], 200);
    }
    public function getSubTopicIdBySubTopicSlug(string $sub_topic_slug)
    {
        $string_sub_topic_slug = strval($sub_topic_slug);
        $id = SubTopics::where("sub_topic_slug", $string_sub_topic_slug)->select("id")->get();
        $json_encode = json_decode($id);
        return $json_encode[0]->id;
    }
    public function showNewsByTopics()
    {
        DB::enableQueryLog();
        $topics = Topics::with("news")->get();
        return response()->json($topics, 200);
    }
    // For home page in topic_home, visitor
    public function showNewsByTopic($topic_slug)
    {
        DB::enableQueryLog();

        $topic = Topics::where("topic_slug", $topic_slug)->first();
        $join_news = News::join("sub_topics", "sub_topics.id", "=", "news.sub_topic_id")
            ->select(
                "news.*",
                "sub_topics.sub_topic_title",
                "sub_topics.added_at as sub_topic_added_at",
                "sub_topics.updated_at as sub_topic_updated_at"
            )
            ->where("sub_topics.topic_id", $topic->id)
            ->get();
        if ($join_news->count() == 0) {
            return response(404, "test");
        }
        return response($join_news, 200);
    }
    public function showNewsBySubTopicsAndTopics(string $sub_topic_slug)
    {
        DB::enableQueryLog();
        $sub_topic = SubTopics::where("sub_topic_slug", $sub_topic_slug)->first();
        $join_news = DB::table("news")->join("sub_topics", "sub_topics.id", "=", "news.sub_topic_id")
            ->select(
                "news.*",
                "sub_topics.sub_topic_title",
                "sub_topics.added_at as sub_topic_added_at",
                "sub_topics.updated_at as sub_topic_updated_at"
            )
            ->where([["news.sub_topic_id", intval($sub_topic->id)]])
            ->get();
        return response()->json($join_news, 200);
    }

    public function showNewsByUserId(int $id)
    {
        DB::enableQueryLog();
        $join_news = DB::table("news")->join("sub_topics", "sub_topics.id", "=", "news.sub_topic_id")
            ->select(
                "news.*",
                "sub_topics.sub_topic_title",
                "sub_topics.added_at as sub_topic_added_at",
                "sub_topics.updated_at as sub_topic_updated_at",
                "sub_topics.topic_id"
            )
            ->where("user_id", $id)
            ->get();
        $decode = json_decode($join_news);
        $topic = Topics::find($decode[0]->topic_id);
        return response()->json(["news" => $join_news, "topics" => $topic], 200);
    }

    public function readingNews(string $news_slug)
    {
        DB::enableQueryLog();
        $join_news = DB::table("news")
            ->join("sub_topics", "sub_topics.id", "=", "news.sub_topic_id")
            ->join("users", "users.id", "=", "news.user_id")
            ->select(
                "news.*",
                "sub_topics.sub_topic_title",
                "users.name",
                "users.photo_profile_link",
                "users.photo_profile_name",
                "sub_topics.added_at as sub_topic_added_at",
                "sub_topics.updated_at as sub_topic_updated_at"
            )
            ->where("news.news_slug", $news_slug)
            ->get();
        return response()->json($join_news, 200);
    }

    public function getSubTopicByTopic(int $topic_id)
    {
        $sub_topic = SubTopics::where("topic_id", $topic_id);
        return response()->json(["sub_topics" => $sub_topic, "status_code" => 200],);
    }

    public function openNewsPicture(int $news_id)
    {
        DB::enableQueryLog();
        $news = News::find($news_id);
        $header = array(
            header("Content-Type: " . File::mimeType($news->news_picture_path . "/" . $news->news_picture_name)),
            header(
                "Content-Length: " . File::size($news->news_picture_path . "/" . $news->news_picture_name),
            ),
            header("Access-Control-Allow-Origin: *")
        );
        return readfile($news->news_picture_path . "/" . $news->news_picture_name);
    }

    public function updateNews(Request $request, int $news_id)
    {
        $news = News::where("id", $news_id)->get();
        $update_news = News::findOrFail($news_id);
        $news_title = ucwords($request->news_title);
        $news_content = $request->news_content;
        $file_image = $request->file("image_file");
        $sub_topic_id = $request->sub_topic_id;
        $directory = "storage";
        $file_name = $file_image->getClientOriginalName();
        $path_info = pathinfo($file_name);
        $base_name = $path_info["filename"];
        $updated_at = round(microtime(true) * 1000);
        $counter = 1;
        $extension_test = File::extension($file_name);
        $current_counter_file = $base_name . "_" . $counter  . "." . $extension_test;
        if (File::exists($news->value("news_picture_path") . "/" . $news->value("news_picture_name"))) {
            File::delete($news->value("news_picture_path") . "/" . $news->value("news_picture_name"));
        }
        if (File::exists($directory . "/" . "news_image/" . $file_name)) {
            if (File::exists($directory . "/" . "news_image/" . $current_counter_file)) {
                do {
                    $next_counter_file = $base_name . "_" . $counter  . "." . $extension_test;
                    $current_counter_file = $next_counter_file;
                    $counter++;
                } while (File::exists($directory . "/" . "news_image/" . $current_counter_file));
                $file_name =  $current_counter_file;
            } else {
                $file_name = $current_counter_file;
            }
        } else {
            $file_name = $file_image->getClientOriginalName();
        }
        $image_url_directory = stripslashes($request->schemeAndHttpHost() . "/" . $directory . "/news_image" . "/" . $file_name);
        $data["news_title"] = $news_title;
        $data["news_content"] = $news_content;
        $data["news_slug"] = preg_replace("/\s+/", "-", strtolower($news_title));
        $data["news_picture_link"] = $image_url_directory;
        $data["news_picture_name"] = $file_name;
        $data["updated_at"] = $updated_at;
        $data['sub_topic_id'] = $sub_topic_id;
        File::copy($file_image, $directory . "/" . "news_image/" . $file_name);
        $update_news->update($data);
        return response()->json(["news" => $data, "status_code" => 200], 200);
    }
    public function delete(int $news_id)
    {
        $news = News::findOrFail($news_id);
        if (File::exists("storage/news_image/" . $news->news_picture_name)) {
            File::delete("storage/news_image/" . $news->news_picture_name);
        }
        $news->delete();
    }
}
