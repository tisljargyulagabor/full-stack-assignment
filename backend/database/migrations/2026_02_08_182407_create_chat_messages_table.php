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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            // A beszélgetés azonosítója (lehet UUID vagy sima string a Vue-ból)
            $table->string('session_id')->index();
            // Ki küldte: 'user' vagy 'bot'
            $table->string('sender');
            // Az üzenet szövege
            $table->text('message');
            // Flag: true, ha az AI nem tudott válaszolni
            $table->boolean('needs_admin')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
