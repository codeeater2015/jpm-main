<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * VoterAssistanceSummary
 *
 * @ORM\Table(name="tbl_assistance_summary")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\VoterAssistanceSummaryRepository")
 */
class VoterAssistanceSummary
{
    /**
     * @var int
     *
     * @ORM\Column(name="sum_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $sumId;

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
     * @var string
     *
     * @ORM\Column(name="category", type="string", length=50)
     * @Assert\NotBlank()
     */
    private $category;

    /**
     * @var float
     *
     * @ORM\Column(name="total_amount", type="float", scale=2)
     */
    private $totalAmount;
    
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
     * Get sumId
     *
     * @return integer
     */
    public function getSumId()
    {
        return $this->sumId;
    }

    /**
     * Set provinceCode
     *
     * @param string $provinceCode
     *
     * @return VoterAssistanceSummary
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
     * @return VoterAssistanceSummary
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
     * @return VoterAssistanceSummary
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
     * Set category
     *
     * @param string $category
     *
     * @return VoterAssistanceSummary
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
     * Set totalAmount
     *
     * @param float $totalAmount
     *
     * @return VoterAssistanceSummary
     */
    public function setTotalAmount($totalAmount)
    {
        $this->totalAmount = $totalAmount;
    
        return $this;
    }

    /**
     * Get totalAmount
     *
     * @return float
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return VoterAssistanceSummary
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
     * @return VoterAssistanceSummary
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
}
