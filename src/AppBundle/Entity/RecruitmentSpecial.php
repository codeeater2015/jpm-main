<?php

namespace AppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * RecruitmentSpecial
 *
 * @ORM\Table(name="tbl_recruitment_special")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RecruitmentSpecialRepository")
 * @UniqueEntity(fields={"proVoterId"},message="This voter already exists.", errorPath="proVoterId")
 */

class RecruitmentSpecial
{
    /**
     * @var int
     *
     * @ORM\Column(name="rec_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $recId;

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
     * @ORM\Column(name="node_level", type="integer")
     * @Assert\NotBlank()
     */
    private $nodeLevel;

     /**
     * @var int
     *
     * @ORM\Column(name="parent_node", type="integer")
     * @Assert\NotBlank()
     */
    private $parentNode;
    
    /**
     * @var datetime
     *
     * @ORM\Column(name="created_at", type="datetime")
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
     * @ORM\Column(name="status", type="string", length=3)
     */

    private $status;

    /**
     * Get recId
     *
     * @return integer
     */
    public function getRecId()
    {
        return $this->recId;
    }

    /**
     * Set proId
     *
     * @param integer $proId
     *
     * @return RecruitmentSpecial
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
     * @return RecruitmentSpecial
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
     * Set nodeLevel
     *
     * @param integer $nodeLevel
     *
     * @return RecruitmentSpecial
     */
    public function setNodeLevel($nodeLevel)
    {
        $this->nodeLevel = $nodeLevel;

        return $this;
    }

    /**
     * Get nodeLevel
     *
     * @return integer
     */
    public function getNodeLevel()
    {
        return $this->nodeLevel;
    }

    /**
     * Set parentNode
     *
     * @param integer $parentNode
     *
     * @return RecruitmentSpecial
     */
    public function setParentNode($parentNode)
    {
        $this->parentNode = $parentNode;

        return $this;
    }

    /**
     * Get parentNode
     *
     * @return integer
     */
    public function getParentNode()
    {
        return $this->parentNode;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return RecruitmentSpecial
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
     * @return RecruitmentSpecial
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
     * @return RecruitmentSpecial
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
     * Set voterId
     *
     * @param integer $voterId
     *
     * @return RecruitmentSpecial
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
}
