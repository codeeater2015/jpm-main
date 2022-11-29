<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * IdRequestDetail
 *
 * @ORM\Table(name="tbl_id_request_detail")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\IdRequestDetailRepository")
 * @UniqueEntity(fields={"dtlId"},message="This id has already been created",errorPath="dtlId")
 * @UniqueEntity(fields={"hdrId","proVoterId"},message="This member was already on the list...",errorPath="voterId")
 */
class IdRequestDetail
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
     * @ORM\Column(name="released_by", type="string", length=150)
     */

    private $releasedBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="released_at", type="datetime")
     */
    private $releasedAt;

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
     * @return IdRequestDetail
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
     * @return IdRequestDetail
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
     * @return IdRequestDetail
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
     * @return IdRequestDetail
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
     * @return IdRequestDetail
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
     * Set status
     *
     * @param string $status
     *
     * @return IdRequestDetail
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
     * Set releasedBy
     *
     * @param string $releasedBy
     *
     * @return IdRequestDetail
     */
    public function setReleasedBy($releasedBy)
    {
        $this->releasedBy = $releasedBy;

        return $this;
    }

    /**
     * Get releasedBy
     *
     * @return string
     */
    public function getReleasedBy()
    {
        return $this->releasedBy;
    }

    /**
     * Set releasedAt
     *
     * @param \DateTime $releasedAt
     *
     * @return IdRequestDetail
     */
    public function setReleasedAt($releasedAt)
    {
        $this->releasedAt = $releasedAt;

        return $this;
    }

    /**
     * Get releasedAt
     *
     * @return \DateTime
     */
    public function getReleasedAt()
    {
        return $this->releasedAt;
    }
}
