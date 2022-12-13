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

        Schema::create("topics", function (Blueprint $table) {
            $table->id();
            $table->string("topic_title", 60)->unique();
            $table->string("topic_slug", 200);
            $table->bigInteger("added_at", false, true);
            $table->bigInteger("updated_at", false, true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('topics');
    }
};
