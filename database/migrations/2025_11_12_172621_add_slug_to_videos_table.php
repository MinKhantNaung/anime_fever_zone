<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
        });

        // Generate slugs for existing videos
        $videos = DB::table('videos')->get();
        foreach ($videos as $video) {
            $baseSlug = Str::slug($video->title);
            $slug = $baseSlug;
            $counter = 1;

            // Ensure uniqueness by appending number if needed
            while (DB::table('videos')->where('slug', $slug)->where('id', '!=', $video->id)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            DB::table('videos')
                ->where('id', $video->id)
                ->update(['slug' => $slug]);
        }

        // Now make slug unique and not nullable
        Schema::table('videos', function (Blueprint $table) {
            $table->string('slug')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
