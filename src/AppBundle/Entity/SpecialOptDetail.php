<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * SpecialOptDetail
 *
 * @ORM\Table(name="tbl_special_opt_detail")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SpecialOptDetailRepository")
 * @UniqueEntity(fields={"hdrId","dtlId"},message="This id has already been created",errorPath="dtlId")
 */
class SpecialOptDetail
{
   /**
     * @var int
     *
     * @ORM\Column(name="dtl_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $dtlId;

    /**
     * @var int
     *
     * @ORM\Column(name="hdr_id", type="integer")
     * @Assert\NotBlank()
     */

    private $hdrId;

    /**
     * @var int
     *
     * @ORM\Column(name="voter_id", type="integer")
     * @Assert\NotBlank()
     */

    private $voterId;

    /**
     * @var int
     *
     * @ORM\Column(name="pro_voter_id", type="integer")
     * @Assert\NotBlank()
     */

    private $proVoterId;

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
     * Get dtlId
     *
     * @return integer
     */
    public function getDtlId()
    {
        return $this->dtlId;
    }

    /**
     * Set hdrId
     *
     * @param integer $hdrId
     *
     * @return SpecialOptDetail
     */
    public function setHdrId($hdrId)
    {
        $this->hdrId = $hdrId;

        return $this;
    }

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
     * Set voterId
     *
     * @param integer $voterId
     *
     * @return SpecialOptDetail
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
     * Set proVoterId
     *
     * @param integer $proVoterId
     *
     * @return SpecialOptDetail
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
     * Set createdBy
     *
     * @param string $createdBy
     *
     * @return SpecialOptDetail
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
     * @return SpecialOptDetail
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
     * Set remarks
     *
     * @param string $remarks
     *
     * @return SpecialOptDetail
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
     * @return SpecialOptDetail
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
