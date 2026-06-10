<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('widget_templates', function (Blueprint $table): void {
            $table->id();
            $table->publicUuid();
            $table->foreignId('widget_id')->constrained('widgets')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('slug', 100);
            $table->text('description')->nullable();
            $table->text('content');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['widget_id', 'slug']);
            $table->index('widget_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('widget_templates');
    }
};
