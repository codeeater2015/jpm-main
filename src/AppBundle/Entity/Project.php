<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Project
 *
 * @ORM\Table(name="tbl_project")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProjectRepository")
 * @UniqueEntity(fields={"proName"},message="This project already exists.", errorPath="proName")
 */
class Project
{
    /**
     * @var int
     *
     * @ORM\Column(name="pro_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $proId;

    /**
     * @var string
     *
     * @ORM\Column(name="pro_name", type="string", length=150)
     * @Assert\NotBlank()
     */
    private $proName;

     /**
     * @var string
     *
     * @ORM\Column(name="pro_desc", type="string", length=256)
     */
    private $proDesc;

     /**
     * @var string
     *
     * @ORM\Column(name="province_code", type="string", length=30)
     * @Assert\NotBlank()
     */
    private $provinceCode;

    /**
     * @var string
     *
     * @ORM\Column(name="remarks", type="string", length=256)
     */
    private $remarks;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=3)
     */
    private $status;


    /**
     * Get proId
     *
     * @return integer
     */
    public function getProId()
    {
        return $this->proId;
    }

    /**
     * Set proName
     *
     * @param string $proName
     *
     * @return Project
     */
    public function setProName($proName)
    {
        $this->proName = $proName;
    
        return $this;
    }

    /**
     * Get proName
     *
     * @return string
     */
    public function getProName()
    {
        return $this->proName;
    }

    /**
     * Set proDesc
     *
     * @param string $proDesc
     *
     * @return Project
     */
    public function setProDesc($proDesc)
    {
        $this->proDesc = $proDesc;
    
        return $this;
    }

    /**
     * Get proDesc
     *
     * @return string
     */
    public function getProDesc()
    {
        return $this->proDesc;
    }

    /**
     * Set provinceCode
     *
     * @param string $provinceCode
     *
     * @return Project
     */
    public function setProvinceCode($provinceCode)
    {
        $this->provinceCode = $provinceCode;
    
        return $this;
    }

    /**
     * Get provinceCode
     *
     * @return string
     */
    public function getProvinceCode()
    {
        return $this->provinceCode;
    }

    /**
     * Set remarks
     *
     * @param string $remarks
     *
     * @return Project
     */
    public function setRemarks($remarks)
    {
        $this->remarks = $remarks;
    
        return $this;
    }

    /**
     * Get remarks
     *
     * @return string
     */
    public function getRemarks()
    {
        return $this->remarks;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Project
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
}
