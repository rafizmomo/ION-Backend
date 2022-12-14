<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller as Controller;
use App\Models\News;
use App\Models\Topics;
use App\Models\SubTopics;
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

    function index()
    {
        $news = SubTopics::with("news")->join("news", "news.id", "=", "news_sub_topics.news_sub_topic_id")->select("news.*", "news_sub_topics.*")->get();
        return response()->join($news);
    }


    /**
     * @param \Illuminate\Http\Request $request
     */
    public function store(Request $request)
    {
        $date_now = round(microtime(true) * 1000);
        $news_title = ucwords($request->news_title);
        $lower_case = strtolower($news_title);
        $slug_no_whitespaces = preg_replace("/\s+/", "-", $lower_case);
        $image_file = $request->file("image_file");
        $extension = $image_file->getExtension();
        $file_name = $image_file->getClientOriginalName();
        $file_name_ = explode(".", $file_name);
        $without_extension = $file_name[0];
        $directory = "storage";
        $url = URL::to('');
        $image_url_directory = stripslashes($url . "/" . $directory . "/" . $file_name);
        $validator = Validator::make($request->all(), [
            "news_title" => "required",
            "news_content" => "required",
            "image_file" => "required|image:jpeg,png,jpg|max:5500",
            "sub_topic_id" => "required|numeric",
            "author_id" => "required|numeric"
        ]);

        $data["news_title"] = $news_title;
        $data["news_content"] = $request->news_content;
        $data["news_slug"] = $slug_no_whitespaces;
        $data["news_picture_link"] = $image_url_directory;
        $data["news_picture_name"] = $file_name;
        $data["added_at"] = $date_now;
        $data["updated_at"] = 0;
        $data["sub_topic_id"] = $request->sub_topic_id;
        $data["author_id"] = $request->author_id;
        $response = null;
        if ($validator->fails()) {
            $response = response()->json(["status" => "Failed", $validator->errors()], 422);
        } else {
            if (News::where("news_title", $news_title)->first() != null) {
                $response = response()->json(["status" => "Failed", "message" => "The news title has existed"], 409);
            } else {
                try {
                    $image_file->move($directory, $image_file->getClientOriginalName());
                    News::create($data);
                } catch (Exception $excption) {
                    $response = response()->json(["status" => "Failed", $excption->getMessage()]);
                }
            }
        }
        return $response;
    }

    // For home page in user, visitor
    public function showNewsBySubTopicsAndTopics()
    {
        DB::enableQueryLog();
        $$topics = Topics::select("id")->get();
        $decode_topics = json_decode($topics);
        $array_decode_topics = array();
        for ($i = 0; $i < sizeof($decode_topics); $i++) {
            $array_decode_topics[$i] = $decode_topics[$i]->id;
        }
        $join_news = DB::table("news")->join("sub_topics", "sub_topics.id", "=", "news.sub_topic_id")
            ->select(
                "news.*",
                "sub_topics.sub_topic_title",
                "sub_topics.added_at as sub_topic_added_at",
                "sub_topics.updated_at as sub_topic_updated_at"
            )
            ->whereIn("sub_topics.topic_id", $array_decode_topics)
            ->get();
        var_dump(DB::getQueryLog());
        return response()->json($join_news, 200);
    }

    public function showNewsByTopicId($id)
    {
        DB::enableQueryLog();
        $topics = Topics::with("news")->where("topics.id", $id)->get();
        DB::getQueryLog();
        return response()->json([$topics], 200);
    }

    public function showNewsBySubTopics()
    {
        $news = DB::table("news")->join("sub_topics", "sub_topics.id", "=", "news.sub_topic_id")->select("news.id", "news.news_title", "news.news_content", "news.news_slug", "news.news_picture_link", "news.news_picture_name", "news.added_at", "news.updated_at", "sub_topics.sub_topic_title")->get();
        return response()->json(["news" => $news], 200);
    }

    public function showNewsBySubTopic($id)
    {
        $news = DB::table("news")->join("sub_topics", "sub_topics.id", "=", "news.sub_topic_id")->where("news.sub_topic_id", $id)->select("news.*", "sub_topics.sub_topic_title")->get();
        return response()->json(["news" => $news], 200);
    }


    public function show($id)
    {
    }
}
