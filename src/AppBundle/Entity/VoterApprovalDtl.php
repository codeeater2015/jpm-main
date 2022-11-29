<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * VoterApprovalDtl
 *
 * @ORM\Table(name="tbl_voter_approval_dtl")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\VoterApprovalDtlRepository")
 */
class VoterApprovalDtl
{
    
    /**
     * @var int
     *
     * @ORM\Column(name="appr_dtl_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $apprDtlId;

     /**
     * @var int
     *
     * @ORM\Column(name="appr_id", type="integer")
     * @Assert\NotBlank()
     */
    private $apprId;

    /**
     * @var int
     *
     * @ORM\Column(name="hist_id", type="integer")
     * @Assert\NotBlank()
     */
    private $histId;

    /**
     * @var int
     *
     * @ORM\Column(name="voter_id", type="integer")
     * @Assert\NotBlank()
     */
    private $voterId;

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
     * @Assert\NotBlank()
     */
    private $status;

    /**
     * Get apprDtlId
     *
     * @return integer
     */
    public function getApprDtlId()
    {
        return $this->apprDtlId;
    }

    /**
     * Set apprId
     *
     * @param integer $apprId
     *
     * @return VoterApprovalDtl
     */
    public function setApprId($apprId)
    {
        $this->apprId = $apprId;

        return $this;
    }

    /**
     * Get apprId
     *
     * @return integer
     */
    public function getApprId()
    {
        return $this->apprId;
    }

    /**
     * Set histId
     *
     * @param integer $histId
     *
     * @return VoterApprovalDtl
     */
    public function setHistId($histId)
    {
        $this->histId = $histId;

        return $this;
    }

    /**
     * Get histId
     *
     * @return integer
     */
    public function getHistId()
    {
        return $this->histId;
    }

    /**
     * Set voterId
     *
     * @param integer $voterId
     *
     * @return VoterApprovalDtl
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return VoterApprovalDtl
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
     * @return VoterApprovalDtl
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
     * @return VoterApprovalDtl
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
     * @return VoterApprovalDtl
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
