<?php

namespace Webstack\UserBundle\Model;

/**
 * Interface GroupInterface
 */
interface GroupInterface
{
    /**
     * @param string $role
     * @return static
     */
    public function addRole($role);

    /**
     * @return string
     */
    public function getName(): ?string;

    /**
     * @param string $role
     * @return bool
     */
    public function hasRole($role): bool;

    /**
     * @return array
     */
    public function getRoles(): array;

    /**
     * @param string $role
     * @return static
     */
    public function removeRole($role);

    /**
     * @param string $name
     * @return static
     */
    public function setName($name);

    /**
     * @param array $roles
     * @return static
     */
    public function setRoles(array $roles);
}