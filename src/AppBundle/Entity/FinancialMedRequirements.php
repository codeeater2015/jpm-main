<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * FinancialMedRequirements
 *
 * @ORM\Table(name="tbl_fa_med_req")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FinancialMedRequirementsRepository")
 * @UniqueEntity(fields={"medId"},message="This med id already exists.", errorPath="medId")
 * @UniqueEntity(fields={"trnNo"},message="This trn no already exists.", errorPath="trnNo")
 */
class FinancialMedRequirements
{
    /**
     * @var int
     *
     * @ORM\Column(name="med_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $medId;

    /**
     * @var int
     *
     * @ORM\Column(name="trn_id", type="integer")
     * @Assert\NotBlank()
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
     * @ORM\Column(name="req_type", type="string", length=15)
     * @Assert\NotBlank()
     */
    private $reqType;

     /**
     * @var integer
     *
     * @ORM\Column(name="has_req_letter", type="integer", scale = 1)
     */
    private $hasReqLetter;

     /**
     * @var integer
     *
     * @ORM\Column(name="has_brgy_clearance", type="integer", scale = 1)
     */
    private $hasBrgyClearance;

     /**
     * @var integer
     *
     * @ORM\Column(name="has_patient_id", type="integer", scale = 1)
     */
    private $hasPatientId;

     /**
     * @var integer
     *
     * @ORM\Column(name="has_med_cert", type="integer", scale = 1)
     */
    private $hasMedCert;

     /**
     * @var integer
     *
     * @ORM\Column(name="has_med_abst", type="integer", scale = 1)
     */
    private $hasMedAbst;

     /**
     * @var integer
     *
     * @ORM\Column(name="has_promisory_note", type="integer", scale = 1)
     */
    private $hasPromisoryNote;

     /**
     * @var integer
     *
     * @ORM\Column(name="has_bill_statement", type="integer", scale = 1)
     */
    private $hasBillStatement;

     /**
     * @var integer
     *
     * @ORM\Column(name="has_price_quot", type="integer", scale = 1)
     */
    private $hasPriceQuot;

    /**
     * @var integer
     *
     * @ORM\Column(name="has_req_of_physician", type="integer", scale = 1)
     */
    private $hasReqOfPhysician;

     /**
     * @var integer
     *
     * @ORM\Column(name="has_reseta", type="integer", scale = 1)
     */
    private $hasReseta;
    
     /**
     * @var integer
     *
     * @ORM\Column(name="has_social_cast_report", type="integer", scale = 1)
     */
    private $hasSocialCastReport;

     /**
     * @var integer
     *
     * @ORM\Column(name="has_police_report", type="integer", scale = 1)
     */
    private $hasPoliceReport;

     /**
     * @var integer
     *
     * @ORM\Column(name="has_death_cert", type="integer", scale = 1)
     */
    private $hasDeathCert;

    /**
     * @var integer
     *
     * @ORM\Column(name="is_dswd_medical", type="integer", scale = 1)
     */
    private $isDswdMedical;

