<?php
declare(strict_types=1);

namespace App\Service;

use Intervention\Image\Constraint;
use Intervention\Image\Facades\Image;
use Symfony\Component\HttpClient\HttpClient;

final class Thumbnailer
{
    public function thumbnail(string $url): string
    {
        $newName = tempnam(sys_get_temp_dir(), 'plex_thumb_') . '.jpg';
        $resizedName = tempnam(sys_get_temp_dir(), 'plex_thumb_resized_') . '.jpg';
        file_put_contents($newName, HttpClient::create()->request('GET', $url)->getContent());

        Image::make($newName)
            ->widen(150, function (Constraint $constraint) {
                $constraint->aspectRatio();
            })
            ->save($resizedName);

        return $resizedName;
    }
}
