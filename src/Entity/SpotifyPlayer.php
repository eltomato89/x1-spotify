<?php

namespace App\Entity;

use App\Repository\SpotifyPlayerRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SpotifyPlayerRepository::class)
 */
class SpotifyPlayer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $identifier;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $spotifyDeviceId;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="spotifyPlayers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getSpotifyDeviceId(): ?string
    {
        return $this->spotifyDeviceId;
    }

    public function setSpotifyDeviceId(string $spotifyDeviceId): self
    {
        $this->spotifyDeviceId = $spotifyDeviceId;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
