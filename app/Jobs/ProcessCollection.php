<?php

namespace App\Jobs;

use App\Models\Media;
use App\Models\User;
use Chindit\PlexApi\Enum\LibraryType;
use Chindit\PlexApi\Model\Server;
use Chindit\PlexApi\PlexServer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Ramsey\Uuid\Uuid;

class ProcessCollection implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private User $user)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $server = new PlexServer($this->user->server_url, $this->user->server_token, $this->user->server_port);

        $libraries = $server->libraries();

        foreach ($libraries as $library) {
            if ($library->getType() === LibraryType::Music) {
                continue;
            }

            $medias = $server->library($library->getId());

            $databaseMedias = collect();
            foreach ($medias as $media) {
                $databaseMedias->add([
                    'id' => Uuid::uuid4(),
                    'server_id' => $media->getId(),
                    'library_id' => $library->getId(),
                    'title' => $media->getTitle(),
                    'audio_codec' => $media->getAudioCodec(),
                    'video_codec' => $media->getVideoCodec(),
                    'aspect_ratio' => $media->getAspectRatio(),
                    'bitrate' => $media->getBitrate(),
                    'container' => $media->getContainer(),
                    'duration' => $media->getDuration(),
                    'framerate' => $media->getFramerate(),
                    'height' => $media->getHeight(),
                    'width' => $media->getWidth(),
                    'profile' => $media->getProfile(),
                    'resolution' => $media->getResolution(),
                    'summary' => $media->getSummary(),
                    'thumb' => $media->getThumb(),
                    'year' => (int)$media->getYear(),
                    'user_id' => $this->user->id
                ]);
            }

            Media::upsert($databaseMedias->all(), ['server_id', 'user_id'], ['id', 'server_id', 'library_id', 'title', 'audio_codec', 'video_codec', 'aspect_ratio', 'bitrate', 'container',
                'duration', 'framerate', 'height', 'width', 'profile', 'resolution', 'summary', 'thumb', 'year']);
        }
    }
}
