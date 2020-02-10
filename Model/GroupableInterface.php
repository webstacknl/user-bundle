<?php

namespace Webstack\UserBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Class GroupableInterface
 */
interface GroupableInterface
{
    /**
     * Gets groups granted to the user.
     *
     * @return ArrayCollection
     */
    public function getGroups(): Collection;

    /**
     * Gets the name of the groups which includes the user.
     *
     * @return array
     */
    public function getGroupNames(): array;

    /**
     * Indicates whether the user belongs to the specified group or not.
     *
     * @param string $name Name of the group
     *
     * @return bool
     */
    public function hasGroup($name): bool;

    /**
     * Add a group to the user groups.
     *
     * @param GroupInterface $group
     * @return static
     */
    public function addGroup(GroupInterface $group);

    /**
     * Remove a group from the user groups.
     *
     * @param GroupInterface $group
     * @return static
     */
    public function removeGroup(GroupInterface $group);
}