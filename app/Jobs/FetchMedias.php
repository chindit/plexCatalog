<?php

namespace App\Jobs;

use App\Events\ReportUpdated;
use Chindit\PlexApi\Account;
use Chindit\PlexApi\Model\Server;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class FetchMedias implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $channelToken,
        private readonly array $libraries,
        private readonly string $token,
        private readonly ?string $url = null,
        private readonly int $port = 32400,
        private readonly bool $unwatchedOnly = false,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ReportUpdated::dispatch($this->channelToken, 'Media fetching started');

        $account = new Account($this->token, $this->url, $this->port);
        /** @var Collection<Server> $servers */
        $servers = collect($account->getServerList())->keyBy('identifier');
        $movies = collect();

        foreach ($this->libraries as $selection) {
            [$serverId, $libraryId] = explode(':', $selection, 2);
            $plexServer = $account->getServer($serverId);
            $serverModel = $servers->firstWhere(fn ($server) => $server->identifier === $serverId);
            $connection = $serverModel?->getConnections()
                ->filter(fn ($connection) => !$connection->isLocal)
                ->first();

            if (!$connection || !ctype_digit($libraryId)) {
                throw new \InvalidArgumentException('Invalid server or library selection');
            }

            $serverUrl = rtrim($connection->host, '/');
            if (parse_url($serverUrl, PHP_URL_SCHEME) === null || parse_url($serverUrl, PHP_URL_PORT) === null) {
                $serverUrl .= ':' . $connection->port;
            }

            foreach ($plexServer->library((int) $libraryId, $this->unwatchedOnly) as $movie) {
                $movies->push([
                    'movie' => $movie,
                    'server' => [
                        's' => $serverUrl,
                        't' => $serverModel->token,
                        'p' => $connection->port,
                    ],
                ]);
            }
        }

        $path = "reports/{$this->channelToken}.serialized";
        Storage::disk('local')->put($path, serialize($movies->all()));

        ReportUpdated::dispatch(
            $this->channelToken,
            sprintf('Media fetching completed. %d medias found', $movies->count()),
        );
    }
}
