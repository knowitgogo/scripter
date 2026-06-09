<?php

declare(strict_types=1);

use App\Enums\WidgetVersionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('widget_versions', function (Blueprint $table): void {
            $table->id();
            $table->publicUuid();
            $table->foreignId('widget_id')->constrained('widgets')->cascadeOnDelete();
            $table->string('version', 20);
            $table->string('status', 20)->default(WidgetVersionStatus::Draft->value);
            $table->string('asset_manifest_url', 2048)->nullable();
            $table->timestamps();

            $table->unique(['widget_id', 'version']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('widget_versions');
    }
};
