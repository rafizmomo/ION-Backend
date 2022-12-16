<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use App\Models\News;
use App\Models\Topics;
use App\Models\SubTopics;
use App\Models\AdminNewsApproval;
use App\Http\Controllers\Controller as Controller;
use App\Models\AdminApproval;
use Exception;

// Eager: Join
// Lazy: Not Join
class NewsController extends Controller
{
    //     o show data from a database based on a date when using epoch time, you will need to convert the epoch time to a human-readable date format. This can typically be done using a combination of the DATE() and FROM_UNIXTIME() functions, which are available in most databases. For example, if your database has a column named timestamp that contains epoch timestamps, you could use a query like the following to show only the rows with a timestamp from a specific date:
    // Copy code
    // SELECT * FROM my_table
    // WHERE DATE(FROM_UNIXTIME(timestamp)) = '2022-12-13'
    // This query will convert the epoch timestamps in the timestamp column to human-readable dates, and then only show the rows where the date matches the specified date. You can adjust the query as needed to match the specific requirements of your project.

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

    public function show($id)
    {
    }
    function index()
    {
        $news = SubTopics::with("news")->join("news", "news.id", "=", "news_sub_topics.news_sub_topic_id")->select("news.*", "news_sub_topics.*")->get();
        return response()->join($news, 200);
    }
    public function showNewsForAdminReview()
    {
        $news = DB::table("news")->join("authors", "authors.id", "=", "news.author_id")
            // ->join("sub_topics", "sub_topics.id", "=", "news.sub_topic_id")
            ->select("news.*", "authors.*")->get();
        return response()->json($news, 200);
    }
    /**
     * @param \Illuminate\Http\Request $request
     */
    public function approve($news_title)
    {
        $date_now = round(microtime(true) * 1000);
        $admin_approval = AdminNewsApproval::where("news_title", $news_title)->get();
        $json_decode = json_decode($admin_approval);
        $news_new = null;
        $news_content = null;
        $news_slug = null;
        $news_picture_link = null;
        $news_picture_name = null;
        $news_picture_path = null;
        $news_sub_topic_id = null;
        $user_id = null;
        if (AdminNewsApproval::where("news_title", $news_title)->first() != null) {
            $news_new = $json_decode[0]->news_title;
            $news_content = $json_decode[0]->news_content;
            $news_slug = $json_decode[0]->news_slug;
            $news_picture_link = $json_decode[0]->news_picture_link;
            $news_picture_name = $json_decode[0]->news_picture_name;
            $news_picture_path = $json_decode[0]->news_picture_path;
            $news_sub_topic_id = $json_decode[0]->sub_topic_id;
            $user_id = $json_decode[0]->user_id;
        }
        $response = null;
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
                $delete_news_approval = AdminNewsApproval::where("news_title", $news_new);
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
        return $json_encode[0]->id;
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
        $topics = Topics::with("news")->get();
        return response()->json($topics, 200);
    }
    // For home page in topic_home, visitor
    public function showNewsByTopic($topic_id)
    {
        DB::enableQueryLog();
        $join_news = DB::table("news")->join("sub_topics", "sub_topics.id", "=", "news.sub_topic_id")
            ->select(
                "news.*",
                "sub_topics.sub_topic_title",
                "sub_topics.added_at as sub_topic_added_at",
                "sub_topics.updated_at as sub_topic_updated_at"
            )
            ->where("sub_topics.topic_id", intval($topic_id))
            ->get();
        return response()->json($join_news, 200);
    }
    public function showNewsBySubTopicsAndTopics(int $topic_id, int $sub_topic_id)
    {
        DB::enableQueryLog();
        $join_news = DB::table("news")->join("sub_topics", "sub_topics.id", "=", "news.sub_topic_id")
            ->select(
                "news.*",
                "sub_topics.sub_topic_title",
                "sub_topics.added_at as sub_topic_added_at",
                "sub_topics.updated_at as sub_topic_updated_at"
            )
            ->where([["sub_topics.topic_id", intval($topic_id)], ["news.sub_topic_id", intval($sub_topic_id)]])
            ->get();
        return response()->json($join_news, 200);
    }
    public function readingNews(int $topic_id, int $sub_topic_id, string $news_title)
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
            ->where([["sub_topics.topic_id", intval($topic_id)], ["news.sub_topic_id", intval($sub_topic_id)], ["news.news_title", strval($news_title)]])
            ->get();
        return response()->json($join_news, 200);
    }
}
