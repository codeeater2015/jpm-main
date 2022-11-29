<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Groups
 *
 * @ORM\Table(name="tbl_menu_item")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MenuItemRepository")
 * @UniqueEntity(fields={"menu_label"},message="This menu item has already been created",errorPath="menu_label")
 */
class MenuItem
{
    /**
     * @var integer
     *
     * @ORM\Column(name="menu_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $menuId;

    /**
     * @var integer
     *
     * @ORM\Column(name="group_id", type="integer")
     */
    private $groupId;

    
    /**
     * @var integer
     *
     * @ORM\Column(name="parent_id", type="integer")
     */
    private $parentId;


     /**
     * @var string
     *
     * @ORM\Column(name="menu_label", type="string", length=255)
     * @Assert\NotBlank()
     */

    private $menuLabel;

     /**
     * @var string
     *
     * @ORM\Column(name="menu_link", type="string", length=255)
     * @Assert\NotBlank()
     */

    private $menuLink;

     /**
     * @var string
     *
     * @ORM\Column(name="menu_icon", type="string", length=255)
     * @Assert\NotBlank()
     */

    private $menuIcon;

     /**
     * @var integer
     *
     * @ORM\Column(name="menu_order", type="integer")
     */

    private $menuOrder;

     /**
     * @var string
     *
     * @ORM\Column(name="menu_target", type="string", length=255)
     */

    private $menuTarget;

    /**
     * @var string
     *
     * @ORM\Column(name="menu_type", type="string", length=15)
     */

    private $menuType;

    /**
     * Get menuId
     *
     * @return integer
     */
    public function getMenuId()
    {
        return $this->menuId;
    }

    /**
     * Set groupId
     *
     * @param integer $groupId
     *
     * @return MenuItem
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;

        return $this;
    }

    /**
     * Get groupId
     *
     * @return integer
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * Set parentId
     *
     * @param integer $parentId
     *
     * @return MenuItem
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Get parentId
     *
     * @return integer
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set menuLabel
     *
     * @param string $menuLabel
     *
     * @return MenuItem
     */
    public function setMenuLabel($menuLabel)
    {
        $this->menuLabel = $menuLabel;

        return $this;
    }

    /**
     * Get menuLabel
     *
     * @return string
     */
    public function getMenuLabel()
    {
        return $this->menuLabel;
    }

    /**
     * Set menuLink
     *
     * @param string $menuLink
     *
     * @return MenuItem
     */
    public function setMenuLink($menuLink)
    {
        $this->menuLink = $menuLink;

        return $this;
    }

    /**
     * Get menuLink
     *
     * @return string
     */
    public function getMenuLink()
    {
        return $this->menuLink;
    }

    /**
     * Set menuIcon
     *
     * @param string $menuIcon
     *
     * @return MenuItem
     */
    public function setMenuIcon($menuIcon)
    {
        $this->menuIcon = $menuIcon;

        return $this;
    }

    /**
     * Get menuIcon
     *
     * @return string
     */
    public function getMenuIcon()
    {
        return $this->menuIcon;
    }

    /**
     * Set menuOrder
     *
     * @param integer $menuOrder
     *
     * @return MenuItem
     */
    public function setMenuOrder($menuOrder)
    {
        $this->menuOrder = $menuOrder;

        return $this;
    }

    /**
     * Get menuOrder
     *
     * @return integer
     */
    public function getMenuOrder()
    {
        return $this->menuOrder;
    }

    /**
     * Set menuTarget
     *
     * @param string $menuTarget
     *
     * @return MenuItem
     */
    public function setMenuTarget($menuTarget)
    {
        $this->menuTarget = $menuTarget;

        return $this;
    }

    /**
     * Get menuTarget
     *
     * @return string
     */
    public function getMenuTarget()
    {
        return $this->menuTarget;
    }

    /**
     * Set menuType
     *
     * @param string $menuType
     *
     * @return MenuItem
     */
    public function setMenuType($menuType)
    {
        $this->menuType = $menuType;

        return $this;
    }

    /**
     * Get menuType
     *
     * @return string
     */
    public function getMenuType()
    {
        return $this->menuType;
    }
}
