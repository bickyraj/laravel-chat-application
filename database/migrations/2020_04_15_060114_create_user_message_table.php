<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_message', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('message_id')->index();
            $table->foreign('message_id')->references('id')->on('messages')->onDelete('cascade');
            $table->unsignedInteger('sender_id')->comment('user id')->index();
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedInteger('receiver_id')->comment('user id')->nullable()->index();
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
            $table->tinyInteger('type')->default(1)->comment("1 = personal message, 2 = group message");
            $table->unsignedInteger('message_group_id')->nullable();
            $table->foreign('message_group_id')->references('id')->on('message_groups')->onDelete('cascade');            
            $table->tinyInteger('seen_status')->default(0);
            $table->tinyInteger('deliver_status')->default(0);
            $table->tinyInteger('status')->default(1);            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_message');
    }
}
