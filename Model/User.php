<?php

namespace Webstack\UserBundle\Model;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface as EmailTwoFactorInterface;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as GoogleTwoFactorInterface;
use Scheb\TwoFactorBundle\Model\PreferredProviderInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Webstack\AdminBundle\Traits\PrimaryUuidTrait;
use Webstack\AdminBundle\Traits\User\EmailTwoFactorTrait;
use Webstack\AdminBundle\Traits\User\GoogleTwoFactorTrait;

/**
 * Class User
 */
abstract class User implements UserInterface, EquatableInterface, GroupableInterface, PreferredProviderInterface, GoogleTwoFactorInterface, EmailTwoFactorInterface
{
    use PrimaryUuidTrait;
    use TimestampableEntity;
    use GoogleTwoFactorTrait;
    use EmailTwoFactorTrait;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    protected $firstName;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $lastNamePrefix;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    protected $lastName;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    protected $username;

    /**
     * @ORM\Column(type="string")
     */
    protected $email;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $enabled;

    /**
     * The salt to use for hashing
     *
     * @ORM\Column(type="string")
     */
    protected $salt;

    /**
     * Encrypted password. Must be persisted.
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $password;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     *
     * @var string
     *
     * @Assert\NotCompromisedPassword()
     */
    protected $plainPassword;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $lastLogin;

    /**
     * Random string sent to the user email address in order to verify it
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $confirmationToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $passwordRequestedAt;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $locked;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $expired;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $expiresAt;

    /**
     * @ORM\Column(type="array")
     */
    protected $roles = ['ROLE_USER'];

