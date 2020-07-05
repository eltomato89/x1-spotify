<?php

namespace App\Entity;

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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastname;

    /**
     * @ORM\Column(name="apiKey", type="string", length=255, unique=true, nullable=true)
     */
    private $apiKey;

    /**
     * @ORM\Column(name="active", type="boolean", nullable=true)
     */
    private $active;

    /**
     * @ORM\Column(name="crmUser", type="string", length=255, nullable=true)
     */
    private $crmUser;

    /**
     * @ORM\Column(name="allowedDistricts", type="simple_array", nullable=true)
     */
    private $allowedDistricts = [];

    /**
     * @ORM\Column(name="allowedRetailPartners", type="simple_array", nullable=true)
     */
    private $allowedRetailPartners = [];

    public function __construct(UserId $id, string $nickname, string $password)
    {
        $this->id = $id;
        $this->credential = new NicknamePassword($nickname, $password);
    }

    public function getId(): UserId
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
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

    public function getCrmUser(): ?string
    {
        return $this->crmUser;
    }

    public function setCrmUser(string $crmUser): self
    {
        $this->crmUser = $crmUser;

        return $this;
    }

    public function getAllowedDistricts(): ?array
    {
        return $this->allowedDistricts;
    }

    public function setAllowedDistricts(array $allowedDistricts): self
    {
        $this->allowedDistricts = $allowedDistricts;

        return $this;
    }

    public function getAllowedRetailPartners(): ?array
    {
        return $this->allowedRetailPartners;
    }

    public function setAllowedRetailPartners(array $allowedRetailPartners): self
    {
        $this->allowedRetailPartners = $allowedRetailPartners;

        return $this;
    }

}
