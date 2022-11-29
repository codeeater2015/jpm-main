<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * FinancialAssistanceHeader
 *
 * @ORM\Table(name="tbl_fa_hdr")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FinancialAssistanceHeaderRepository")
 * @UniqueEntity(fields={"trnNo"},message="This trn no already exists.", errorPath="trnNo")
 * @UniqueEntity(fields={"trnId"},message="This trn id already exists.", errorPath="trnId")
 */
class FinancialAssistanceHeader
{
    /**
     * @var int
     *
     * @ORM\Column(name="trn_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $trnId;

    /**
     * @var string
     *
     * @ORM\Column(name="trn_no", type="string")
     * @Assert\NotBlank()
     */
    private $trnNo;

    /**
     * @var string
     *
     * @ORM\Column(name="applicant_name", type="string", length=150)
     * @Assert\NotBlank()
     */
    private $applicantName;

    /**
     * @var int
     *
     * @ORM\Column(name="applicant_pro_voter_id", type="integer")
     */
    private $applicantProVoterId;

    /**
     * @var string
     *
     * @ORM\Column(name="beneficiary_name", type="string", length=150)
     * @Assert\NotBlank()
     */
    private $beneficiaryName;

    /**
     * @var int
     *
     * @ORM\Column(name="beneficiary_pro_voter_id", type="integer")
     */
    private $beneficiaryProVoterId;

     /**
     * @var int
     *
     * @ORM\Column(name="is_released", type="integer")
     */
    private $isReleased;
    
     /**
     * @var int
     *
     * @ORM\Column(name="is_closed", type="integer")
     */
    private $isClosed;
    
     /**
     * @var string
     *
     * @ORM\Column(name="closed_date", type="string", length=15)
     */
    private $closedDate;

     /**
     * @var string
     *
     * @ORM\Column(name="contact_no", type="string", length=15)
     * @Assert\NotBlank()
     */
    private $contactNo;

    /**
     * @var string
     *
     * @ORM\Column(name="hospital_name", type="string", length=150)
     */
    private $hospitalName;

    /**
     * @var string
     *
     * @ORM\Column(name="jpm_id_no", type="string", length=100)
     */
    private $jpmIdNo;

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
     * @ORM\Column(name="barangay_no", type="string", length=15)
     * @Assert\NotBlank()
     */
    private $barangayNo;

     /**
     * @var string
     *
     * @ORM\Column(name="type_of_asst", type="string")
     * @Assert\NotBlank()
     */
    private $typeOfAsst;

     /**
     * @var string
     *
     * @ORM\Column(name="endorsed_by", type="string", length=150)
     * @Assert\NotBlank()
     */
    private $endorsedBy;
    
     /**
     * @var string
     *
     * @ORM\Column(name="trn_date", type="string", length=15)
     * @Assert\NotBlank()
     */
    private $trnDate;

     /**
     * @var float
     *
     * @ORM\Column(name="projected_amt", type="float", scale=2)
     */
    private $projectedAmt;

    /**
     * @var float
     *
     * @ORM\Column(name="granted_amt", type="float", scale=2)
     */
    private $grantedAmt;

     /**
     * @var string
     *
     * @ORM\Column(name="release_date", type="string", length=15)
     */
    private $releaseDate;

     /**
     * @var string
     *
     * @ORM\Column(name="releasing_office", type="string", length = 150)
     */
    private $releasingOffice;
    
    /**
     * @var string
     *
     * @ORM\Column(name="received_by", type="string", length=150)
     */
    private $receivedBy;

    /**
     * @var string
     *
     * @ORM\Column(name="personnel", type="string", length = 150)
     */
    private $personnel;
    
