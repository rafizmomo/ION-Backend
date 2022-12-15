<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\SubTopicController;
use App\Http\Controllers\AdminApprovalController;
// use App\Resources\NewsSubTopicsResource;
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
Route::post('/register', [UserController::class, "signup"]);
// Route::post("/login", [AuthController::class, "signin"]);

Route::get("/", [NewsController::class, "showNewsByTopics"]);
Route::get("/sub", [NewsController::class, "showNewsBySubTopics"]);
Route::get("/sub/{id}", [NewsController::class, "showNewsBySubTopic"]);

// News Routes
Route::post("/news", [NewsController::class, "store"]);
Route::get("/shownewsbytopic/{id}", [NewsController::class, "showNewsByTopic"]);
Route::get("/news/{id}", [NewsController::class, "show"]);
Route::get("/news/search", [NewsController::class, "index"]);

Route::get("/sub_topics", [SubTopicController::class, "index"]);
Route::get("/sub_topics{sub_topics}", [SubTopicController::class, "show"]);
Route::post("/sub_topics", [SubTopicController::class, "store"]);
Route::patch("/sub_topics/{id}", [SubTopicController::class, "update"]);
Route::patch("/sub_topics/{id}", [SubTopicController::class, "delete"]);
Route::get("/topics", [TopicController::class, "index"]);
Route::get("/topics/{topics}", [TopicController::class, "show"]);
Route::post("/topics", [TopicController::class, "save"]);
Route::patch("/topics/{id}", [TopicController::class, "update"]);
Route::delete("/topics/{id}", [TopicController::class, "delete"]);
Route::get("/author", [AuthorController::class, "index"]);
Route::post("/author", [AuthorController::class, "store"]);
Route::get("/user", [UserController::class, "getAllUser"]);
Route::post("/logout", [AuthController::class, "signout"]);

// Admin Approval Route
Route::get("/adminapproval", [AdminApprovalController::class, "listAdminApproval"]);
Route::post("/adminapproval/{id}", [AdminApprovalController::class, "makeApproval"]);
Route::prefix("adminapproval")->group(function () {
    route::get("/approve/{id}", [AuthorController::class, "approve"]);
    route::post("/reject", [AuthorController::class, "reject"]);
});

Route::get("/shownewsforadminreview", [NewsController::class, "showNewsForAdminReview"]);
Route::middleware("auth:sanctum")->group(function () {
});
