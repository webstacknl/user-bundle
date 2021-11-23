<?php

namespace Webstack\UserBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Group
 */
abstract class Group implements GroupInterface
{
    /**
     * @ORM\Column(type="string", length=60, unique=true)
     */
    protected $name;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="array")
     */
    protected $roles = [];

    /**
     * @param string $role
     * @return Group
     */
    public function addRole($role): self
    {
        if (!$this->hasRole($role)) {
            $this->roles[] = strtoupper($role);
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Group
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return (array) $this->roles;
    }

    /**
     * @param array $roles
     * @return Group
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @param string $role
     * @return bool
     */
    public function hasRole($role): bool
    {
        if ($this->roles === null) {
            return false;
        }

        return in_array(strtoupper($role), $this->roles, true);
    }
    /**
     * @param string $role
     * @return Group
     */
    public function removeRole($role): self
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);

            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }
}
