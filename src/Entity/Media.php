<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
#[ORM\UniqueConstraint(columns: ['server_id', 'owner_id'])]
class Media
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id = '';

    #[ORM\Column]
    private ?int $serverId = null;

    #[ORM\Column]
    private ?int $libraryId = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $audioCodec = null;

    #[ORM\Column(length: 255)]
    private ?string $videoCodec = null;

    #[ORM\Column]
    private ?float $aspectRatio = null;

    #[ORM\Column]
    private ?int $bitrate = null;

    #[ORM\Column(length: 255)]
    private ?string $framerate = null;

    #[ORM\Column]
    private ?int $height = null;

    #[ORM\Column]
    private ?int $width = null;

    #[ORM\Column(length: 255)]
    private ?string $profile = null;

    #[ORM\Column]
    private ?int $resolution = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $summary = null;

    #[ORM\Column(length: 255)]
    private ?string $thumb = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $year = null;

    #[ORM\Column(length: 255)]
    private ?string $container = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $duration = null;

    #[ORM\ManyToOne(inversedBy: 'medias')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    public function getId(): string
    {
        return $this->id;
    }

    public function getServerId(): ?int
    {
        return $this->serverId;
    }

    public function setServerId(int $serverId): self
    {
        $this->serverId = $serverId;

        return $this;
    }

    public function getLibraryId(): ?int
    {
        return $this->libraryId;
    }

    public function setLibraryId(int $libraryId): self
    {
        $this->libraryId = $libraryId;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getAudioCodec(): ?string
    {
        return $this->audioCodec;
    }

    public function setAudioCodec(string $audioCodec): self
    {
        $this->audioCodec = $audioCodec;

        return $this;
    }

    public function getVideoCodec(): ?string
    {
        return $this->videoCodec;
    }

    public function setVideoCodec(string $videoCodec): self
    {
        $this->videoCodec = $videoCodec;

        return $this;
    }

    public function getAspectRatio(): ?float
    {
        return $this->aspectRatio;
    }

    public function setAspectRatio(float $aspectRatio): self
    {
        $this->aspectRatio = $aspectRatio;

        return $this;
    }

    public function getBitrate(): ?int
    {
        return $this->bitrate;
    }

    public function setBitrate(int $bitrate): self
    {
        $this->bitrate = $bitrate;

        return $this;
    }

    public function getFramerate(): ?string
    {
        return $this->framerate;
    }

    public function setFramerate(string $framerate): self
    {
        $this->framerate = $framerate;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getProfile(): ?string
    {
        return $this->profile;
    }

    public function setProfile(string $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    public function getResolution(): ?int
    {
        return $this->resolution;
    }

    public function setResolution(int $resolution): self
    {
        $this->resolution = $resolution;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    public function getThumb(): ?string
    {
        return $this->thumb;
    }

    public function setThumb(string $thumb): self
    {
        $this->thumb = $thumb;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getContainer(): ?string
    {
        return $this->container;
    }

    public function setContainer(string $container): self
    {
        $this->container = $container;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}