    /**
     * @var datetime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="updated_by", type="string", length=150)
     */
    private $updatedBy;

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
     * @ORM\Column(name="remarks", type="string", length=255)
     */
    private $remarks;
    
    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=3)
     */
    private $status;


    /**
     * Get trnId
     *
     * @return integer
     */
    public function getTrnId()
    {
        return $this->trnId;
    }

    /**
     * Set trnNo
     *
     * @param string $trnNo
     *
     * @return FinancialAssistanceHeader
     */
    public function setTrnNo($trnNo)
    {
        $this->trnNo = $trnNo;

        return $this;
    }

    /**
     * Get trnNo
     *
     * @return string
     */
    public function getTrnNo()
    {
        return $this->trnNo;
    }

    /**
     * Set applicantName
     *
     * @param string $applicantName
     *
     * @return FinancialAssistanceHeader
     */
    public function setApplicantName($applicantName)
    {
        $this->applicantName = $applicantName;

        return $this;
    }

    /**
     * Get applicantName
     *
     * @return string
     */
    public function getApplicantName()
    {
        return $this->applicantName;
    }

    /**
     * Set applicantProVoterId
     *
     * @param integer $applicantProVoterId
     *
     * @return FinancialAssistanceHeader
     */
    public function setApplicantProVoterId($applicantProVoterId)
    {
        $this->applicantProVoterId = $applicantProVoterId;

        return $this;
    }

    /**
     * Get applicantProVoterId
     *
     * @return integer
     */
    public function getApplicantProVoterId()
    {
        return $this->applicantProVoterId;
    }

    /**
     * Set beneficiaryName
     *
     * @param string $beneficiaryName
     *
     * @return FinancialAssistanceHeader
     */
    public function setBeneficiaryName($beneficiaryName)
    {
        $this->beneficiaryName = $beneficiaryName;

        return $this;
    }

    /**
     * Get beneficiaryName
     *
     * @return string
     */
    public function getBeneficiaryName()
    {
        return $this->beneficiaryName;
    }

    /**
     * Set contactNo
     *
     * @param string $contactNo
     *
     * @return FinancialAssistanceHeader
     */
    public function setContactNo($contactNo)
    {
        $this->contactNo = $contactNo;

        return $this;
    }

    /**
     * Get contactNo
     *
     * @return string
     */
    public function getContactNo()
    {
        return $this->contactNo;
    }

    /**
     * Set jpmIdNo
     *
     * @param string $jpmIdNo
     *
     * @return FinancialAssistanceHeader
     */
    public function setJpmIdNo($jpmIdNo)
    {
        $this->jpmIdNo = $jpmIdNo;

        return $this;
    }

    /**
     * Get jpmIdNo
     *
     * @return string
     */
    public function getJpmIdNo()
    {
        return $this->jpmIdNo;
    }

    /**
     * Set municipalityNo
     *
     * @param string $municipalityNo
     *
     * @return FinancialAssistanceHeader
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
     * Set barangayNo
     *
     * @param string $barangayNo
     *
     * @return FinancialAssistanceHeader
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
     * Set typeOfAsst
     *
     * @param string $typeOfAsst
     *
     * @return FinancialAssistanceHeader
     */
    public function setTypeOfAsst($typeOfAsst)
    {
        $this->typeOfAsst = $typeOfAsst;

        return $this;
    }

    /**
     * Get typeOfAsst
     *
     * @return string
     */
    public function getTypeOfAsst()
    {
        return $this->typeOfAsst;
    }

    /**
     * Set endorsedBy
     *
     * @param string $endorsedBy
     *
     * @return FinancialAssistanceHeader
     */
    public function setEndorsedBy($endorsedBy)
    {
        $this->endorsedBy = $endorsedBy;

        return $this;
    }

    /**
     * Get endorsedBy
     *
     * @return string
     */
    public function getEndorsedBy()
    {
        return $this->endorsedBy;
    }

    /**
     * Set trnDate
     *
     * @param string $trnDate
     *
     * @return FinancialAssistanceHeader
     */
    public function setTrnDate($trnDate)
    {
        $this->trnDate = $trnDate;

        return $this;
    }

    /**
     * Get trnDate
     *
     * @return string
     */
    public function getTrnDate()
    {
        return $this->trnDate;
    }

    /**
     * Set projectedAmt
     *
     * @param float $projectedAmt
     *
     * @return FinancialAssistanceHeader
     */
    public function setProjectedAmt($projectedAmt)
    {
        $this->projectedAmt = $projectedAmt;

        return $this;
    }

    /**
     * Get projectedAmt
     *
     * @return float
     */
    public function getProjectedAmt()
    {
        return $this->projectedAmt;
    }

    /**
     * Set grantedAmt
     *
     * @param float $grantedAmt
     *
     * @return FinancialAssistanceHeader
     */
    public function setGrantedAmt($grantedAmt)
    {
        $this->grantedAmt = $grantedAmt;

        return $this;
    }

    /**
     * Get grantedAmt
     *
     * @return float
     */
    public function getGrantedAmt()
    {
        return $this->grantedAmt;
    }

    /**
     * Set releaseDate
     *
     * @param string $releaseDate
     *
     * @return FinancialAssistanceHeader
     */
    public function setReleaseDate($releaseDate)
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    /**
     * Get releaseDate
     *
     * @return string
     */
    public function getReleaseDate()
    {
        return $this->releaseDate;
    }

    /**
     * Set releasingOffice
     *
     * @param string $releasingOffice
     *
     * @return FinancialAssistanceHeader
     */
    public function setReleasingOffice($releasingOffice)
    {
        $this->releasingOffice = $releasingOffice;

        return $this;
    }

    /**
     * Get releasingOffice
     *
     * @return string
     */
    public function getReleasingOffice()
    {
        return $this->releasingOffice;
    }

    /**
     * Set receivedBy
     *
     * @param string $receivedBy
     *
     * @return FinancialAssistanceHeader
     */
    public function setReceivedBy($receivedBy)
    {
        $this->receivedBy = $receivedBy;

        return $this;
    }

    /**
     * Get receivedBy
     *
     * @return string
     */
    public function getReceivedBy()
    {
        return $this->receivedBy;
    }

    /**
     * Set personnel
     *
     * @param string $personnel
     *
     * @return FinancialAssistanceHeader
     */
    public function setPersonnel($personnel)
    {
        $this->personnel = $personnel;

        return $this;
    }

    /**
     * Get personnel
     *
     * @return string
     */
    public function getPersonnel()
    {
        return $this->personnel;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return FinancialAssistanceHeader
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set updatedBy
     *
     * @param string $updatedBy
     *
     * @return FinancialAssistanceHeader
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return string
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return FinancialAssistanceHeader
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
     * @return FinancialAssistanceHeader
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
     * @return FinancialAssistanceHeader
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
     * @return FinancialAssistanceHeader
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
     * Set beneficiaryProVoterId
     *
     * @param integer $beneficiaryProVoterId
     *
     * @return FinancialAssistanceHeader
     */
    public function setBeneficiaryProVoterId($beneficiaryProVoterId)
    {
        $this->beneficiaryProVoterId = $beneficiaryProVoterId;

        return $this;
    }

    /**
     * Get beneficiaryProVoterId
     *
     * @return integer
     */
    public function getBeneficiaryProVoterId()
    {
        return $this->beneficiaryProVoterId;
    }

    /**
     * Set isReleased
     *
     * @param integer $isReleased
     *
     * @return FinancialAssistanceHeader
     */
    public function setIsReleased($isReleased)
    {
        $this->isReleased = $isReleased;

        return $this;
    }

    /**
     * Get isReleased
     *
     * @return integer
     */
    public function getIsReleased()
    {
        return $this->isReleased;
    }

    /**
     * Set isClosed
     *
     * @param integer $isClosed
     *
     * @return FinancialAssistanceHeader
     */
    public function setIsClosed($isClosed)
    {
        $this->isClosed = $isClosed;

        return $this;
    }

    /**
     * Get isClosed
     *
     * @return integer
     */
    public function getIsClosed()
    {
        return $this->isClosed;
    }

    /**
     * Set closedDate
     *
     * @param string $closedDate
     *
     * @return FinancialAssistanceHeader
     */
    public function setClosedDate($closedDate)
    {
        $this->closedDate = $closedDate;

        return $this;
    }

    /**
     * Get closedDate
     *
     * @return string
     */
    public function getClosedDate()
    {
        return $this->closedDate;
    }

    /**
     * Set hospitalName
     *
     * @param string $hospitalName
     *
     * @return FinancialAssistanceHeader
     */
    public function setHospitalName($hospitalName)
    {
        $this->hospitalName = $hospitalName;

        return $this;
    }

    /**
     * Get hospitalName
     *
     * @return string
     */
    public function getHospitalName()
    {
        return $this->hospitalName;
    }
}
