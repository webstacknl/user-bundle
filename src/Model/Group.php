<?php

namespace Webstack\UserBundle\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

abstract class Group implements GroupInterface
{
    #[ORM\Column(length: 60, unique: true)]
    protected string $name = '';

    #[ORM\Column(nullable: true)]
    protected ?string $description = null;

    /**
     * @var list<string>
     */
    #[ORM\Column(type: Types::JSON)]
    protected array $roles = [];

    public function addRole(string $role): void
    {
        if (false === $this->hasRole($role)) {
            $this->roles[] = strtoupper($role);
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function hasRole(string $role): bool
    {
        return in_array(strtoupper($role), $this->roles, true);
    }

    public function removeRole(string $role): void
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);

            $this->roles = array_values($this->roles);
        }
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }
}
