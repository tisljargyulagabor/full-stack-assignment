<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            // chat_messages_user_id lesz a mező neve
            // A constrained('users', 'id') megmondja, hogy a 'users' tábla 'id' mezőjére mutat
            $table->unsignedBigInteger('chat_message_user_id')->nullable();

            $table->foreign('chat_message_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropForeign(['chat_message_user_id']);
            $table->dropColumn('chat_message_user_id');
        });
    }
};
