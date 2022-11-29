<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * VoterHistory
 *
 * @ORM\Table(name="tbl_voter_history")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\VoterHistoryRepository")
 */
class VoterHistory
{
    /**
     * @var int
     *
     * @ORM\Column(name="hist_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
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
     * @var string
     *
     * @ORM\Column(name="voter_name", type="string", length=256)
     * @Assert\NotBlank()
     */
    private $voterName;

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
     * @ORM\Column(name="address", type="string", length=256)
     * @Assert\NotBlank()
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="precinct_no", type="string", length=30)
     */
    private $precinctNo;

    /**
     * @var string
     *
     * @ORM\Column(name="voter_no", type="string", length=30)
     */
    private $voterNo;

     /**
     * @var string
     *
     * @ORM\Column(name="cellphone_no", type="string", length=30)
     */
    private $cellphoneNo;

    /**
     * @var int
     *
     * @ORM\Column(name="voter_class", type="integer")
     */
    private $voterClass;

    /**
     * @var int
     *
     * @ORM\Column(name="voted_2017", type="integer")
     */
    private $voted2017;

    /**
     * @var int
     *
     * @ORM\Column(name="has_ast", type="integer")
     */
    private $hasAst;

     /**
     * @var int
     *
     * @ORM\Column(name="has_a", type="integer")
     */
    private $hasA;

    /**
     * @var int
     *
     * @ORM\Column(name="has_b", type="integer")
     */
    private $hasB;

    /**
     * @var int
     *
     * @ORM\Column(name="has_c", type="integer")
     */
    private $hasC;

     /**
     * @var int
     *
     * @ORM\Column(name="is_1", type="integer")
     */
    private $is1;

    /**
     * @var int
     *
     * @ORM\Column(name="is_2", type="integer")
     */
    private $is2;

    /**
     * @var int
     *
     * @ORM\Column(name="is_3", type="integer")
     */
    private $is3;

    /**
     * @var int
     *
     * @ORM\Column(name="is_4", type="integer")
     */
    private $is4;

    /**
     * @var int
     *
     * @ORM\Column(name="is_5", type="integer")
     */
    private $is5;

    /**
     * @var int
     *
     * @ORM\Column(name="is_6", type="integer")
     */
    private $is6;

    /**
     * @var int
     *
     * @ORM\Column(name="is_7", type="integer")
     */
    private $is7;

    /**
     * @var string
     *
     * @ORM\Column(name="voter_status", type="string", length=15)
     */
    private $voterStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="birthdate", type="string", length=30)
     */
    private $birthdate;

    /**
     * @var int
     *
     * @ORM\Column(name="on_network", type="integer")
     */
    private $onNetwork;
    
    /**
     * @var string
     *
     * @ORM\Column(name="category", type="string", length=150)
     */
    private $category;

    /**
     * @var string
     *
     * @ORM\Column(name="organization", type="string", length=55)
     */
    private $organization;

    /**
     * @var string
     *
     * @ORM\Column(name="position", type="string", length=55)
     */
    private $position;

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
     * @return VoterHistory
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
     * Set voterName
     *
     * @param string $voterName
     *
     * @return VoterHistory
     */
    public function setVoterName($voterName)
    {
        $this->voterName = $voterName;

        return $this;
    }

    /**
     * Get voterName
     *
     * @return string
     */
    public function getVoterName()
    {
        return $this->voterName;
    }

    /**
     * Set municipalityNo
     *
     * @param string $municipalityNo
     *
     * @return VoterHistory
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
     * @return VoterHistory
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
     * Set address
     *
     * @param string $address
     *
     * @return VoterHistory
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
     * Set precinctNo
     *
     * @param string $precinctNo
     *
     * @return VoterHistory
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
     * Set voterNo
     *
     * @param string $voterNo
     *
     * @return VoterHistory
     */
    public function setVoterNo($voterNo)
    {
        $this->voterNo = $voterNo;

        return $this;
    }

    /**
     * Get voterNo
     *
     * @return string
     */
    public function getVoterNo()
    {
        return $this->voterNo;
    }

    /**
     * Set cellphoneNo
     *
     * @param string $cellphoneNo
     *
     * @return VoterHistory
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
     * Set voterClass
     *
     * @param integer $voterClass
     *
     * @return VoterHistory
     */
    public function setVoterClass($voterClass)
    {
        $this->voterClass = $voterClass;

        return $this;
    }

    /**
     * Get voterClass
     *
     * @return integer
     */
    public function getVoterClass()
    {
        return $this->voterClass;
    }

    /**
     * Set voted2017
     *
     * @param integer $voted2017
     *
     * @return VoterHistory
     */
    public function setVoted2017($voted2017)
    {
        $this->voted2017 = $voted2017;

        return $this;
    }

    /**
     * Get voted2017
     *
     * @return integer
     */
    public function getVoted2017()
    {
        return $this->voted2017;
    }

    /**
     * Set hasAst
     *
     * @param integer $hasAst
     *
     * @return VoterHistory
     */
    public function setHasAst($hasAst)
    {
        $this->hasAst = $hasAst;

        return $this;
    }

    /**
     * Get hasAst
     *
     * @return integer
     */
    public function getHasAst()
    {
        return $this->hasAst;
    }

    /**
     * Set hasA
     *
     * @param integer $hasA
     *
     * @return VoterHistory
     */
    public function setHasA($hasA)
    {
        $this->hasA = $hasA;

        return $this;
    }

    /**
     * Get hasA
     *
     * @return integer
     */
    public function getHasA()
    {
        return $this->hasA;
    }

    /**
     * Set hasB
     *
     * @param integer $hasB
     *
     * @return VoterHistory
     */
    public function setHasB($hasB)
    {
        $this->hasB = $hasB;

        return $this;
    }

    /**
     * Get hasB
     *
     * @return integer
     */
    public function getHasB()
    {
        return $this->hasB;
    }

    /**
     * Set hasC
     *
     * @param integer $hasC
     *
     * @return VoterHistory
     */
    public function setHasC($hasC)
    {
        $this->hasC = $hasC;

        return $this;
    }

    /**
     * Get hasC
     *
     * @return integer
     */
    public function getHasC()
    {
        return $this->hasC;
    }

    /**
     * Set is1
     *
     * @param integer $is1
     *
     * @return VoterHistory
     */
    public function setIs1($is1)
    {
        $this->is1 = $is1;

        return $this;
    }

    /**
     * Get is1
     *
     * @return integer
     */
    public function getIs1()
    {
        return $this->is1;
    }

    /**
     * Set is2
     *
     * @param integer $is2
     *
     * @return VoterHistory
     */
    public function setIs2($is2)
    {
        $this->is2 = $is2;

        return $this;
    }

    /**
     * Get is2
     *
     * @return integer
     */
    public function getIs2()
    {
        return $this->is2;
    }

    /**
     * Set is3
     *
     * @param integer $is3
     *
     * @return VoterHistory
     */
    public function setIs3($is3)
    {
        $this->is3 = $is3;

        return $this;
    }

    /**
     * Get is3
     *
     * @return integer
     */
    public function getIs3()
    {
        return $this->is3;
    }

    /**
     * Set is4
     *
     * @param integer $is4
     *
     * @return VoterHistory
     */
    public function setIs4($is4)
    {
        $this->is4 = $is4;

        return $this;
    }

    /**
     * Get is4
     *
     * @return integer
     */
    public function getIs4()
    {
        return $this->is4;
    }

    /**
     * Set is5
     *
     * @param integer $is5
     *
     * @return VoterHistory
     */
    public function setIs5($is5)
    {
        $this->is5 = $is5;

        return $this;
    }

    /**
     * Get is5
     *
     * @return integer
     */
    public function getIs5()
    {
        return $this->is5;
    }

    /**
     * Set is6
     *
     * @param integer $is6
     *
     * @return VoterHistory
     */
    public function setIs6($is6)
    {
        $this->is6 = $is6;

        return $this;
    }

    /**
     * Get is6
     *
     * @return integer
     */
    public function getIs6()
    {
        return $this->is6;
    }

    /**
     * Set is7
     *
     * @param integer $is7
     *
     * @return VoterHistory
     */
    public function setIs7($is7)
    {
        $this->is7 = $is7;

        return $this;
    }

    /**
     * Get is7
     *
     * @return integer
     */
    public function getIs7()
    {
        return $this->is7;
    }

    /**
     * Set voterStatus
     *
     * @param string $voterStatus
     *
     * @return VoterHistory
     */
    public function setVoterStatus($voterStatus)
    {
        $this->voterStatus = $voterStatus;

        return $this;
    }

    /**
     * Get voterStatus
     *
     * @return string
     */
    public function getVoterStatus()
    {
        return $this->voterStatus;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return VoterHistory
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
     * @return VoterHistory
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
     * @return VoterHistory
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
     * @return VoterHistory
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
     * Set onNetwork
     *
     * @param integer $onNetwork
     *
     * @return VoterHistory
     */
    public function setOnNetwork($onNetwork)
    {
        $this->onNetwork = $onNetwork;
    
        return $this;
    }

    /**
     * Get onNetwork
     *
     * @return integer
     */
    public function getOnNetwork()
    {
        return $this->onNetwork;
    }

    /**
     * Set category
     *
     * @param string $category
     *
     * @return VoterHistory
     */
    public function setCategory($category)
    {
        $this->category = strtoupper(trim($category));
    
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
     * Set birthdate
     *
     * @param string $birthdate
     *
     * @return VoterHistory
     */
    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;
    
        return $this;
    }

    /**
     * Get birthdate
     *
     * @return string
     */
    public function getBirthdate()
    {
        return $this->birthdate;
    }

    /**
     * Set organization
     *
     * @param string $organization
     *
     * @return VoterHistory
     */
    public function setOrganization($organization)
    {
        $this->organization = strtoupper(trim($organization));
    
        return $this;
    }

    /**
     * Get organization
     *
     * @return string
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Set position
     *
     * @param string $position
     *
     * @return VoterHistory
     */
    public function setPosition($position)
    {
        $this->position = strtoupper(trim($position));
    
        return $this;
    }

    /**
     * Get position
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }
}
