<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * VoterAssistance
 *
 * @ORM\Table(name="tbl_assistance")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\VoterAssistanceRepository")
 * @UniqueEntity(fields={"astId"},message="This name already exist.", errorPath="astId")
 */
class VoterAssistance
{
    /**
     * @var int
     *
     * @ORM\Column(name="ast_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $astId;

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
     * @ORM\Column(name="description", type="string", length=150)
     * @Assert\NotBlank()
     */
    private $description;

     /**
     * @var string
     *
     * @ORM\Column(name="category", type="string", length=150)
     * @Assert\NotBlank()
     */
    private $category;

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
     * @ORM\Column(name="municipality_no", type="string", length=30)
     * @Assert\NotBlank()
     */
    private $municipalityNo;

    /**
     * @var string
     *
     * @ORM\Column(name="brgy_no", type="string", length=30)
     * @Assert\NotBlank()
     */
    private $brgyNo;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float", scale=2)
     * @Assert\Range(min = 1, max=999999 )
     */
    private $amount;

     /**
     * @var string
     *
     * @ORM\Column(name="remarks", type="string", length=150)
     */
    private $remarks;

    
    /**
     * @var datetime
     *
     * @ORM\Column(name="issued_at", type="datetime")
     * @Assert\NotBlank()
     */
    private $issuedAt;

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
     * @ORM\Column(name="created_by", type="string")
     * @Assert\NotBlank()
     */
    private $createdBy;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=3)
     */
    private $status;

    /**
     * Get astId
     *
     * @return integer
     */
    public function getAstId()
    {
        return $this->astId;
    }

    /**
     * Set voterId
     *
     * @param integer $voterId
     *
     * @return VoterAssistance
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
     * Set description
     *
     * @param string $description
     *
     * @return VoterAssistance
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set category
     *
     * @param string $category
     *
     * @return VoterAssistance
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
     * Set amount
     *
     * @param float $amount
     *
     * @return VoterAssistance
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    
        return $this;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set remarks
     *
     * @param string $remarks
     *
     * @return VoterAssistance
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return VoterAssistance
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
     * @return VoterAssistance
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
     * Set status
     *
     * @param string $status
     *
     * @return VoterAssistance
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
     * Set provinceCode
     *
     * @param string $provinceCode
     *
     * @return VoterAssistance
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
     * @return VoterAssistance
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
     * @return VoterAssistance
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
     * Set issuedAt
     *
     * @param \DateTime $issuedAt
     *
     * @return VoterAssistance
     */
    public function setIssuedAt($issuedAt)
    {
        $this->issuedAt = $issuedAt;
    
        return $this;
    }

    /**
     * Get issuedAt
     *
     * @return \DateTime
     */
    public function getIssuedAt()
    {
        return $this->issuedAt;
    }
}
