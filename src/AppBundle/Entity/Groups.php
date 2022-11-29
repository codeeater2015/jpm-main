<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Groups
 *
 * @ORM\Table(name="tbl_group")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GroupsRepository")
 * @UniqueEntity(fields={"groupName"},message="This group has already been created",errorPath="groupName")
 */
class Groups
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="group_name", type="string", length=85, unique=true)
     * @Assert\NotBlank()
     */
    private $groupName;

    /**
     * @var string
     *
     * @ORM\Column(name="access_level", type="string", length=85, unique=true)
     */
    private $accessLevel;

    /**
     * @var int
     *
     * @ORM\Column(name="allow_read", type="integer")
     */
    private $allowRead;

    /**
     * @var int
     *
     * @ORM\Column(name="allow_write", type="integer")
     */
    private $allowWrite;

    /**
     * @var string
     *
     * @ORM\Column(name="group_desc", type="text", nullable=true)
     * @Assert\Length(max="255")
     */
    private $groupDesc;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer", nullable=true)
     */
    private $status;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set groupName
     *
     * @param string $groupName
     *
     * @return Groups
     */
    public function setGroupName($groupName)
    {
        $this->groupName = $groupName;

        return $this;
    }

    /**
     * Get groupName
     *
     * @return string
     */
    public function getGroupName()
    {
        return $this->groupName;
    }

    /**
     * Set groupDesc
     *
     * @param string $groupDesc
     *
     * @return Groups
     */
    public function setGroupDesc($groupDesc)
    {
        $this->groupDesc = $groupDesc;

        return $this;
    }

    /**
     * Get groupDesc
     *
     * @return string
     */
    public function getGroupDesc()
    {
        return $this->groupDesc;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Groups
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set accessLevel
     *
     * @param string $accessLevel
     *
     * @return Groups
     */
    public function setAccessLevel($accessLevel)
    {
        $this->accessLevel = $accessLevel;
    
        return $this;
    }

    /**
     * Get accessLevel
     *
     * @return string
     */
    public function getAccessLevel()
    {
        return $this->accessLevel;
    }

    /**
     * Set allowRead
     *
     * @param integer $allowRead
     *
     * @return Groups
     */
    public function setAllowRead($allowRead)
    {
        $this->allowRead = $allowRead;
    
        return $this;
    }

    /**
     * Get allowRead
     *
     * @return integer
     */
    public function getAllowRead()
    {
        return $this->allowRead;
    }

    /**
     * Set allowWrite
     *
     * @param integer $allowWrite
     *
     * @return Groups
     */
    public function setAllowWrite($allowWrite)
    {
        $this->allowWrite = $allowWrite;
    
        return $this;
    }

    /**
     * Get allowWrite
     *
     * @return integer
     */
    public function getAllowWrite()
    {
        return $this->allowWrite;
    }
}
