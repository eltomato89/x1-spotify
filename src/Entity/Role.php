<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use MsgPhp\User\Role as BaseRole;

/**
 * @ORM\Entity()
 *
 * @final
 */
class Role extends BaseRole
{
    /** @ORM\Id() @ORM\Column(length=191) */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    private $descriptionLong;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hidden;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDescriptionLong(): ?string
    {
        return $this->descriptionLong;
    }

    public function setDescriptionLong(?string $descriptionLong): self
    {
        $this->descriptionLong = $descriptionLong;

        return $this;
    }

    public function getHidden(): ?bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): self
    {
        $this->hidden = $hidden;

        return $this;
    }
}
