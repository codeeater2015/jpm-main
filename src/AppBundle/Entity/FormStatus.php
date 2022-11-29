<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * FormStatus
 *
 * @ORM\Table(name="tbl_form_status")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FormStatusRepository")
 * @UniqueEntity(fields={"proVoterId"},message="This voter already exists.", errorPath="proVoterId")
 */
class FormStatus
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

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
    * @ORM\Column(name="pro_id_code", type="string", length=30)
    * @Assert\NotBlank()
    */
   private $proIdCode;

   /**
    * @var string
    *
    * @ORM\Column(name="voter_name", type="string", length=255)
    */
   private $voterName;
   
    /**
    * @var string
    *
    * @ORM\Column(name="voter_group", type="string", length=30)
    */
   private $voterGroup;

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
    * @ORM\Column(name="municipality_name", type="string", length=15)
    */
   private $municipalityName;

   /**
    * @var string
    *
    * @ORM\Column(name="barangay_name", type="string", length=255)
    */
   private $barangayName;

   /**
    * @var string
    *
    * @ORM\Column(name="barangay_no", type="string", length=15)
    * @Assert\NotBlank()
    */
   private $barangayNo;

    /** 
    * @var int
    *
    * @ORM\Column(name="rec_form_sub", type="integer")
    */
    private $recFormSub;

    /** 
    * @var int
    *
    * @ORM\Column(name="house_form_sub", type="integer")
    */
    private $houseFormSub;

     /** 
    * @var int
    *
    * @ORM\Column(name="rec_form_enc", type="integer")
    */
    private $recFormEnc;

    /** 
    * @var int
    *
    * @ORM\Column(name="house_form_enc", type="integer")
    */
    private $houseFormEnc;

     /** 
    * @var int
    *
    * @ORM\Column(name="rec_form_sub_count", type="integer")
    */
    private $recFormSubCount;

    /** 
    * @var int
    *
    * @ORM\Column(name="house_form_sub_count", type="integer")
    */
    private $houseFormSubCount;

     /** 
    * @var int
    *
    * @ORM\Column(name="rec_form_enc_count", type="integer")
    */
    private $recFormEncCount;

    /** 
    * @var int
    *
    * @ORM\Column(name="house_form_enc_count", type="integer")
    */
    private $houseFormEncCount;

    
    /** 
    * @var string
    *
    * @ORM\Column(name="rec_form_sub_date", type="string")
    */
    private $recFormSubDate;

    /** 
    * @var string
    *
    * @ORM\Column(name="house_form_sub_date", type="string")
    */
    private $houseFormSubDate;

     /** 
    * @var string
    *
    * @ORM\Column(name="rec_form_enc_date", type="string")
    */
    private $recFormEncDate;

    /** 
    * @var string
    *
    * @ORM\Column(name="house_form_enc_date", type="string")
    */
    private $houseFormEncDate;

     /**
     * @var string
     *
     * @ORM\Column(name="remarks", type="string", length=255)
     */
    private $remarks;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set proVoterId
     *
     * @param integer $proVoterId
     *
     * @return FormStatus
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
     * Set proIdCode
     *
     * @param string $proIdCode
     *
     * @return FormStatus
     */
    public function setProIdCode($proIdCode)
    {
        $this->proIdCode = $proIdCode;

        return $this;
    }

    /**
     * Get proIdCode
     *
     * @return string
     */
    public function getProIdCode()
    {
        return $this->proIdCode;
    }

    /**
     * Set voterName
     *
     * @param string $voterName
     *
     * @return FormStatus
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
     * Set voterGroup
     *
     * @param string $voterGroup
     *
     * @return FormStatus
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
     * Set municipalityNo
     *
     * @param string $municipalityNo
     *
     * @return FormStatus
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
     * Set municipalityName
     *
     * @param string $municipalityName
     *
     * @return FormStatus
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
     * @return FormStatus
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
     * Set barangayNo
     *
     * @param string $barangayNo
     *
     * @return FormStatus
     */
    public function setBarangayNo($barangayNo)
    {
        $this->barangayNo = $barangayNo;

        return $this;
    }

    /**
     * Get barangayNo
     *
     * @return string
     */
    public function getBarangayNo()
    {
        return $this->barangayNo;
    }

    /**
     * Set recFormSub
     *
     * @param integer $recFormSub
     *
     * @return FormStatus
     */
    public function setRecFormSub($recFormSub)
    {
        $this->recFormSub = $recFormSub;

        return $this;
    }

    /**
     * Get recFormSub
     *
     * @return integer
     */
    public function getRecFormSub()
    {
        return $this->recFormSub;
    }

    /**
     * Set houseFormSub
     *
     * @param integer $houseFormSub
     *
     * @return FormStatus
     */
    public function setHouseFormSub($houseFormSub)
    {
        $this->houseFormSub = $houseFormSub;

        return $this;
    }

    /**
     * Get houseFormSub
     *
     * @return integer
     */
    public function getHouseFormSub()
    {
        return $this->houseFormSub;
    }

    /**
     * Set recFormEnc
     *
     * @param integer $recFormEnc
     *
     * @return FormStatus
     */
    public function setRecFormEnc($recFormEnc)
    {
        $this->recFormEnc = $recFormEnc;

        return $this;
    }

    /**
     * Get recFormEnc
     *
     * @return integer
     */
    public function getRecFormEnc()
    {
        return $this->recFormEnc;
    }

    /**
     * Set houseFormEnc
     *
     * @param integer $houseFormEnc
     *
     * @return FormStatus
     */
    public function setHouseFormEnc($houseFormEnc)
    {
        $this->houseFormEnc = $houseFormEnc;

        return $this;
    }

    /**
     * Get houseFormEnc
     *
     * @return integer
     */
    public function getHouseFormEnc()
    {
        return $this->houseFormEnc;
    }

    /**
     * Set recFormSubCount
     *
     * @param integer $recFormSubCount
     *
     * @return FormStatus
     */
    public function setRecFormSubCount($recFormSubCount)
    {
        $this->recFormSubCount = $recFormSubCount;

        return $this;
    }

    /**
     * Get recFormSubCount
     *
     * @return integer
     */
    public function getRecFormSubCount()
    {
        return $this->recFormSubCount;
    }

    /**
     * Set houseFormSubCount
     *
     * @param integer $houseFormSubCount
     *
     * @return FormStatus
     */
    public function setHouseFormSubCount($houseFormSubCount)
    {
        $this->houseFormSubCount = $houseFormSubCount;

        return $this;
    }

    /**
     * Get houseFormSubCount
     *
     * @return integer
     */
    public function getHouseFormSubCount()
    {
        return $this->houseFormSubCount;
    }

    /**
     * Set recFormEncCount
     *
     * @param integer $recFormEncCount
     *
     * @return FormStatus
     */
    public function setRecFormEncCount($recFormEncCount)
    {
        $this->recFormEncCount = $recFormEncCount;

        return $this;
    }

    /**
     * Get recFormEncCount
     *
     * @return integer
     */
    public function getRecFormEncCount()
    {
        return $this->recFormEncCount;
    }

    /**
     * Set houseFormEncCount
     *
     * @param integer $houseFormEncCount
     *
     * @return FormStatus
     */
    public function setHouseFormEncCount($houseFormEncCount)
    {
        $this->houseFormEncCount = $houseFormEncCount;

        return $this;
    }

    /**
     * Get houseFormEncCount
     *
     * @return integer
     */
    public function getHouseFormEncCount()
    {
        return $this->houseFormEncCount;
    }

    /**
     * Set recFormSubDate
     *
     * @param string $recFormSubDate
     *
     * @return FormStatus
     */
    public function setRecFormSubDate($recFormSubDate)
    {
        $this->recFormSubDate = $recFormSubDate;

        return $this;
    }

    /**
     * Get recFormSubDate
     *
     * @return string
     */
    public function getRecFormSubDate()
    {
        return $this->recFormSubDate;
    }

    /**
     * Set houseFormSubDate
     *
     * @param string $houseFormSubDate
     *
     * @return FormStatus
     */
    public function setHouseFormSubDate($houseFormSubDate)
    {
        $this->houseFormSubDate = $houseFormSubDate;

        return $this;
    }

    /**
     * Get houseFormSubDate
     *
     * @return string
     */
    public function getHouseFormSubDate()
    {
        return $this->houseFormSubDate;
    }

    /**
     * Set recFormEncDate
     *
     * @param string $recFormEncDate
     *
     * @return FormStatus
     */
    public function setRecFormEncDate($recFormEncDate)
    {
        $this->recFormEncDate = $recFormEncDate;

        return $this;
    }

    /**
     * Get recFormEncDate
     *
     * @return string
     */
    public function getRecFormEncDate()
    {
        return $this->recFormEncDate;
    }

    /**
     * Set houseFormEncDate
     *
     * @param string $houseFormEncDate
     *
     * @return FormStatus
     */
    public function setHouseFormEncDate($houseFormEncDate)
    {
        $this->houseFormEncDate = $houseFormEncDate;

        return $this;
    }

    /**
     * Get houseFormEncDate
     *
     * @return string
     */
    public function getHouseFormEncDate()
    {
        return $this->houseFormEncDate;
    }

    /**
     * Set remarks
     *
     * @param string $remarks
     *
     * @return FormStatus
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
     * @return FormStatus
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
     * @return FormStatus
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
     * @return FormStatus
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
