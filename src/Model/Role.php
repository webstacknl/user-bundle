<?php

namespace Webstack\UserBundle\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

abstract class Role
{
    #[ORM\Column(unique: true)]
    protected string $role = '';

    #[ORM\Column(nullable: true)]
    protected ?string $description = null;

    #[ORM\Column(nullable: true)]
    protected ?string $remark = null;

    /**
     * @var list<string>
     */
    #[ORM\Column(type: Types::ARRAY)]
    protected array $source;

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
     * @return list<string>|null
     */
    public function getSource(): ?array
    {
        return $this->source;
    }

    /**
     * @param list<string> $source
     */
    public function setSource(array $source): void
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
