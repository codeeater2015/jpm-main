<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * MergedProfile
 *
 * @ORM\Table(name="tbl_merged_profile")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MergedProfileRepository")
 * @UniqueEntity(fields={"voterName"},message="This name already exist.",errorPath="voterName")
 */
class MergedProfile
{
    /**
     * @var int
     *
     * @ORM\Column(name="profile_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $profileId;

     /**
     * @var string
     *
     * @ORM\Column(name="category", type="string", length=30)
     * @Assert\NotBlank()
     */
    private $category;

    /**
     * @var string
     *
     * @ORM\Column(name="profile_name", type="string", length=256)
     * @Assert\NotBlank()
     */
    private $profileName;

     /**
     * @var string
     *
     * @ORM\Column(name="province_code", type="string", length=15)
     * @Assert\NotBlank()
     */
    private $provinceCode;

    /**
     * @var string
     *
     * @ORM\Column(name="municipality_no", type="string", length=15)
     * @Assert\NotBlank()
     */
    private $municipalityNo;

    /**
     * @var string
     *
     * @ORM\Column(name="brgy_no", type="string", length=15)
     * @Assert\NotBlank()
     */
    private $brgyNo;

    /**
     * @var string
     *
     * @ORM\Column(name="precinct_no", type="string", length=30)
     */
    private $precinctNo;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=256)
     */
    private $address;

     /**
     * @var string
     *
     * @ORM\Column(name="cellphone_no", type="string", length=30)
     */
    private $cellphoneNo;

    /**
     * @var datetime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Assert\NotBlank()
     */
    private $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="created_by", type="string", length=150)
     * @Assert\NotBlank()
     */
    private $createdBy;

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
     * Get profileId
     *
     * @return integer
     */
    public function getProfileId()
    {
        return $this->profileId;
    }

    /**
     * Set category
     *
     * @param string $category
     *
     * @return MergedProfile
     */
    public function setCategory($category)
    {
        $this->category = $category;
    
        return $this;
    }

    /**
     * Get category
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set profileName
     *
     * @param string $profileName
     *
     * @return MergedProfile
     */
    public function setProfileName($profileName)
    {
        $this->profileName = $profileName;
    
        return $this;
    }

    /**
     * Get profileName
     *
     * @return string
     */
    public function getProfileName()
    {
        return $this->profileName;
    }

    /**
     * Set provinceCode
     *
     * @param string $provinceCode
     *
     * @return MergedProfile
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
     * Set municipalityNo
     *
     * @param string $municipalityNo
     *
     * @return MergedProfile
     */
    public function setMunicipalityNo($municipalityNo)
    {
        $this->municipalityNo = $municipalityNo;
    
        return $this;
    }

    /**
     * Get municipalityNo
     *
     * @return string
     */
    public function getMunicipalityNo()
    {
        return $this->municipalityNo;
    }

    /**
     * Set brgyNo
     *
     * @param string $brgyNo
     *
     * @return MergedProfile
     */
    public function setBrgyNo($brgyNo)
    {
        $this->brgyNo = $brgyNo;
    
        return $this;
    }

    /**
     * Get brgyNo
     *
     * @return string
     */
    public function getBrgyNo()
    {
        return $this->brgyNo;
    }

    /**
     * Set precinctNo
     *
     * @param string $precinctNo
     *
     * @return MergedProfile
     */
    public function setPrecinctNo($precinctNo)
    {
        $this->precinctNo = $precinctNo;
    
        return $this;
    }

    /**
     * Get precinctNo
     *
     * @return string
     */
    public function getPrecinctNo()
    {
        return $this->precinctNo;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return MergedProfile
     */
    public function setAddress($address)
    {
        $this->address = $address;
    
        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set cellphoneNo
     *
     * @param string $cellphoneNo
     *
     * @return MergedProfile
     */
    public function setCellphoneNo($cellphoneNo)
    {
        $this->cellphoneNo = $cellphoneNo;
    
        return $this;
    }

    /**
     * Get cellphoneNo
     *
     * @return string
     */
    public function getCellphoneNo()
    {
        return $this->cellphoneNo;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return MergedProfile
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set createdBy
     *
     * @param string $createdBy
     *
     * @return MergedProfile
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
    
        return $this;
    }

    /**
     * Get createdBy
     *
     * @return string
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set remarks
     *
     * @param string $remarks
     *
     * @return MergedProfile
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
     * @return MergedProfile
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
