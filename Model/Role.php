<?php

namespace Webstack\UserBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Role
 */
abstract class Role
{
    /**
     * @var string
     * @ORM\Column(type="string", unique=true, nullable=false)
     */
    protected $role;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    protected $description;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    protected $remark;

    /**
     * @var array|null
     * @ORM\Column(type="array", nullable=true)
     */
    protected $source;

    /**
     * Role constructor.
     */
    public function __construct()
    {
        $this->source = [];
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @param string $role
     */
    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string|null
     */
    public function getRemark(): ?string
    {
        return $this->remark;
    }

    /**
     * @param string|null $remark
     */
    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    /**
     * @return array|null
     */
    public function getSource(): ?array
    {
        return $this->source;
    }

    /**
     * @param array|null $source
     */
    public function setSource(?array $source): void
    {
        $this->source = $source;
    }

    /**
     * @param string $source
     */
    public function addSource(string $source): void
    {
        if(!in_array($source, $this->source, true)) {
            $this->source[] = $source;
        }
    }
}