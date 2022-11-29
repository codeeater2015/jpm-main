<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * SpecialOperationDetail
 *
 * @ORM\Table(name="tbl_recruitment_special_dtl")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SpecialOperationDetailRepository")
 * @UniqueEntity(fields={"proVoterId","recId"},message="This voter already exists.", errorPath="proVoterId")
 */
class SpecialOperationDetail
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
     * @ORM\Column(name="rec_id", type="integer")
     * @Assert\NotBlank()
     */
    private $recId;

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
     * @var string
     * 
     * @Assert\NotBlank()
     */
    private $gender;
    
    /**
     * @var string
     * 
     * @Assert\NotBlank()
     */
    private $firstname;
    
    /**
     * @var string
     * 
     * @Assert\NotBlank()
     */
    private $middlename;
    
    /**
     * @var string
     * 
     * @Assert\NotBlank()
     */
    private $lastname;

     /**
     * @var string
     * 
     */
    private $extName;
    
    /**
     * @var string
     * 
     */
    private $birthdate;
    
    /**
     * @var string
     *
     */
    private $dialect;

    /**
     * @var string
     * 
     */
    private $religion;
    
    /**
     * @var string
     * 
     * @Assert\Regex("/^(09)\d{9}$/")
     */
    private $cellphone;

    /**
     * @var int
     *
     */
    private $isBisaya;

     /**
     * @var int
     *
     */
    private $isCuyonon;

    /**
     * @var int
     *
     */
    private $isTagalog;

     /**
     * @var int
     *
     */
    private $isIlonggo;

    /**
     * @var int
     *
     */
    private $isCatholic;

    /**
     * @var int
     *
     */
    private $isInc;

    /**
     * @var int
     *
     */
    private $isIslam;

    /**
     * @var string
     *
     */
    private $position;


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
     * Set recId
     *
     * @param integer $recId
     *
     * @return RecruitmentDetail
     */
    public function setRecId($recId)
    {
        $this->recId = $recId;

        return $this;
    }

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
     * Set proVoterId
     *
     * @param integer $proVoterId
     *
     * @return RecruitmentDetail
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
     * Set voterName
     *
     * @param string $voterName
     *
     * @return RecruitmentDetail
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
     * @return RecruitmentDetail
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
     * @return RecruitmentDetail
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
     * @return RecruitmentDetail
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
     * @return RecruitmentDetail
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
     * @return RecruitmentDetail
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
     * Set gender
     *
     * @param string $gender
     *
     * @return RecruitmentDetail
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     *
     * @return RecruitmentDetail
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set middlename
     *
     * @param string $middlename
     *
     * @return RecruitmentDetail
     */
    public function setMiddlename($middlename)
    {
        $this->middlename = $middlename;

        return $this;
    }

    /**
     * Get middlename
     *
     * @return string
     */
    public function getMiddlename()
    {
        return $this->middlename;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     *
     * @return RecruitmentDetail
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set birthdate
     *
     * @param string $birthdate
     *
     * @return RecruitmentDetail
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
     * Set dialect
     *
     * @param string $dialect
     *
     * @return RecruitmentDetail
     */
    public function setDialect($dialect)
    {
        $this->dialect = $dialect;

        return $this;
    }

    /**
     * Get dialect
     *
     * @return string
     */
    public function getDialect()
    {
        return $this->dialect;
    }

    /**
     * Set religion
     *
     * @param string $religion
     *
     * @return RecruitmentDetail
     */
    public function setReligion($religion)
    {
        $this->religion = $religion;

        return $this;
    }

    /**
     * Get religion
     *
     * @return string
     */
    public function getReligion()
    {
        return $this->religion;
    }

    /**
     * Set cellphone
     *
     * @param string $cellphone
     *
     * @return RecruitmentDetail
     */
    public function setCellphone($cellphone)
    {
        $this->cellphone = $cellphone;

        return $this;
    }

    /**
     * Get cellphone
     *
     * @return string
     */
    public function getCellphone()
    {
        return $this->cellphone;
    }

    /**
     * Set remarks
     *
     * @param string $remarks
     *
     * @return RecruitmentDetail
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
     * @return RecruitmentDetail
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
     * @return RecruitmentDetail
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
     * @return RecruitmentDetail
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
     * Set extName
     *
     * @param string $extName
     *
     * @return RecruitmentDetail
     */
    public function setExtName($extName)
    {
        $this->extName = $extName;

        return $this;
    }

    /**
     * Get extName
     *
     * @return string
     */
    public function getExtName()
    {
        return $this->extName;
    }

    /**
     * Set proIdCode
     *
     * @param string $proIdCode
     *
     * @return ProjectVoter
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
     * Set isBisaya
     *
     * @param integer $isBisaya
     *
     * @return ProjectVoter
     */
    public function setIsBisaya($isBisaya)
    {
        $this->isBisaya = $isBisaya;

        return $this;
    }

    /**
     * Get isBisaya
     *
     * @return integer
     */
    public function getIsBisaya()
    {
        return $this->isBisaya;
    }

    /**
     * Set isCuyonon
     *
     * @param integer $isCuyonon
     *
     * @return ProjectVoter
     */
    public function setIsCuyonon($isCuyonon)
    {
        $this->isCuyonon = $isCuyonon;

        return $this;
    }

    /**
     * Get isCuyonon
     *
     * @return integer
     */
    public function getIsCuyonon()
    {
        return $this->isCuyonon;
    }

    /**
     * Set isTagalog
     *
     * @param integer $isTagalog
     *
     * @return ProjectVoter
     */
    public function setIsTagalog($isTagalog)
    {
        $this->isTagalog = $isTagalog;

        return $this;
    }

    /**
     * Get isTagalog
     *
     * @return integer
     */
    public function getIsTagalog()
    {
        return $this->isTagalog;
    }

    /**
     * Set isIlonggo
     *
     * @param integer $isIlonggo
     *
     * @return ProjectVoter
     */
    public function setIsIlonggo($isIlonggo)
    {
        $this->isIlonggo = $isIlonggo;

        return $this;
    }

    /**
     * Get isIlonggo
     *
     * @return integer
     */
    public function getIsIlonggo()
    {
        return $this->isIlonggo;
    }

    /**
     * Set isCatholic
     *
     * @param integer $isCatholic
     *
     * @return ProjectVoter
     */
    public function setIsCatholic($isCatholic)
    {
        $this->isCatholic = $isCatholic;

        return $this;
    }

    /**
     * Get isCatholic
     *
     * @return integer
     */
    public function getIsCatholic()
    {
        return $this->isCatholic;
    }

    /**
     * Set isInc
     *
     * @param integer $isInc
     *
     * @return ProjectVoter
     */
    public function setIsInc($isInc)
    {
        $this->isInc = $isInc;

        return $this;
    }

    /**
     * Get isInc
     *
     * @return integer
     */
    public function getIsInc()
    {
        return $this->isInc;
    }

    /**
     * Set isIslam
     *
     * @param integer $isIslam
     *
     * @return ProjectVoter
     */
    public function setIsIslam($isIslam)
    {
        $this->isIslam = $isIslam;

        return $this;
    }

    /**
     * Get isIslam
     *
     * @return integer
     */
    public function getIsIslam()
    {
        return $this->isIslam;
    }

    /**
     * Set position
     *
     * @param string $position
     *
     * @return ProjectVoter
     */
    public function setPosition($position)
    {
        $this->position = $position;

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
