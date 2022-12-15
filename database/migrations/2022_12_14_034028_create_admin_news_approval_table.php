<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_news_approval', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id", false);
            $table->string("news_title");
            $table->string("news_content");
            $table->string("news_picture_link", 150);
            $table->string("news_picture_name", 50);
            $table->string("news_picture_path", 50)->nullable();
            $table->foreign("user_id")->references("id")->on("users");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_news_approval');
    }
};
