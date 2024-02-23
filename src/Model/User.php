<?php

namespace Webstack\UserBundle\Model;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Webstack\AdminBundle\Traits\PrimaryUuidTrait;
use Webstack\AdminBundle\Traits\User\EmailTwoFactorTrait;
use Webstack\AdminBundle\Traits\User\GoogleTwoFactorTrait;

abstract class User implements UserInterface, PasswordAuthenticatedUserInterface, EquatableInterface, GroupableInterface
{
    use PrimaryUuidTrait;
    use TimestampableEntity;
    use GoogleTwoFactorTrait;
    use EmailTwoFactorTrait;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    protected ?string $firstName = null;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected ?string $lastNamePrefix = null;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    protected ?string $lastName = null;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    protected ?string $username = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $email = null;

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $enabled = true;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $password = null;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     */
    protected string $plainPassword = '';

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?\DateTime $lastLogin = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $confirmationToken = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?\DateTime $passwordRequestedAt = null;

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $locked = false;

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $expired = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?\DateTime $expiresAt = null;

    /**
     * @var array<string>
     *
     * @ORM\Column(type="array")
     */
    protected array $roles = ['ROLE_USER'];

    /**
     * @var Collection<GroupInterface>|null
     */
    protected ?Collection $groups = null;

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $credentialsExpired = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?\DateTime $credentialsExpireAt = null;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastNamePrefix(): ?string
    {
        return $this->lastNamePrefix;
    }

    public function setLastNamePrefix(?string $lastNamePrefix): void
    {
        $this->lastNamePrefix = $lastNamePrefix;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getFullName(): ?string
    {
        if (!$this->firstName && !$this->lastName) {
            return $this->username;
        }

        return implode(' ', array_filter([$this->firstName, $this->lastNamePrefix, $this->lastName]));
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): void
    {
        $this->password = null;
        $this->plainPassword = $plainPassword;

        $this->updatedAt = new \DateTime();
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = '';
    }

    public function getLastLogin(): ?\DateTime
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTime $time): void
    {
        $this->lastLogin = $time;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken): void
    {
        $this->confirmationToken = $confirmationToken;
    }

    public function getPasswordRequestedAt(): ?\DateTime
    {
        return $this->passwordRequestedAt;
    }

    public function setPasswordRequestedAt(?\DateTime $date = null): void
    {
        $this->passwordRequestedAt = $date;
    }

    public function getLocked(): ?bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked): void
    {
        $this->locked = $locked;
    }

    public function isAccountNonLocked(): ?bool
    {
        return !$this->locked;
    }

    public function getExpired(): bool
    {
        return $this->expired;
    }

    public function setExpired(bool $expired): void
    {
        $this->expired = $expired;
    }

    public function getExpiresAt(): ?\DateTime
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTime $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }

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

    public function getRoles(bool $withGroups = true): array
    {
        $roles = $this->roles;

        if ($withGroups) {
            foreach ($this->getGroups() as $group) {
                foreach ($group->getRoles() as $role) {
                    $roles[] = $role;
                }
            }
        }

        // Guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param array<string> $roles
     */
    public function setRoles(array $roles): void
    {
        foreach ($roles as $role) {
            $this->addRole($role);
        }
    }

    public function addRole(string $role): void
    {
        $role = strtoupper($role);

        if ('ROLE_USER' === $role) {
            return;
        }

        if (false === in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles, true);
    }

    public function removeRole(string $role): void
    {
        $key = array_search($role, $this->roles, true);

        if (false !== $key) {
            unset($this->roles[$key]);
        }
    }

    final public function isSuperAdmin(): bool
    {
        return $this->hasRole('ROLE_SUPER_ADMIN');
    }

    public function setCredentialsExpired(bool $credentialsExpired): void
    {
        $this->credentialsExpired = $credentialsExpired;
    }

    public function getCredentialsExpired(): ?bool
    {
        return $this->credentialsExpired;
    }

    public function setCredentialsExpireAt(\DateTime $datetime): void
    {
        $this->credentialsExpireAt = $datetime;
    }

    public function getCredentialsExpireAt(): ?\DateTime
    {
        return $this->credentialsExpireAt;
    }

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
     * @return Collection<GroupInterface>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function getGroupNames(): array
    {
        $names = [];

        foreach ($this->getGroups() as $group) {
            $names[] = $group->getName();
        }

        return $names;
    }

    public function hasGroup(string $name): bool
    {
        return in_array($name, $this->getGroupNames(), true);
    }

    public function addGroup(GroupInterface $group): void
    {
        $this->getGroups()->add($group);
    }

    public function removeGroup(GroupInterface $group): void
    {
        $this->getGroups()->removeElement($group);
    }

    public function isEqualTo(UserInterface $user): bool
    {
        if (false === $user instanceof self) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        return true;
    }

    public function getPreferredTwoFactorProvider(): string
    {
        return 'google';
    }
}
