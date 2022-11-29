<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * SpecialOptHeader
 *
 * @ORM\Table(name="tbl_special_opt_header")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SpecialOptHeaderRepository")
 * @UniqueEntity(fields={"hdrId"},message="This id has already been created",errorPath="hdrId")
 */
class SpecialOptHeader
{
    /**
     * @var int
     *
     * @ORM\Column(name="hdr_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $hdrId;

    /**
     * @var int
     *
     * @ORM\Column(name="elect_id", type="integer")
     * @Assert\NotBlank()
     */

    private $electId;

    /**
     * @var int
     *
     * @ORM\Column(name="pro_id", type="integer")
     * @Assert\NotBlank()
     */

    private $proId;

    /**
     * @var int
     *
     * @ORM\Column(name="pro_voter_id", type="integer")
     * @Assert\NotBlank()
     */

    private $proVoterId;

    /**
     * @var int
     *
     * @ORM\Column(name="voter_id", type="integer")
     * @Assert\NotBlank()
     */

    private $voterId;

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
     */

    private $brgyNo;

     /**
     * @var string
     *
     * @ORM\Column(name="opt_type", type="string", length=30)
     * @Assert\NotBlank()
     */

    private $optType;

    /**
     * @var string
     *
     * @ORM\Column(name="created_by", type="string", length=150)
     * @Assert\NotBlank()
     */

    private $createdBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Assert\NotBlank() 
     */
    private $createdAt;

     /**
     * @var string
     *
     * @ORM\Column(name="updated_by", type="string", length=150)
     */

    private $updatedBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="remarks", type="string", length=255)
     */

    private $remarks;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=3)
     * @Assert\NotBlank()
     */

    private $status;

    /**
     * Get hdrId
     *
     * @return integer
     */
    public function getHdrId()
    {
        return $this->hdrId;
    }

    /**
     * Set electId
     *
     * @param integer $electId
     *
     * @return SpecialOptHeader
     */
    public function setElectId($electId)
    {
        $this->electId = $electId;

        return $this;
    }

    /**
     * Get electId
     *
     * @return integer
     */
    public function getElectId()
    {
        return $this->electId;
    }

    /**
     * Set proId
     *
     * @param integer $proId
     *
     * @return SpecialOptHeader
     */
    public function setProId($proId)
    {
        $this->proId = $proId;

        return $this;
    }

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
     * Set proVoterId
     *
     * @param integer $proVoterId
     *
     * @return SpecialOptHeader
     */
    public function setProVoterId($proVoterId)
    {
        $this->proVoterId = $proVoterId;

        return $this;
    }

    /**
     * Get proVoterId
     *
     * @return integer
     */
    public function getProVoterId()
    {
        return $this->proVoterId;
    }

    /**
     * Set voterId
     *
     * @param integer $voterId
     *
     * @return SpecialOptHeader
     */
    public function setVoterId($voterId)
    {
        $this->voterId = $voterId;

        return $this;
    }

    /**
     * Get voterId
     *
     * @return integer
     */
    public function getVoterId()
    {
        return $this->voterId;
    }

    /**
     * Set provinceCode
     *
     * @param string $provinceCode
     *
     * @return SpecialOptHeader
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
     * @return SpecialOptHeader
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
     * @return SpecialOptHeader
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
     * Set createdBy
     *
     * @param string $createdBy
     *
     * @return SpecialOptHeader
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return SpecialOptHeader
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
     * Set updatedBy
     *
     * @param string $updatedBy
     *
     * @return SpecialOptHeader
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return string
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return SpecialOptHeader
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set remarks
     *
     * @param string $remarks
     *
     * @return SpecialOptHeader
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
     * @return SpecialOptHeader
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

    /**
     * Set optType
     *
     * @param string $optType
     *
     * @return SpecialOptHeader
     */
    public function setOptType($optType)
    {
        $this->optType = $optType;

        return $this;
    }

    /**
     * Get optType
     *
     * @return string
     */
    public function getOptType()
    {
        return $this->optType;
    }
}
