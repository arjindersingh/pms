<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_error_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('placement', 32)->default('right_popup');
            $table->string('font_family', 32)->default('system');
            $table->unsignedTinyInteger('font_size')->default(14);
            $table->string('text_color', 7)->default('#7f1d1d');
            $table->string('background_color', 7)->default('#fef2f2');
            $table->string('accent_color', 7)->default('#dc2626');
            $table->string('density', 16)->default('comfortable');
            $table->string('motion', 16)->default('slide');
            $table->boolean('show_icon')->default(true);
            $table->boolean('allow_dismiss')->default(true);
            $table->boolean('group_messages')->default(true);
            $table->unsignedTinyInteger('auto_dismiss_seconds')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_error_settings');
    }
};