    /**
     * @var GroupInterface|Collection
     */
    protected $groups;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $credentialsExpired;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $credentialsExpireAt;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->enabled = false;
        $this->locked = false;
        $this->expired = false;
        $this->roles = ['ROLE_USER'];
        $this->credentialsExpired = false;
    }

    /**
     * Get firstName
     *
     * @return null|string
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * Get lastName prefix
     *
     * @return null|string
     */
    public function getLastNamePrefix(): ?string
    {
        return $this->lastNamePrefix;
    }

    /**
     * @param mixed $lastNamePrefix
     */
    public function setLastNamePrefix($lastNamePrefix): void
    {
        $this->lastNamePrefix = $lastNamePrefix;
    }


    /**
     * Get lastName
     *
     * @return null|string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     */
    public function setLastName($lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * Get full name
     *
     * @return null|string
     */
    public function getFullName(): ?string
    {
        if (!$this->firstName && !$this->lastName) {
            return $this->username;
        }

        return implode(' ', array_filter([$this->firstName, $this->lastNamePrefix, $this->lastName]));
    }

    /**
     * Get username
     *
     * @return null|string
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * Set username
     *
     * @param string $username
     */
    public function setUsername($username): void
    {
        $this->username = $username;
    }

    /**
     * Get Email
     *
     * @return null|string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }


    /**
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * Get enabled
     *
     * @return bool
     */
    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Set enabled
     *
     * @param bool $enabled
     */
    public function setEnabled($enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get salt
     *
     * @return null|string
     */
    public function getSalt(): ?string
    {
        return $this->salt;
    }

    /**
     * Set salt
     *
     * @param string $salt
     */
    public function setSalt($salt): void
    {
        $this->salt = $salt;
    }

    /**
     * Get password (bcrypt)
     *
     * @return null|string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Set password (bcrypt)
     *
     * @param string $password
     */
    public function setPassword($password): void
    {
        $this->password = $password;
    }

    /**
     * Get plain password
     *
     * @return string
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     */
    public function setPlainPassword(string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;

        // forces the object to look "dirty" to Doctrine. Avoids
        // Doctrine *not* saving this entity, if only plainPassword changes
        $this->password = null;

        try {
            $this->updatedAt = new DateTime();
        } catch (Exception $e) {
            //
        }
    }

    /**
     * Removes sensitive data from the user.
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    /**
     * Get last login
     *
     * @return DateTime|null
     */
    public function getLastLogin(): ?DateTime
    {
        return $this->lastLogin;
    }

    /**
     * Set last login
     *
     * @param DateTime|null $time
     */
    public function setLastLogin(?DateTime $time): void
    {
        $this->lastLogin = $time;
    }

    /**
     * Get confirmation token
     *
     * @return string
     */
    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    /**
     * Set confirmation token
     *
     * @param string $confirmationToken
     */
    public function setConfirmationToken($confirmationToken): void
    {
        $this->confirmationToken = $confirmationToken;
    }

    /**
     * Get password requested at
     *
     * @return null|DateTime
     */
    public function getPasswordRequestedAt(): ?DateTime
    {
        return $this->passwordRequestedAt;
    }

    /**
     * Set password request at
     *
     * @param DateTime|null $date
     */
    public function setPasswordRequestedAt(DateTime $date = null): void
    {
        $this->passwordRequestedAt = $date;
    }

    /**
     * Get locked
     *
     * @return bool
     */
    public function getLocked(): ?bool
    {
        return $this->locked;
    }

    /**
     * Set locked
     *
     * @param bool $locked
     */
    public function setLocked($locked): void
    {
        $this->locked = $locked;
    }

    /**
     * @return bool
     */
    public function isAccountNonLocked(): ?bool
    {
        return !$this->locked;
    }

    /**
     * Get expired
     *
     * @return bool
     */
    public function getExpired(): bool
    {
        return $this->expired;
    }

    /**
     * Set expired
     *
     * @param bool $expired
     */
    public function setExpired($expired): void
    {
        $this->expired = $expired;
    }

    /**
     * Get expires at
     *
     * @return DateTime
     */
    public function getExpiresAt(): ?DateTime
    {
        return $this->expiresAt;
    }

    /**
     * Set expires at
     *
     * @param DateTime $expiresAt
     */
    public function setExpiresAt(DateTime $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }

    /**
     * @return bool
     */
    public function isAccountNonExpired(): bool
    {
        if (true === $this->expired) {
            return false;
        }

        if (null !== $this->expiresAt && $this->expiresAt->getTimestamp() < time()) {
            return false;
        }

        return true;
    }


    /**
     * Get roles
     *
     * @param bool $withGroups
     *
     * @return array
     */
    public function getRoles($withGroups = true): ?array
    {
        $roles = $this->roles;

        if ($withGroups) {
            foreach ($this->getGroups() as $group) {
                foreach ($group->getRoles() as $role) {
                    $roles[] = $role;
                }
            }
        }

        //guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * Set roles
     *
     * @param array $roles
     */
    public function setRoles(array $roles): void
    {
        foreach ($roles as $role) {
            $this->addRole($role);
        }
    }

    /**
     * Add role
     *
     * @param $role
     */
    public function addRole($role): void
    {
        $role = strtoupper($role);

        if ($role === 'ROLE_USER') {
            return;
        }

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }
    }

    /**
     * Check is role is present
     *
     * @param $role
     * @return bool
     */
    public function hasRole($role): bool
    {
        return in_array($role, $this->roles, true);
    }

    /**
     * @param string $role
     */
    public function removeRole(string $role)
    {
        $key = array_search($role, $this->roles, true);

        if ($key !== false) {
            unset($this->roles[$key]);
        }

        // Ensure at least ROLE_ADMIN is set
        if (empty($this->roles)) {
            $this->roles[] = 'ROLE_ADMIN';
        }
    }

    /**
     * @return bool
     */
    final public function isSuperAdmin(): bool
    {
        return $this->hasRole('ROLE_SUPER_ADMIN');
    }

    /**
     * Set credentials expired
     *
     * @param bool $credentialsExpired
     */
    public function setCredentialsExpired($credentialsExpired): void
    {
        $this->credentialsExpired = $credentialsExpired;
    }

    /**
     * Get credentials expired
     *
     * @return bool
     */
    public function getCredentialsExpired(): ?bool
    {
        return $this->credentialsExpired;
    }

    /**
     * Set credentials expired at
     *
     * @param DateTime $datetime
     */
    public function setCredentialsExpireAt(DateTime $datetime): void
    {
        $this->credentialsExpireAt = $datetime;
    }

    /**
     * Get credentials expired at
     *
     * @return DateTime
     */
    public function getCredentialsExpireAt(): ?DateTime
    {
        return $this->credentialsExpireAt;
    }

    /**
     * @return bool
     */
    public function isCredentialsNonExpired(): bool
    {
        if (true === $this->credentialsExpired) {
            return false;
        }

        if (null !== $this->credentialsExpireAt && $this->credentialsExpireAt->getTimestamp() < time()) {
            return false;
        }

        return true;
    }

    /**
     * @return ArrayCollection|GroupInterface[]
     */
    public function getGroups(): Collection
    {
        return $this->groups ?: $this->groups = new ArrayCollection();
    }

    /**
     * @return array
     */
    public function getGroupNames(): array
    {
        $names = array();

        foreach ($this->getGroups() as $group) {
            $names[] = $group->getName();
        }

        return $names;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasGroup($name): bool
    {
        return in_array($name, $this->getGroupNames(), true);
    }

    /**
     * @param GroupInterface $group
     */
    public function addGroup(GroupInterface $group)
    {
        if (!$this->getGroups()->contains($group)) {
            $this->getGroups()->add($group);
        }
    }

    /**
     * @param GroupInterface $group
     */
    public function removeGroup(GroupInterface $group)
    {
        if ($this->getGroups()->contains($group)) {
            $this->getGroups()->removeElement($group);
        }
    }

    /**
     * @param UserInterface $user
     * @return bool
     */
    public function isEqualTo(UserInterface $user): bool
    {
        if (!$user instanceof self) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->salt !== $user->getSalt()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getPreferredTwoFactorProvider(): string
    {
        return 'google';
    }
}