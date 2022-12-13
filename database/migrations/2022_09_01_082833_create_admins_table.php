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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string("name", 60);
            $table->string("email", 60)->unique();
            $table->string("password", 200);
            $table->unsignedBigInteger("created_at", false);
            $table->unsignedBigInteger("update_at", false);
            $table->unsignedBigInteger("user_id", false)->nullable(true);
            $table->foreign("user_id")->references("id")->on("users")->cascadeOnUpdate("cascade")->onDelete("cascade");
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admins');
    }
};
