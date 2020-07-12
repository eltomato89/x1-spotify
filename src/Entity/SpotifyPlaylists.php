<?php

namespace App\Entity;

use App\Repository\SpotifyPlaylistsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SpotifyPlaylistsRepository::class)
 */
class SpotifyPlaylists
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="spotifyPlaylists")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $playlists = [];

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPlaylists(): ?array
    {
        return $this->playlists;
    }

    public function setPlaylists(?array $playlists): self
    {
        $this->playlists = $playlists;

        return $this;
    }
}