    /**
     * @var integer
     *
     * @ORM\Column(name="is_dswd_opd", type="integer", scale = 1)
     */
    private $isDswdOpd;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="is_doh_maip_medical", type="integer", scale = 1)
     */
    private $isDohMaipMedical;

    /**
     * @var integer
     *
     * @ORM\Column(name="is_doh_maip_opd", type="integer", scale = 1)
     */
    private $isDohMaipOpd;

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
     * @var string
     *
     * @ORM\Column(name="remarks", type="string", length=255)
     */
    private $remarks;

    /**
     * Get medId
     *
     * @return integer
     */
    public function getMedId()
    {
        return $this->medId;
    }

    /**
     * Set trnId
     *
     * @param integer $trnId
     *
     * @return FinancialMedRequirements
     */
    public function setTrnId($trnId)
    {
        $this->trnId = $trnId;

        return $this;
    }

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
     * @return FinancialMedRequirements
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
     * Set reqType
     *
     * @param string $reqType
     *
     * @return FinancialMedRequirements
     */
    public function setReqType($reqType)
    {
        $this->reqType = $reqType;

        return $this;
    }

    /**
     * Get reqType
     *
     * @return string
     */
    public function getReqType()
    {
        return $this->reqType;
    }

    /**
     * Set hasReqLetter
     *
     * @param integer $hasReqLetter
     *
     * @return FinancialMedRequirements
     */
    public function setHasReqLetter($hasReqLetter)
    {
        $this->hasReqLetter = $hasReqLetter;

        return $this;
    }

    /**
     * Get hasReqLetter
     *
     * @return integer
     */
    public function getHasReqLetter()
    {
        return $this->hasReqLetter;
    }

    /**
     * Set hasBrgyClearance
     *
     * @param integer $hasBrgyClearance
     *
     * @return FinancialMedRequirements
     */
    public function setHasBrgyClearance($hasBrgyClearance)
    {
        $this->hasBrgyClearance = $hasBrgyClearance;

        return $this;
    }

    /**
     * Get hasBrgyClearance
     *
     * @return integer
     */
    public function getHasBrgyClearance()
    {
        return $this->hasBrgyClearance;
    }

    /**
     * Set hasPatientId
     *
     * @param integer $hasPatientId
     *
     * @return FinancialMedRequirements
     */
    public function setHasPatientId($hasPatientId)
    {
        $this->hasPatientId = $hasPatientId;

        return $this;
    }

    /**
     * Get hasPatientId
     *
     * @return integer
     */
    public function getHasPatientId()
    {
        return $this->hasPatientId;
    }

    /**
     * Set hasMedCert
     *
     * @param integer $hasMedCert
     *
     * @return FinancialMedRequirements
     */
    public function setHasMedCert($hasMedCert)
    {
        $this->hasMedCert = $hasMedCert;

        return $this;
    }

    /**
     * Get hasMedCert
     *
     * @return integer
     */
    public function getHasMedCert()
    {
        return $this->hasMedCert;
    }

    /**
     * Set hasMedAbst
     *
     * @param integer $hasMedAbst
     *
     * @return FinancialMedRequirements
     */
    public function setHasMedAbst($hasMedAbst)
    {
        $this->hasMedAbst = $hasMedAbst;

        return $this;
    }

    /**
     * Get hasMedAbst
     *
     * @return integer
     */
    public function getHasMedAbst()
    {
        return $this->hasMedAbst;
    }

    /**
     * Set hasPromisoryNote
     *
     * @param integer $hasPromisoryNote
     *
     * @return FinancialMedRequirements
     */
    public function setHasPromisoryNote($hasPromisoryNote)
    {
        $this->hasPromisoryNote = $hasPromisoryNote;

        return $this;
    }

    /**
     * Get hasPromisoryNote
     *
     * @return integer
     */
    public function getHasPromisoryNote()
    {
        return $this->hasPromisoryNote;
    }

    /**
     * Set hasBillStatement
     *
     * @param integer $hasBillStatement
     *
     * @return FinancialMedRequirements
     */
    public function setHasBillStatement($hasBillStatement)
    {
        $this->hasBillStatement = $hasBillStatement;

        return $this;
    }

    /**
     * Get hasBillStatement
     *
     * @return integer
     */
    public function getHasBillStatement()
    {
        return $this->hasBillStatement;
    }

    /**
     * Set hasPriceQuot
     *
     * @param integer $hasPriceQuot
     *
     * @return FinancialMedRequirements
     */
    public function setHasPriceQuot($hasPriceQuot)
    {
        $this->hasPriceQuot = $hasPriceQuot;

        return $this;
    }

    /**
     * Get hasPriceQuot
     *
     * @return integer
     */
    public function getHasPriceQuot()
    {
        return $this->hasPriceQuot;
    }

    /**
     * Set hasReqOfPhysician
     *
     * @param integer $hasReqOfPhysician
     *
     * @return FinancialMedRequirements
     */
    public function setHasReqOfPhysician($hasReqOfPhysician)
    {
        $this->hasReqOfPhysician = $hasReqOfPhysician;

        return $this;
    }

    /**
     * Get hasReqOfPhysician
     *
     * @return integer
     */
    public function getHasReqOfPhysician()
    {
        return $this->hasReqOfPhysician;
    }

    /**
     * Set hasReseta
     *
     * @param integer $hasReseta
     *
     * @return FinancialMedRequirements
     */
    public function setHasReseta($hasReseta)
    {
        $this->hasReseta = $hasReseta;

        return $this;
    }

    /**
     * Get hasReseta
     *
     * @return integer
     */
    public function getHasReseta()
    {
        return $this->hasReseta;
    }

    /**
     * Set hasSocialCastReport
     *
     * @param integer $hasSocialCastReport
     *
     * @return FinancialMedRequirements
     */
    public function setHasSocialCastReport($hasSocialCastReport)
    {
        $this->hasSocialCastReport = $hasSocialCastReport;

        return $this;
    }

    /**
     * Get hasSocialCastReport
     *
     * @return integer
     */
    public function getHasSocialCastReport()
    {
        return $this->hasSocialCastReport;
    }

    /**
     * Set hasPoliceReport
     *
     * @param integer $hasPoliceReport
     *
     * @return FinancialMedRequirements
     */
    public function setHasPoliceReport($hasPoliceReport)
    {
        $this->hasPoliceReport = $hasPoliceReport;

        return $this;
    }

    /**
     * Get hasPoliceReport
     *
     * @return integer
     */
    public function getHasPoliceReport()
    {
        return $this->hasPoliceReport;
    }

    /**
     * Set hasDeathCert
     *
     * @param integer $hasDeathCert
     *
     * @return FinancialMedRequirements
     */
    public function setHasDeathCert($hasDeathCert)
    {
        $this->hasDeathCert = $hasDeathCert;

        return $this;
    }

    /**
     * Get hasDeathCert
     *
     * @return integer
     */
    public function getHasDeathCert()
    {
        return $this->hasDeathCert;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return FinancialMedRequirements
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
     * @return FinancialMedRequirements
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
     * @return FinancialMedRequirements
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
     * Set remarks
     *
     * @param string $remarks
     *
     * @return FinancialMedRequirements
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
     * Set isDswdMedical
     *
     * @param integer $isDswdMedical
     *
     * @return FinancialMedRequirements
     */
    public function setIsDswdMedical($isDswdMedical)
    {
        $this->isDswdMedical = $isDswdMedical;

        return $this;
    }

    /**
     * Get isDswdMedical
     *
     * @return integer
     */
    public function getIsDswdMedical()
    {
        return $this->isDswdMedical;
    }

    /**
     * Set isDswdOpd
     *
     * @param integer $isDswdOpd
     *
     * @return FinancialMedRequirements
     */
    public function setIsDswdOpd($isDswdOpd)
    {
        $this->isDswdOpd = $isDswdOpd;

        return $this;
    }

    /**
     * Get isDswdOpd
     *
     * @return integer
     */
    public function getIsDswdOpd()
    {
        return $this->isDswdOpd;
    }

    /**
     * Set isDohMaipMedical
     *
     * @param integer $isDohMaipMedical
     *
     * @return FinancialMedRequirements
     */
    public function setIsDohMaipMedical($isDohMaipMedical)
    {
        $this->isDohMaipMedical = $isDohMaipMedical;

        return $this;
    }

    /**
     * Get isDohMaipMedical
     *
     * @return integer
     */
    public function getIsDohMaipMedical()
    {
        return $this->isDohMaipMedical;
    }

    /**
     * Set isDohMaipOpd
     *
     * @param integer $isDohMaipOpd
     *
     * @return FinancialMedRequirements
     */
    public function setIsDohMaipOpd($isDohMaipOpd)
    {
        $this->isDohMaipOpd = $isDohMaipOpd;

        return $this;
    }

    /**
     * Get isDohMaipOpd
     *
     * @return integer
     */
    public function getIsDohMaipOpd()
    {
        return $this->isDohMaipOpd;
    }
}
