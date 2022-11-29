<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Permission
 *
 * @ORM\Table(name="tbl_permission")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PermissionRepository")
 */
class Permission
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
     * @ORM\Column(name="permission_name", type="string", length=255)
     */
    private $permissionName;

    /**
     * @var string
     *
     * @ORM\Column(name="permission_desc", type="text", nullable=true)
     */
    private $permissionDesc;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="Module")
     * @ORM\JoinColumn(name="module_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $moduleId;



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
     * Set permissionName
     *
     * @param string $permissionName
     *
     * @return Permission
     */
    public function setPermissionName($permissionName)
    {
        $this->permissionName = $permissionName;

        return $this;
    }

    /**
     * Get permissionName
     *
     * @return string
     */
    public function getPermissionName()
    {
        return $this->permissionName;
    }

    /**
     * Set permissionDesc
     *
     * @param $permissionDesc
     *
     * @return $this
     */
    public function setPermissionDesc($permissionDesc)
    {
        $this->permissionDesc = $permissionDesc;

        return $this;
    }

    /**
     * Get permissionDesc
     *
     * @return string
     */
    public function getPermissionDesc()
    {
        return $this->permissionDesc;
    }


    /**
     * Set moduleId
     *
     * @param \AppBundle\Entity\Module $moduleId
     *
     * @return Permission
     */
    public function setModuleId(\AppBundle\Entity\Module $moduleId = null)
    {
        $this->moduleId = $moduleId;

        return $this;
    }

    /**
     * Get moduleId
     *
     * @return \AppBundle\Entity\Module
     */
    public function getModuleId()
    {
        return $this->moduleId;
    }
}
