<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use MsgPhp\Domain\Model\CanBeEnabled;
use MsgPhp\Domain\Model\CreatedAtField;
use MsgPhp\User\User as BaseUser;
use MsgPhp\User\UserId;
use MsgPhp\Domain\Event\DomainEventHandler;
use MsgPhp\Domain\Event\DomainEventHandlerTrait;
use MsgPhp\User\Credential\NicknamePassword;
use MsgPhp\User\Model\NicknamePasswordCredential;
use MsgPhp\User\Model\ResettablePassword;
use MsgPhp\User\Model\RolesField;

/**
 * @ORM\Entity()
 */
class User extends BaseUser implements DomainEventHandler
{
    use DomainEventHandlerTrait;
    use NicknamePasswordCredential;
    use ResettablePassword;
    use RolesField;

    #use CreatedAtField;
    #use CanBeEnabled;

    /** @ORM\Id() @ORM\GeneratedValue() @ORM\Column(type="msgphp_user_id", length=191) */
    private $id;

    /**
     * @ORM\Column(name="apiKey", type="string", length=255, unique=true, nullable=true)
     */
    private $apiKey;

    /**
     * @ORM\Column(name="active", type="boolean", nullable=true)
     */
    private $active;

    /**
     * @ORM\OneToMany(targetEntity=SpotifyCredentials::class, mappedBy="user", orphanRemoval=true)
     */
    private $spotifyCredentials;

    /**
     * @ORM\OneToMany(targetEntity=SpotifyPlaylists::class, mappedBy="user", orphanRemoval=true)
     */
    private $spotifyPlaylists;

    /**
     * @ORM\OneToMany(targetEntity=SpotifyPlayer::class, mappedBy="user", orphanRemoval=true)
     */
    private $spotifyPlayers;

    public function __construct(UserId $id, string $nickname, string $password)
    {
        $this->id = $id;
        $this->credential = new NicknamePassword($nickname, $password);
        $this->spotifyCredentials = new ArrayCollection();
        $this->spotifyPlaylists = new ArrayCollection();
        $this->spotifyPlayers = new ArrayCollection();
    }

    public function getId(): UserId
    {
        return $this->id;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(?string $apiKey): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection|SpotifyCredentials[]
     */
    public function getSpotifyCredentials(): Collection
    {
        return $this->spotifyCredentials;
    }

    public function addSpotifyCredential(SpotifyCredentials $spotifyCredential): self
    {
        if (!$this->spotifyCredentials->contains($spotifyCredential)) {
            $this->spotifyCredentials[] = $spotifyCredential;
            $spotifyCredential->setUser($this);
        }

        return $this;
    }

    public function removeSpotifyCredential(SpotifyCredentials $spotifyCredential): self
    {
        if ($this->spotifyCredentials->contains($spotifyCredential)) {
            $this->spotifyCredentials->removeElement($spotifyCredential);
            // set the owning side to null (unless already changed)
            if ($spotifyCredential->getUser() === $this) {
                $spotifyCredential->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SpotifyPlaylists[]
     */
    public function getSpotifyPlaylists(): Collection
    {
        return $this->spotifyPlaylists;
    }

    public function addSpotifyPlaylist(SpotifyPlaylists $spotifyPlaylist): self
    {
        if (!$this->spotifyPlaylists->contains($spotifyPlaylist)) {
            $this->spotifyPlaylists[] = $spotifyPlaylist;
            $spotifyPlaylist->setUser($this);
        }

        return $this;
    }

    public function removeSpotifyPlaylist(SpotifyPlaylists $spotifyPlaylist): self
    {
        if ($this->spotifyPlaylists->contains($spotifyPlaylist)) {
            $this->spotifyPlaylists->removeElement($spotifyPlaylist);
            // set the owning side to null (unless already changed)
            if ($spotifyPlaylist->getUser() === $this) {
                $spotifyPlaylist->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SpotifyPlayer[]
     */
    public function getSpotifyPlayers(): Collection
    {
        return $this->spotifyPlayers;
    }

    public function addSpotifyPlayer(SpotifyPlayer $spotifyPlayer): self
    {
        if (!$this->spotifyPlayers->contains($spotifyPlayer)) {
            $this->spotifyPlayers[] = $spotifyPlayer;
            $spotifyPlayer->setUser($this);
        }

        return $this;
    }

    public function removeSpotifyPlayer(SpotifyPlayer $spotifyPlayer): self
    {
        if ($this->spotifyPlayers->contains($spotifyPlayer)) {
            $this->spotifyPlayers->removeElement($spotifyPlayer);
            // set the owning side to null (unless already changed)
            if ($spotifyPlayer->getUser() === $this) {
                $spotifyPlayer->setUser(null);
            }
        }

        return $this;
    }

}
