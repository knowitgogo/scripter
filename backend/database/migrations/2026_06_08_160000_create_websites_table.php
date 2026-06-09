<?php

declare(strict_types=1);

use App\Enums\WebsiteStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('websites', function (Blueprint $table): void {
            $table->id();
            $table->publicUuid();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 255);
            $table->string('url', 2048);
            $table->string('status', 20)->default(WebsiteStatus::Active->value);
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->unique('url');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('websites');
    }
};
