<?php

namespace Webstack\UserBundle\Model;

interface GroupInterface
{
    public function addRole(string $role): void;

    public function getName(): string;

    public function hasRole(string $role): bool;

    /**
     * @return list<string>
     */
    public function getRoles(): array;

    public function removeRole(string $role): void;

    public function setName(string $name): void;

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): void;
}
