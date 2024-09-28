<?php
declare(strict_types=1);

namespace App\Service;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\HttpClient;

final class Thumbnailer
{
    public function thumbnail(string $imageContent): string
    {
        $newName = tempnam(sys_get_temp_dir(), 'plex_thumb_') . '.jpg';
        file_put_contents($newName, $imageContent);

        $manager = new ImageManager(new Driver());
        $image = $manager->read($newName)->scale(width: 150)->toWebp();
        unlink($newName);

        return $image->toDataUri();
    }
}
