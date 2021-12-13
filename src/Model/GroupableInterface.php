<?php

namespace Webstack\UserBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

interface GroupableInterface
{
    /**
     * @return ArrayCollection<GroupInterface>
     */
    public function getGroups(): Collection;

    /**
     * @return array<string>
     */
    public function getGroupNames(): array;

    public function hasGroup(string $name): bool;

    public function addGroup(GroupInterface $group): void;

    public function removeGroup(GroupInterface $group): void;
}