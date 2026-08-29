<?php
declare(strict_types=1);

namespace App\Service;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\HttpClient;

final class Thumbnailer
{
    public function thumbnail(string $url): string
    {
        try {
            $imageContent = HttpClient::create(['verify_host' => false])->request('GET', $url)->getContent();
        } catch (ClientException $exception) {
            return '';
        }

        $newName = tempnam(sys_get_temp_dir(), 'plex_thumb_') . '.jpg';
        $resizedName = tempnam(sys_get_temp_dir(), 'plex_thumb_resized_') . '.jpg';
        file_put_contents($newName, $imageContent);

        $manager = ImageManager::usingDriver(Driver::class);
        $manager->decodePath($newName)->scale(width: 150)->save($resizedName);
        unlink($newName);

        return $resizedName;
    }
}
