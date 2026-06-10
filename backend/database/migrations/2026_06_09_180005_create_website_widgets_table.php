<?php

declare(strict_types=1);

use App\Enums\WebsiteWidgetStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_widgets', function (Blueprint $table): void {
            $table->id();
            $table->publicUuid();
            $table->foreignId('website_id')->constrained('websites')->cascadeOnDelete();
            $table->foreignId('widget_version_id')->constrained('widget_versions')->cascadeOnDelete();
            $table->string('status', 20)->default(WebsiteWidgetStatus::Active->value);
            $table->json('configuration_json')->nullable();
            $table->timestamps();

            $table->unique(['website_id', 'widget_version_id']);
            $table->index('website_id');
            $table->index('widget_version_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_widgets');
    }
};
