<?php

namespace Webstack\UserBundle\Model;

use Doctrine\ORM\Mapping as ORM;

abstract class Role
{
    /**
     * @ORM\Column(type="string", unique=true)
     */
    protected string $role = '';

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $description = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $remark = null;

    /**
     * @var array<string>|null
     *
     * @ORM\Column(type="array", nullable=true)
     */
    protected ?array $source = null;

    public function __construct()
    {
        $this->source = [];
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    /**
     * @return array<string>|null
     */
    public function getSource(): ?array
    {
        return $this->source;
    }

    /**
     * @param array<string>|null $source
     */
    public function setSource(?array $source): void
    {
        $this->source = $source;
    }

    public function addSource(string $source): void
    {
        if (!in_array($source, $this->source, true)) {
            $this->source[] = $source;
        }
    }
}
