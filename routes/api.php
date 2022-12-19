<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\SubTopicController;
use App\Http\Controllers\AdminApprovalController;
use App\Http\Controllers\AdminNewsApprovalController;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::post("/register", [AuthController::class, "signup"]);
/**
 * route "/login"
 * @method "POST"
 */
Route::post('/login', App\Http\Controllers\LoginController::class)->name('login');
// Route::post('/register', App\Http\Controllers\LoginController::class)->name('signup');
Route::post('/register/admin', [UserController::class, "signupAdmin"]);
Route::post('/register/author', [UserController::class, "signupAuthor"]);
Route::put('/register/approve/{user_id}', [UserController::class, "approve"]);
Route::post('/login/admin', [UserController::class, "loginAdmin"]);
Route::post('/login/author', [UserController::class, "loginAuthor"]);
Route::get("/userprofile/{id}", [UserController::class, "userProfile"]);

// News Routes
// Route::get("/gettopicidbytopicslug/{topic_slug}", [NewsController::class, "getTopicIdByTopicSlug"]);
// Route::get("/getsubtopicidbysubtopicslug/{topic_slug}", [NewsController::class, "getSubTopicIdBySubTopicSlug"]);
Route::get("topics/news", [NewsController::class, "showNewsByTopics"]);
Route::get("topics/{topic_slug}", [NewsController::class, "showNewsByTopic"]); //Show news by a topic
Route::get("topics/sub_topics/{sub_topic_slug}", [NewsController::class, "showNewsBySubTopicsAndTopics"]);
Route::get("topics/{topic_id}/sub_topics/{sub_topic_id}/news/{news_title}", [NewsController::class, "readingNews"]);
Route::get("news/user/{id}", [NewsController::class, "showNewsByUserId"]);

Route::get("/sub_topics", [SubTopicController::class, "index"]);
Route::get("/sub_topics{sub_topics}", [SubTopicController::class, "show"]);
Route::post("/sub_topics", [SubTopicController::class, "store"]);
Route::patch("/sub_topics/{id}", [SubTopicController::class, "update"]);
Route::patch("/sub_topics/{id}", [SubTopicController::class, "delete"]);
Route::get("/topics", [TopicController::class, "index"]);
Route::get("/showtopic/{topics}", [TopicController::class, "show"]);
Route::post("/topics", [TopicController::class, "save"]);
Route::get("/history/{id?}", [HistoryController::class, "index"]);
Route::post("/history", [HistoryController::class, "store"]);
Route::delete("/history/{id}", [HistoryController::class, "delete"]);
Route::patch("/topics/{id}", [TopicController::class, "update"]);
Route::delete("/topics/{id}", [TopicController::class, "delete"]);

Route::get("/user", [UserController::class, "getAllUser"]);
Route::post("/logout", [AuthController::class, "signout"]);

// Admin Approval Routes
Route::get("/adminapproval/author", [AdminApprovalController::class, "listAdminApproval"]);
Route::post("/adminapproval/author/{id}", [AdminApprovalController::class, "makeApproval"]);
Route::prefix("adminapproval/author")->group(function () {
    route::post("/approve/{id}", [AuthorController::class, "approve"]);
    route::post("reject/{id}", [AuthorController::class, "reject"]);
});
Route::get("/adminapproval/news", [AdminNewsApprovalController::class, "showAdminNewsApproval"]);
Route::post("/adminapproval/news/{user_id}", [AdminNewsApprovalController::class, "makeApproval"]);
Route::put("/adminapproval/news/balance/{user_id}", [AdminNewsApprovalController::class, "updateBalance"]);
Route::prefix("adminapproval/news")->group(function () {
    route::post("approve/{news_title}", [NewsController::class, "approve"]);
    route::post("reject/{news_title}", [NewsController::class, "reject"]);
});
Route::middleware("auth:sanctum")->group(function () {
});
