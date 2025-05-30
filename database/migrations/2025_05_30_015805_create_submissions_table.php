<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("submissions", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("question_id")
                ->constrained()
                ->onDelete("cascade");
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->timestamp("submitted_at")->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("submissions");
    }
};
