<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * PhotoUploadSummary
 *
 * @ORM\Table(name="tbl_photo_upload_summary")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PhotoUploadSummaryRepository")
 * @UniqueEntity(fields={"municipalityNo","brgyNo", "sumDate"}, message="Conflicting summary.",errorPath="brgyNo")
 */
class PhotoUploadSummary
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
     * @var string
     *
     * @ORM\Column(name="municipality_no", type="string", length=30)
     * @Assert\NotBlank()
     */
    private $municipalityNo;

    /**
     * @var string
     *
     * @ORM\Column(name="municipality_name", type="string", length=80)
     * @Assert\NotBlank()
     */
    private $municipalityName;

     /**
     * @var string
     *
     * @ORM\Column(name="brgy_no", type="string", length=30)
     * @Assert\NotBlank()
     */
    private $brgyNo;

    /**
     * @var string
     *
     * @ORM\Column(name="brgy_name", type="string", length=80)
     * @Assert\NotBlank()
     */
    private $brgyName;

    /**
     * @var string
     *
     * @ORM\Column(name="sum_date", type="string", length=15)
     * @Assert\NotBlank()
     */
    private $sumDate;

    /**
     * @var string
     *
     * @ORM\Column(name="voter_group", type="string", length=15)
     * @Assert\NotBlank()
     */
    private $voterGroup;

     /**
     * @var int
     *
     * @ORM\Column(name="total_uploads", type="integer")
     */
    private $totalUploads;

    /**
     * @var int
     *
     * @ORM\Column(name="total_linked", type="integer")
     */
    private $totalLinked;

    
    /**
     * @var int
     *
     * @ORM\Column(name="total_unlinked", type="integer")
     */
    private $totalUnlinked;

    
    /**
     * @var int
     *
     * @ORM\Column(name="total_has_photo", type="integer")
     */
    private $totalHasPhoto;

    
    /**
     * @var int
     *
     * @ORM\Column(name="total_has_id", type="integer")
     */
    private $totalHasId;

    
    /**
     * @var int
     *
     * @ORM\Column(name="total_for_printing", type="integer")
     */
    private $totalForPrinting;

    /**
     * @var int
     *
     * @ORM\Column(name="total_precincts", type="integer")
     */
    private $totalPrecincts;

    /**
     * @var string
     *
     * @ORM\Column(name="remarks", type="string", length=150)
     */
    private $remarks;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=30)
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
     * Set municipalityNo
     *
     * @param string $municipalityNo
     *
     * @return PhotoUploadSummary
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
     * @return PhotoUploadSummary
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
     * Set brgyNo
     *
     * @param string $brgyNo
     *
     * @return PhotoUploadSummary
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
     * Set brgyName
     *
     * @param string $brgyName
     *
     * @return PhotoUploadSummary
     */
    public function setBrgyName($brgyName)
    {
        $this->brgyName = $brgyName;

        return $this;
    }

    /**
     * Get brgyName
     *
     * @return string
     */
    public function getBrgyName()
    {
        return $this->brgyName;
    }

    /**
     * Set sumDate
     *
     * @param string $sumDate
     *
     * @return PhotoUploadSummary
     */
    public function setSumDate($sumDate)
    {
        $this->sumDate = $sumDate;

        return $this;
    }

    /**
     * Get sumDate
     *
     * @return string
     */
    public function getSumDate()
    {
        return $this->sumDate;
    }

    /**
     * Set voterGroup
     *
     * @param string $voterGroup
     *
     * @return PhotoUploadSummary
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
     * Set totalUploads
     *
     * @param integer $totalUploads
     *
     * @return PhotoUploadSummary
     */
    public function setTotalUploads($totalUploads)
    {
        $this->totalUploads = $totalUploads;

        return $this;
    }

    /**
     * Get totalUploads
     *
     * @return integer
     */
    public function getTotalUploads()
    {
        return $this->totalUploads;
    }

    /**
     * Set totalLinked
     *
     * @param integer $totalLinked
     *
     * @return PhotoUploadSummary
     */
    public function setTotalLinked($totalLinked)
    {
        $this->totalLinked = $totalLinked;

        return $this;
    }

    /**
     * Get totalLinked
     *
     * @return integer
     */
    public function getTotalLinked()
    {
        return $this->totalLinked;
    }

    /**
     * Set totalUnlinked
     *
     * @param integer $totalUnlinked
     *
     * @return PhotoUploadSummary
     */
    public function setTotalUnlinked($totalUnlinked)
    {
        $this->totalUnlinked = $totalUnlinked;

        return $this;
    }

    /**
     * Get totalUnlinked
     *
     * @return integer
     */
    public function getTotalUnlinked()
    {
        return $this->totalUnlinked;
    }

    /**
     * Set totalHasPhoto
     *
     * @param integer $totalHasPhoto
     *
     * @return PhotoUploadSummary
     */
    public function setTotalHasPhoto($totalHasPhoto)
    {
        $this->totalHasPhoto = $totalHasPhoto;

        return $this;
    }

    /**
     * Get totalHasPhoto
     *
     * @return integer
     */
    public function getTotalHasPhoto()
    {
        return $this->totalHasPhoto;
    }

    /**
     * Set totalHasId
     *
     * @param integer $totalHasId
     *
     * @return PhotoUploadSummary
     */
    public function setTotalHasId($totalHasId)
    {
        $this->totalHasId = $totalHasId;

        return $this;
    }

    /**
     * Get totalHasId
     *
     * @return integer
     */
    public function getTotalHasId()
    {
        return $this->totalHasId;
    }

    /**
     * Set totalForPrinting
     *
     * @param integer $totalForPrinting
     *
     * @return PhotoUploadSummary
     */
    public function setTotalForPrinting($totalForPrinting)
    {
        $this->totalForPrinting = $totalForPrinting;

        return $this;
    }

    /**
     * Get totalForPrinting
     *
     * @return integer
     */
    public function getTotalForPrinting()
    {
        return $this->totalForPrinting;
    }

    /**
     * Set totalPrecincts
     *
     * @param integer $totalPrecincts
     *
     * @return PhotoUploadSummary
     */
    public function setTotalPrecincts($totalPrecincts)
    {
        $this->totalPrecincts = $totalPrecincts;

        return $this;
    }

    /**
     * Get totalPrecincts
     *
     * @return integer
     */
    public function getTotalPrecincts()
    {
        return $this->totalPrecincts;
    }

    /**
     * Set remarks
     *
     * @param string $remarks
     *
     * @return PhotoUploadSummary
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
     * @return PhotoUploadSummary
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
