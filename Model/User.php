<?php

namespace Webstack\UserBundle\Model;

use App\Entity\GroupInterface;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class User
 */
abstract class User implements UserInterface, EquatableInterface
{
    private const ROLE_DEFAULT = 'ROLE_USER';

    /**
     * @var String
     */
    protected $firstname;

    /**
     * @var String
     */
    protected $lastnamePrefix;

    /**
     * @var String
     */
    protected $lastname;

    /**
     * @var String
     */
    protected $username;

    /**
     * @var String
     */
    protected $email;

    /**
     * @var Boolean
     */
    protected $enabled;

    /**
     * @var String
     */
    protected $salt;

    /**
     * @var String
     */
    protected $password;

    /**
     * @var String
     */
    protected $plainPassword;

    /**
     * @var DateTime
     */
    protected $lastLogin;

    /**
     * @var String
     */
    protected $confirmationToken;

    /**
     * @var
     */
    protected $passwordRequestedAt;

    /**
     * @var array
     */
    protected $groups;

    /**
     * @var array
     */
    protected $roles;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->enabled = false;
        $this->roles = array();
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
    public function setFirstname($firstname): User
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastNamePrefix(): ?string
    {
        return $this->lastnamePrefix;
    }

    /**
     * @param string $lastNamePrefix
     *
     * @return User
     */
    public function setLastNamePrefix(?string $lastNamePrefix): User
    {
        $this->lastnamePrefix = $lastNamePrefix;

        return $this;
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
     * @return string
     */
    public function getFullName(): string
    {
        return implode(' ', array_filter([
            $this->firstname,
            $this->lastnamePrefix,
            $this->lastname
        ]));
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
    public function eraseCredentials(): void
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
     * {@inheritdoc}
     */
    public function addRole($role)
    {
        $role = strtoupper($role);
        if ($role === static::ROLE_DEFAULT) {
            return $this;
        }

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        $roles = $this->roles;

        foreach ($this->getGroups() as $group) {
            $roles = array_merge($roles, $group->getRoles());
        }

        $roles[] = static::ROLE_DEFAULT;

        return array_unique($roles);
    }

    /**
     * {@inheritdoc}
     */
    public function hasRole($role)
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function getGroups()
    {
        return $this->groups ?: $this->groups = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupNames()
    {
        $names = array();
        foreach ($this->getGroups() as $group) {
            $names[] = $group->getName();
        }

        return $names;
    }

    /**
     * {@inheritdoc}
     */
    public function hasGroup($name)
    {
        return in_array($name, $this->getGroupNames());
    }

    /**
     * {@inheritdoc}
     */
    public function addGroup(GroupInterface $group)
    {
        if (!$this->getGroups()->contains($group)) {
            $this->getGroups()->add($group);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
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