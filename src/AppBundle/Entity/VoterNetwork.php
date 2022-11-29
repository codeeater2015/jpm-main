<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * VoterNetwork
 *
 * @ORM\Table(name="tbl_voter_network")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\VoterNetworkRepository")
 * @UniqueEntity(fields={"voterId","proId"},message="This voter already belongs to a group.",errorPath="voterId")
 */
class VoterNetwork
{
    /**
     * @var int
     *
     * @ORM\Column(name="node_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $nodeId;

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
     * @ORM\Column(name="parent_id", type="integer")
     * @Assert\NotBlank()
     */
    private $parentId;

     /**
     * @var int
     *
     * @ORM\Column(name="pro_id", type="integer")
     */
    private $proId;

    /**
     * @var int
     *
     * @ORM\Column(name="elect_id", type="integer")
     */
    private $electId;

    /**
     * @var int
     *
     * @ORM\Column(name="node_level", type="integer")
     * @Assert\NotBlank()
     */
    private $nodeLevel;

    /**
     * @var string
     *
     * @ORM\Column(name="province_code", type="string", length=256)
     */
    private $provinceCode;

    /**
     * @var string
     *
     * @ORM\Column(name="municipality_no", type="string", length=256)
     * @Assert\NotBlank()
     */
    private $municipalityNo;
    
    /**
     * @var string
     *
     * @ORM\Column(name="brgy_no", type="string", length=256)
     * @Assert\NotBlank()
     */
    private $brgyNo;

     /**
     * @var string
     *
     * @ORM\Column(name="precinct_no", type="string", length=30)
     * @Assert\NotBlank()
     */
    private $precinctNo;

    /**
     * @var string
     *
     * @ORM\Column(name="node_label", type="string", length=30)
     * @Assert\NotBlank()
     */
    private $nodeLabel;

    /**
     * @var string
     *
     * @ORM\Column(name="node_order", type="string", length=30)
     * @Assert\NotBlank()
     */
    private $nodeOrder;

    /**
     * @var string
     *
     * @ORM\Column(name="voter_group", type="string", length=30)
     */
    private $voterGroup;

    /**
     * @var string
     *
     * @ORM\Column(name="cellphone_no", type="string", length=30)
     */
    private $cellphoneNo;

     /**
     * @var string
     *
     * @ORM\Column(name="email_address", type="string", length=256)
     */
    private $emailAddress;

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
     * Get nodeId
     *
     * @return integer
     */
    public function getNodeId()
    {
        return $this->nodeId;
    }

    /**
     * Set voterId
     *
     * @param integer $voterId
     *
     * @return VoterNetwork
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
     * Set parentId
     *
     * @param integer $parentId
     *
     * @return VoterNetwork
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Get parentId
     *
     * @return integer
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set municipalityNo
     *
     * @param string $municipalityNo
     *
     * @return VoterNetwork
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
     * @return VoterNetwork
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
     * Set nodeLabel
     *
     * @param string $nodeLabel
     *
     * @return VoterNetwork
     */
    public function setNodeLabel($nodeLabel)
    {
        $this->nodeLabel = $nodeLabel;

        return $this;
    }

    /**
     * Get nodeLabel
     *
     * @return string
     */
    public function getNodeLabel()
    {
        return $this->nodeLabel;
    }

    /**
     * Set nodeOrder
     *
     * @param string $nodeOrder
     *
     * @return VoterNetwork
     */
    public function setNodeOrder($nodeOrder)
    {
        $this->nodeOrder = $nodeOrder;

        return $this;
    }

    /**
     * Get nodeOrder
     *
     * @return string
     */
    public function getNodeOrder()
    {
        return $this->nodeOrder;
    }

    /**
     * Set nodeLevel
     *
     * @param integer $nodeLevel
     *
     * @return VoterNetwork
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
     * Set precinctNo
     *
     * @param string $precinctNo
     *
     * @return VoterNetwork
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return VoterNetwork
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
     * @return VoterNetwork
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
     * Set provinceCode
     *
     * @param string $provinceCode
     *
     * @return VoterNetwork
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
     * Set proId
     *
     * @param integer $proId
     *
     * @return VoterNetwork
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
     * Set electId
     *
     * @param integer $electId
     *
     * @return VoterNetwork
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
     * Set voterGroup
     *
     * @param string $voterGroup
     *
     * @return VoterNetwork
     */
    public function setVoterGroup($voterGroup)
    {
        $this->voterGroup = $voterGroup;
    
        return $this;
    }

    /**
     * Get voterGroup
     *
     * @return string
     */
    public function getVoterGroup()
    {
        return $this->voterGroup;
    }

    /**
     * Set cellphoneNo
     *
     * @param string $cellphoneNo
     *
     * @return VoterNetwork
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
     * Set emailAddress
     *
     * @param string $emailAddress
     *
     * @return VoterNetwork
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    
        return $this;
    }

    /**
     * Get emailAddress
     *
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }
}
