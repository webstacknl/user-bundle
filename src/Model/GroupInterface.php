<?php

namespace Webstack\UserBundle\Model;

interface GroupInterface
{
    public function addRole(string $role): void;

    public function getName(): string;

    public function hasRole(string $role): bool;

    /**
     * @return array<string>
     */
    public function getRoles(): array;

    public function removeRole(string $role): void;

    public function setName(string $name): void;

    /**
     * @param array<string> $roles
     */
    public function setRoles(array $roles): void;
}
