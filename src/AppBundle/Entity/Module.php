<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups as Group;

/**
 * Module
 *
 * @ORM\Table(name="tbl_module")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ModuleRepository")
 * @UniqueEntity("moduleName", message="module.module_name_used")
 * @UniqueEntity("moduleLabel", message="module.module_label_used")
 * @UniqueEntity("moduleRoute", message="module.module_route_used")
 */

class Module
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Group({"fields"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="module_name", type="string", length=255, unique=true)
     * @Assert\NotBlank()
    * @Group({"fields"})
     */
    private $moduleName;

    /**
     * @var string
     *
     * @ORM\Column(name="module_label", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Group({"fields"})
     */
    private $moduleLabel;

    /**
     * @var string
     *
     * @ORM\Column(name="module_desc", type="text", nullable=true)
     * @Assert\Length(min=5)
     * @Group({"fields"})
     */
    private $moduleDesc;

    /**
     * @var string
     *
     * @ORM\Column(name="module_icon", type="string", length=255)
     * @Assert\NotBlank()
     * @Group({"fields"})
     */
    private $moduleIcon;

    /**
     * @var string
     *
     * @ORM\Column(name="module_route", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Group({"fields"})
     */
    private $moduleRoute;

    /**
     * @var int
     *
     * @ORM\Column(name="sort_order", type="integer")
     * @Assert\NotBlank()
     * @Group({"fields"})
     */
    private $sortOrder;

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
     * Set moduleName
     *
     * @param string $moduleName
     *
     * @return Module
     */
    public function setModuleName($moduleName)
    {
        $this->moduleName = $moduleName;

        return $this;
    }

    /**
     * Get moduleName
     *
     * @return string
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * Set moduleLabel
     *
     * @param string $moduleLabel
     *
     * @return Module
     */
    public function setModuleLabel($moduleLabel)
    {
        $this->moduleLabel = $moduleLabel;

        return $this;
    }

    /**
     * Get moduleLabel
     *
     * @return string
     */
    public function getModuleLabel()
    {
        return $this->moduleLabel;
    }

    /**
     * Set moduleIcon
     *
     * @param string $moduleIcon
     *
     * @return Module
     */
    public function setModuleIcon($moduleIcon)
    {
        $this->moduleIcon = $moduleIcon;

        return $this;
    }

    /**
     * Get moduleIcon
     *
     * @return string
     */
    public function getModuleIcon()
    {
        return $this->moduleIcon;
    }

    /**
     * Set moduleRoute
     *
     * @param string $moduleRoute
     *
     * @return Module
     */
    public function setModuleRoute($moduleRoute)
    {
        $this->moduleRoute = $moduleRoute;

        return $this;
    }

    /**
     * Get moduleRoute
     *
     * @return string
     */
    public function getModuleRoute()
    {
        return $this->moduleRoute;
    }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     *
     * @return Module
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * Get sortOrder
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * Set moduleDesc
     *
     * @param string $moduleDesc
     *
     * @return Module
     */
    public function setModuleDesc($moduleDesc)
    {
        $this->moduleDesc = $moduleDesc;

        return $this;
    }

    /**
     * Get moduleDesc
     *
     * @return string
     */
    public function getModuleDesc()
    {
        return $this->moduleDesc;
    }


}
