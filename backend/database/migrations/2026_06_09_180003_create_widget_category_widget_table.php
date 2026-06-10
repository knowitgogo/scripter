<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('widget_category_widget', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('widget_id')->constrained('widgets')->cascadeOnDelete();
            $table->foreignId('widget_category_id')->constrained('widget_categories')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['widget_id', 'widget_category_id']);
            $table->index('widget_category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('widget_category_widget');
    }
};
