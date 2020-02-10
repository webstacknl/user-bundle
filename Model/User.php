<?php

namespace Webstack\UserBundle\Model;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Rollerworks\Component\PasswordStrength\Validator\Constraints\PasswordStrength;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class User
 */
abstract class User implements UserInterface, EquatableInterface, GroupableInterface
{
    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    protected $firstname;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    protected $lastnamePrefix;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    protected $lastname;

    /**
     * @ORM\Column(type="string", length=64, unique=true)
     */
    protected $username;

    /**
     * @ORM\Column(type="string", length=64)
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
     * Get firstname
     *
     * @return null|string
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     * @return User
     */
    public function setFirstname(string $firstname): User
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get lastname prefix
     *
     * @return null|string
     */
    public function getLastnamePrefix(): ?string
    {
        return $this->lastnamePrefix;
    }

    /**
     * @param mixed $lastnamePrefix
     */
    public function setLastnamePrefix($lastnamePrefix): void
    {
        $this->lastnamePrefix = $lastnamePrefix;
    }


    /**
     * Get lastname
     *
     * @return null|string
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     * @return User
     */
    public function setLastname($lastname): User
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get full name
     *
     * @return null|string
     */
    public function getFullName(): ?string
    {
        if (!$this->firstname && !$this->lastname) {
            return $this->username;
        }

        return $this->firstname . ' ' . $this->lastname;
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
     * @return User
     */
    public function setUsername($username): User
    {
        $this->username = $username;

        return $this;
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
     * @return User
     */
    public function setEmail($email): User
    {
        $this->email = $email;

        return $this;
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
     * @return User
     */
    public function setEnabled($enabled): User
    {
        $this->enabled = $enabled;

        return $this;
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
     * @return User
     */
    public function setSalt($salt): User
    {
        $this->salt = $salt;

        return $this;
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
     * @return User
     */
    public function setPassword($password): User
    {
        $this->password = $password;

        return $this;
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
     * Set plain password
     *
     * @param string $plainPassword
     * @return User
     */
    public function setPlainPassword($plainPassword): User
    {
        $this->plainPassword = $plainPassword;

        return $this->setPassword(password_hash($plainPassword, PASSWORD_BCRYPT, [
            'costs' => 12
        ]));
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
     * @return User
     */
    public function setLastLogin(?DateTime $time): User
    {
        $this->lastLogin = $time;

        return $this;
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
     * @return User
     */
    public function setConfirmationToken($confirmationToken): User
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
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
     * @return User
     */
    public function setPasswordRequestedAt(DateTime $date = null): User
    {
        $this->passwordRequestedAt = $date;

        return $this;
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
     * @return User
     */
    public function setLocked($locked): User
    {
        $this->locked = $locked;

        return $this;
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
     * @return User
     */
    public function setExpired($expired): ?User
    {
        $this->expired = $expired;

        return $this;
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
     * @return User
     */
    public function setExpiresAt(DateTime $expiresAt): User
    {
        $this->expiresAt = $expiresAt;

        return $this;
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
     * @return User
     */
    public function setRoles(array $roles): User
    {
        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    /**
     * Add role
     *
     * @param $role
     * @return User
     */
    public function addRole($role): User
    {
        $role = strtoupper($role);

        if ($role === 'ROLE_USER' || $role === 'ROLE_ADMIN') {
            return $this;
        }

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
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
     * @return User
     */
    public function setCredentialsExpired($credentialsExpired): User
    {
        $this->credentialsExpired = $credentialsExpired;

        return $this;
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
     * @return User
     */
    public function setCredentialsExpireAt(DateTime $datetime): User
    {
        $this->credentialsExpireAt = $datetime;

        return $this;
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
     * @return User|GroupableInterface
     */
    public function addGroup(GroupInterface $group)
    {
        if (!$this->getGroups()->contains($group)) {
            $this->getGroups()->add($group);
        }
        return $this;
    }

    /**
     * @param GroupInterface $group
     * @return $this|GroupableInterface
     */
    public function removeGroup(GroupInterface $group)
    {
        if ($this->getGroups()->contains($group)) {
            $this->getGroups()->removeElement($group);
        }
        return $this;
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
}