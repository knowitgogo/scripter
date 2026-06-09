<?php

declare(strict_types=1);

use App\Enums\WidgetStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('widgets', function (Blueprint $table): void {
            $table->id();
            $table->publicUuid();
            $table->string('name', 255);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->string('status', 20)->default(WidgetStatus::Draft->value);
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('widgets');
    }
};
