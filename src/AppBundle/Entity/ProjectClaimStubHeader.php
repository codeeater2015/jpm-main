<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * ProjectClaimStubHeader
 *
 * @ORM\Table(name="tbl_project_claim_stub_header")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProjectClaimStubHeaderRepository")
 * @UniqueEntity(fields={"batchId"},message="This value is already in use.", errorPath="batchId")
 */
class ProjectClaimStubHeader
{
    /**
     * @var int
     *
     * @ORM\Column(name="batch_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $batchId;

    /**
     * @var integer
     *
     * @ORM\Column(name="pro_id", type="integer")
     * @Assert\NotBlank()
     */
    private $proId;

     /**
     * @var string
     *
     * @ORM\Column(name="template_desc", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $templateDesc;

     /**
     * @var string
     *
     * @ORM\Column(name="municipality_name", type="string", length=150)
     */
    private $municipalityName;

     /**
     * @var string
     *
     * @ORM\Column(name="barangay_name", type="string", length=150)
     */
    private $barangayName;

    /**
     * @var string
     *
     * @ORM\Column(name="municipality_no", type="string", length=15)
     */
    private $municipalityNo;

    /**
     * @var string
     *
     * @ORM\Column(name="brgy_no", type="string", length=15)
     */
    private $brgyNo;

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
     * Get batchId
     *
     * @return integer
     */
    public function getBatchId()
    {
        return $this->batchId;
    }

    /**
     * Set proId
     *
     * @param integer $proId
     *
     * @return ProjectClaimStubHeader
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
     * Set municipalityName
     *
     * @param string $municipalityName
     *
     * @return ProjectClaimStubHeader
     */
    public function setMunicipalityName($municipalityName)
    {
        $this->municipalityName = $municipalityName;

        return $this;
    }

    /**
     * Get municipalityName
     *
     * @return string
     */
    public function getMunicipalityName()
    {
        return $this->municipalityName;
    }

    /**
     * Set barangayName
     *
     * @param string $barangayName
     *
     * @return ProjectClaimStubHeader
     */
    public function setBarangayName($barangayName)
    {
        $this->barangayName = $barangayName;

        return $this;
    }

    /**
     * Get barangayName
     *
     * @return string
     */
    public function getBarangayName()
    {
        return $this->barangayName;
    }

    /**
     * Set municipalityNo
     *
     * @param string $municipalityNo
     *
     * @return ProjectClaimStubHeader
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
     * @return ProjectClaimStubHeader
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ProjectClaimStubHeader
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
     * @return ProjectClaimStubHeader
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
     * @return ProjectClaimStubHeader
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
     * @return ProjectClaimStubHeader
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
     * Set templateDesc
     *
     * @param string $templateDesc
     *
     * @return ProjectClaimStubHeader
     */
    public function setTemplateDesc($templateDesc)
    {
        $this->templateDesc = $templateDesc;

        return $this;
    }

    /**
     * Get templateDesc
     *
     * @return string
     */
    public function getTemplateDesc()
    {
        return $this->templateDesc;
    }
}
