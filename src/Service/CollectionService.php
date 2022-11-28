<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Media as MediaEntity;
use App\Entity\User;
use App\Repository\MediaRepository;
use Chindit\PlexApi\Enum\LibraryType;
use Chindit\PlexApi\PlexServer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final class CollectionService
{
    public function __construct(private EntityManagerInterface $entityManager, private MediaRepository $mediaRepository)
    {}

    public function syncCollection(User $user): void
    {
        $server = new PlexServer($user->getServerUrl(), $user->getServerToken(), $user->getServerPort());

        $libraries = $server->libraries();

        foreach ($libraries as $library) {
            if ($library->getType() === LibraryType::Music) {
                continue;
            }

            $medias = $server->library($library->getId());

            foreach ($medias as $media) {
                //$persistedMedia = $this->mediaRepository->findOneBy(['owner' => $user, 'serverId' => $media->getId()]);
                //if (!$persistedMedia) {
                    $persistedMedia = new MediaEntity();
                //}
                $this->entityManager->persist(
                    $persistedMedia
                    ->setServerId($media->getId())
                    ->setLibraryId($library->getId())
                    ->setTitle($media->getTitle())
                    ->setAudioCodec($media->getAudioCodec())
                    ->setVideoCodec($media->getVideoCodec())
                    ->setAspectRatio($media->getAspectRatio())
                    ->setBitrate($media->getBitrate())
                    ->setContainer($media->getContainer())
                    ->setDuration($media->getDuration())
                    ->setFramerate($media->getFramerate())
                    ->setHeight($media->getHeight())
                    ->setWidth($media->getWidth())
                    ->setProfile($media->getProfile())
                    ->setResolution($media->getResolution())
                    ->setSummary($media->getSummary())
                    ->setThumb($media->getThumb())
                    ->setYear((int)$media->getYear())
                    ->setOwner($user)
                );
                           //dump($persistedMedia->getServerId(), $persistedMedia->getLibraryId());
                /*$this->entityManager->persist($persistedMedia);
                try {
                    $this->entityManager->flush();
                } catch (UniqueConstraintViolationException $e) {

                }    */
                //$this->entityManager->getConnection()->prepare(
                $this->entityManager->getConnection()->prepare(
                    "INSERT INTO media (id, server_id, library_id, title, audio_codec, video_codec, aspect_ratio, bitrate, framerate,
                   height, width, profile, resolution, summary, thumb, year, container, duration, owner_id) VALUES (
                    :id, :serverId, :libraryId, :title, :audio, :video, :ratio, :bitrate, :framerate, :height, :width, :profile, :resolution, :summary, :thumb, :year, :container, :duration, :owner) ON CONFLICT (server_id,owner_id) DO UPDATE SET
                    server_id = :serverId, library_id = :libraryId, title = :title, audio_codec = :audio, video_codec = :video, aspect_ratio = :ratio, bitrate = :bitrate, framerate = :framerate,
                   height = :height, width = :width, profile = :profile, resolution = :resolution, summary = :summary, thumb = :thumb, year = :year, container = :container, duration = :duration"
                )->executeStatement(
                    [
                        'id' => (string)Uuid::v4(),
                        'serverId' => $persistedMedia->getServerId(),
                        'libraryId' => $persistedMedia->getLibraryId(),
                        'title' => $persistedMedia->getTitle(),
                        'audio' => $persistedMedia->getAudioCodec(),
                        'video' => $persistedMedia->getVideoCodec(),
                        'ratio' => $persistedMedia->getAspectRatio(),
                        'bitrate' => $persistedMedia->getBitrate(),
                        'framerate' => $persistedMedia->getFramerate(),
                        'height' => $persistedMedia->getHeight(),
                        'width' => $persistedMedia->getWidth(),
                        'profile' => $persistedMedia->getProfile(),
                        'resolution' => $persistedMedia->getResolution(),
                        'summary' => $persistedMedia->getSummary(),
                        'thumb' => $persistedMedia->getThumb(),
                        'year' => $persistedMedia->getYear(),
                        'container' => $persistedMedia->getContainer(),
                        'duration' => $persistedMedia->getDuration(),
                        'owner' => $persistedMedia->getOwner()->getId(),
                    ]
                );
            }

            //$this->entityManager->flush();
        }
    }
}
