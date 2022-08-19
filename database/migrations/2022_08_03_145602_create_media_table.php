<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    /**
     * @property int $server_id
     * @property int $library_id
     * @property string $title
     * @property string $audio_codec
     * @property string $video_codec
     * @property float aspect_ratio
     * @property int $bitrate
     * @property string $container
     * @property int $duration
     * @property string $framerate
     * @property int $height
     * @property int $width
     * @property string $profile
     * @property int $resolution
     * @property string $summary
     * @property string $thumb
     * @property int $year
     */
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->string('id', 36)->primary()->unique();
            $table->unsignedInteger('server_id');
            $table->unsignedInteger('library_id');
            $table->string('title');
            $table->string('audio_codec');
            $table->string('video_codec');
            $table->string('container');
            $table->unsignedFloat('aspect_ratio');
            $table->unsignedInteger('bitrate');
            $table->string('framerate');
            $table->unsignedInteger('height');
            $table->unsignedInteger('width');
            $table->string('profile');
            $table->unsignedInteger('resolution');
            $table->text('summary');
            $table->string('thumb');
            $table->unsignedSmallInteger('year');
            $table->unsignedSmallInteger('duration');
            $table->string('user_id', 36)->index()->nullable(false);
            $table->timestamps();
        });

        Schema::table('media', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
            $table->unique(['server_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('media');
    }
};
