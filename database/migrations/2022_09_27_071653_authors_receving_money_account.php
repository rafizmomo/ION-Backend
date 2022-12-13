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
        //
        Schema::create("authors_receiving_money_account", function (Blueprint $table) {
            $table->id();
            $table->unsignedDecimal("balance", 8, 2);
            $table->unsignedBigInteger("create_at", false);
            $table->unsignedBigInteger("updated_at", false);
            $table->unsignedBigInteger("user_balance_id", false)->unique("user_balance_id_unique");
            $table->foreign("user_balance_id")->references("id")->on("authors")->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists("authors_receiving_money_account");
    }
};
