<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id('event_id');
            $table->foreignId('event_user_id')->constrained('users')->onDelete('cascade');
            $table->string('event_name');
            $table->dateTime('event_date');
            $table->text('event_description')->nullable();
            $table->timestamp('event_created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
