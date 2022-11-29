<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * DataUpdateDetail
 *
 * @ORM\Table(name="tbl_data_update_detail")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DataUpdateDetailRepository")
 * @UniqueEntity(fields={"dtlId"},message="This id has already been created",errorPath="dtlId")
 */
class DataUpdateDetail
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
     * @ORM\Column(name="pro_voter_id", type="integer")
     * @Assert\NotBlank()
     */
    private $proVoterId;

    /**
     * @var string
     *
     * @ORM\Column(name="voter_id", type="integer")
     * @Assert\NotBlank()
     */
    private $voterId;

    /**
     * @var string
     *
     * @ORM\Column(name="pro_id", type="integer")
     * @Assert\NotBlank()
     */
    private $proId;

    /**
     * @var string
     *
     * @ORM\Column(name="elect_id", type="integer")
     * @Assert\NotBlank()
     */
    private $electId;

    /**
     * @var string
     *
     * @ORM\Column(name="voter_name", type="string", length=255)
     */
    private $voterName;

    /**
     * @var string
     *
     * @ORM\Column(name="pro_id_code", type="string", length=30)
     */
    private $proIdCode;

     /**
     * @var string
     *
     * @ORM\Column(name="voter_group", type="string", length=30)
     */
    private $voterGroup;

    /**
     * @var string
     *
     * @ORM\Column(name="position", type="string", length=50)
     */
    private $position;

    /**
     * @var string
     *
     * @ORM\Column(name="cellphone", type="string", length=30)
     */
    private $cellphone;

    /**
     * @var string
     *
     * @ORM\Column(name="cellphone_b", type="string", length=30)
     */
    private $cellphoneB;

    /**
     * @var string
     *
     * @ORM\Column(name="cellphone_c", type="string", length=30)
     */
    private $cellphoneC;

    /**
     * @var int
     *
     * @ORM\Column(name="has_submitted", type="integer")
     */
    private $hasSubmitted;

    /**
     * @var string
     *
     * @ORM\Column(name="with_stub", type="integer")
     */
    private $withStub;

    /**
     * @var string
     *
     * @ORM\Column(name="has_photo", type="integer")
     */
    private $hasPhoto;
    
    /**
     * @var datetime
     *
     * @ORM\Column(name="photo_at", type="datetime")
     */
    private $photoAt;

    /**
     * @var string
     *
     * @ORM\Column(name="has_id", type="integer")
     */
    private $hasId;

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
     * @var int
     *
     * @ORM\Column(name="is_8", type="integer")
     */
    private $is8;

    /**
     * @var int
     *
     * @ORM\Column(name="is_9", type="integer")
     */
    private $is9;

    /**
     * @var int
     *
     * @ORM\Column(name="is_10", type="integer")
     */
    private $is10;
    
     /**
     * @var datetime
     *
     * @ORM\Column(name="blocked_at", type="datetime")
     */

    private $blockedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="blocked_by", type="string", length=150)
     */
    private $blockedBy;

     /**
     * @var string
     *
     * @ORM\Column(name="blocked_reason", type="string", length=255)
     */
    private $blockedReason;

     /**
     * @var datetime
     *
     * @ORM\Column(name="unblocked_at", type="datetime")
     */

    private $unblockedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="unblocked_by", type="string", length=150)
     */
    private $unblockedBy;

     /**
     * @var string
     *
     * @ORM\Column(name="unblocked_reason", type="string", length=255)
     */
    private $unblockedReason;

    /**
     * @var datetime
     *
     * @ORM\Column(name="activated_at", type="datetime")
     */

    private $activatedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="activated_by", type="string", length=150)
     */
    private $activatedBy;

     /**
     * @var string
     *
     * @ORM\Column(name="activated_reason", type="string", length=255)
     */
    private $activatedReason;

    /**
     * @var datetime
     *
     * @ORM\Column(name="deactivated_at", type="datetime")
     */

    private $deactivatedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="deactivated_by", type="string", length=150)
     */
    private $deactivatedBy;

     /**
     * @var string
     *
     * @ORM\Column(name="deactivated_reason", type="string", length=255)
     */
    private $deactivatedReason;

    /**
     * @var string
     *
     * @ORM\Column(name="pro_status", type="string", length=10)
     */
    private $proStatus;

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
     * @return DataUpdateDetail
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
     * Set proVoterId
     *
     * @param integer $proVoterId
     *
     * @return DataUpdateDetail
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
     * Set voterId
     *
     * @param integer $voterId
     *
     * @return DataUpdateDetail
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
     * Set proId
     *
     * @param integer $proId
     *
     * @return DataUpdateDetail
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
     * @return DataUpdateDetail
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
     * Set proIdCode
     *
     * @param string $proIdCode
     *
     * @return DataUpdateDetail
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
     * Set voterGroup
     *
     * @param string $voterGroup
     *
     * @return DataUpdateDetail
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
     * Set position
     *
     * @param string $position
     *
     * @return DataUpdateDetail
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

    /**
     * Set cellphone
     *
     * @param string $cellphone
     *
     * @return DataUpdateDetail
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
     * Set cellphoneB
     *
     * @param string $cellphoneB
     *
     * @return DataUpdateDetail
     */
    public function setCellphoneB($cellphoneB)
    {
        $this->cellphoneB = $cellphoneB;

        return $this;
    }

    /**
     * Get cellphoneB
     *
     * @return string
     */
    public function getCellphoneB()
    {
        return $this->cellphoneB;
    }

    /**
     * Set cellphoneC
     *
     * @param string $cellphoneC
     *
     * @return DataUpdateDetail
     */
    public function setCellphoneC($cellphoneC)
    {
        $this->cellphoneC = $cellphoneC;

        return $this;
    }

    /**
     * Get cellphoneC
     *
     * @return string
     */
    public function getCellphoneC()
    {
        return $this->cellphoneC;
    }

    /**
     * Set hasSubmitted
     *
     * @param integer $hasSubmitted
     *
     * @return DataUpdateDetail
     */
    public function setHasSubmitted($hasSubmitted)
    {
        $this->hasSubmitted = $hasSubmitted;

        return $this;
    }

    /**
     * Get hasSubmitted
     *
     * @return integer
     */
    public function getHasSubmitted()
    {
        return $this->hasSubmitted;
    }

    /**
     * Set withStub
     *
     * @param integer $withStub
     *
     * @return DataUpdateDetail
     */
    public function setWithStub($withStub)
    {
        $this->withStub = $withStub;

        return $this;
    }

    /**
     * Get withStub
     *
     * @return integer
     */
    public function getWithStub()
    {
        return $this->withStub;
    }

    /**
     * Set hasPhoto
     *
     * @param integer $hasPhoto
     *
     * @return DataUpdateDetail
     */
    public function setHasPhoto($hasPhoto)
    {
        $this->hasPhoto = $hasPhoto;

        return $this;
    }

    /**
     * Get hasPhoto
     *
     * @return integer
     */
    public function getHasPhoto()
    {
        return $this->hasPhoto;
    }

    /**
     * Set photoAt
     *
     * @param \DateTime $photoAt
     *
     * @return DataUpdateDetail
     */
    public function setPhotoAt($photoAt)
    {
        $this->photoAt = $photoAt;

        return $this;
    }

    /**
     * Get photoAt
     *
     * @return \DateTime
     */
    public function getPhotoAt()
    {
        return $this->photoAt;
    }

    /**
     * Set hasId
     *
     * @param integer $hasId
     *
     * @return DataUpdateDetail
     */
    public function setHasId($hasId)
    {
        $this->hasId = $hasId;

        return $this;
    }

    /**
     * Get hasId
     *
     * @return integer
     */
    public function getHasId()
    {
        return $this->hasId;
    }

    /**
     * Set is1
     *
     * @param integer $is1
     *
     * @return DataUpdateDetail
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
     * @return DataUpdateDetail
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
     * @return DataUpdateDetail
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
     * @return DataUpdateDetail
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
     * @return DataUpdateDetail
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
     * @return DataUpdateDetail
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
     * @return DataUpdateDetail
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
     * Set is8
     *
     * @param integer $is8
     *
     * @return DataUpdateDetail
     */
    public function setIs8($is8)
    {
        $this->is8 = $is8;

        return $this;
    }

    /**
     * Get is8
     *
     * @return integer
     */
    public function getIs8()
    {
        return $this->is8;
    }

    /**
     * Set is9
     *
     * @param integer $is9
     *
     * @return DataUpdateDetail
     */
    public function setIs9($is9)
    {
        $this->is9 = $is9;

        return $this;
    }

    /**
     * Get is9
     *
     * @return integer
     */
    public function getIs9()
    {
        return $this->is9;
    }

    /**
     * Set is10
     *
     * @param integer $is10
     *
     * @return DataUpdateDetail
     */
    public function setIs10($is10)
    {
        $this->is10 = $is10;

        return $this;
    }

    /**
     * Get is10
     *
     * @return integer
     */
    public function getIs10()
    {
        return $this->is10;
    }

    /**
     * Set proStatus
     *
     * @param string $proStatus
     *
     * @return DataUpdateDetail
     */
    public function setProStatus($proStatus)
    {
        $this->proStatus = $proStatus;

        return $this;
    }

    /**
     * Get proStatus
     *
     * @return string
     */
    public function getProStatus()
    {
        return $this->proStatus;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return DataUpdateDetail
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
     * @return DataUpdateDetail
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return DataUpdateDetail
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
     * @return DataUpdateDetail
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
     * Set remarks
     *
     * @param string $remarks
     *
     * @return DataUpdateDetail
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
     * @return DataUpdateDetail
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
     * Set voterName
     *
     * @param string $voterName
     *
     * @return DataUpdateDetail
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
     * Set blockedAt
     *
     * @param \DateTime $blockedAt
     *
     * @return DataUpdateDetail
     */
    public function setBlockedAt($blockedAt)
    {
        $this->blockedAt = $blockedAt;

        return $this;
    }

    /**
     * Get blockedAt
     *
     * @return \DateTime
     */
    public function getBlockedAt()
    {
        return $this->blockedAt;
    }

    /**
     * Set blockedBy
     *
     * @param string $blockedBy
     *
     * @return DataUpdateDetail
     */
    public function setBlockedBy($blockedBy)
    {
        $this->blockedBy = $blockedBy;

        return $this;
    }

    /**
     * Get blockedBy
     *
     * @return string
     */
    public function getBlockedBy()
    {
        return $this->blockedBy;
    }

    /**
     * Set blockedReason
     *
     * @param string $blockedReason
     *
     * @return DataUpdateDetail
     */
    public function setBlockedReason($blockedReason)
    {
        $this->blockedReason = $blockedReason;

        return $this;
    }

    /**
     * Get blockedReason
     *
     * @return string
     */
    public function getBlockedReason()
    {
        return $this->blockedReason;
    }

    /**
     * Set unblockedAt
     *
     * @param \DateTime $unblockedAt
     *
     * @return DataUpdateDetail
     */
    public function setUnblockedAt($unblockedAt)
    {
        $this->unblockedAt = $unblockedAt;

        return $this;
    }

    /**
     * Get unblockedAt
     *
     * @return \DateTime
     */
    public function getUnblockedAt()
    {
        return $this->unblockedAt;
    }

    /**
     * Set unblockedBy
     *
     * @param string $unblockedBy
     *
     * @return DataUpdateDetail
     */
    public function setUnblockedBy($unblockedBy)
    {
        $this->unblockedBy = $unblockedBy;

        return $this;
    }

    /**
     * Get unblockedBy
     *
     * @return string
     */
    public function getUnblockedBy()
    {
        return $this->unblockedBy;
    }

    /**
     * Set unblockedReason
     *
     * @param string $unblockedReason
     *
     * @return DataUpdateDetail
     */
    public function setUnblockedReason($unblockedReason)
    {
        $this->unblockedReason = $unblockedReason;

        return $this;
    }

    /**
     * Get unblockedReason
     *
     * @return string
     */
    public function getUnblockedReason()
    {
        return $this->unblockedReason;
    }

    /**
     * Set activatedAt
     *
     * @param \DateTime $activatedAt
     *
     * @return DataUpdateDetail
     */
    public function setActivatedAt($activatedAt)
    {
        $this->activatedAt = $activatedAt;

        return $this;
    }

    /**
     * Get activatedAt
     *
     * @return \DateTime
     */
    public function getActivatedAt()
    {
        return $this->activatedAt;
    }

    /**
     * Set activatedBy
     *
     * @param string $activatedBy
     *
     * @return DataUpdateDetail
     */
    public function setActivatedBy($activatedBy)
    {
        $this->activatedBy = $activatedBy;

        return $this;
    }

    /**
     * Get activatedBy
     *
     * @return string
     */
    public function getActivatedBy()
    {
        return $this->activatedBy;
    }

    /**
     * Set activatedReason
     *
     * @param string $activatedReason
     *
     * @return DataUpdateDetail
     */
    public function setActivatedReason($activatedReason)
    {
        $this->activatedReason = $activatedReason;

        return $this;
    }

    /**
     * Get activatedReason
     *
     * @return string
     */
    public function getActivatedReason()
    {
        return $this->activatedReason;
    }

    /**
     * Set deactivatedAt
     *
     * @param \DateTime $deactivatedAt
     *
     * @return DataUpdateDetail
     */
    public function setDeactivatedAt($deactivatedAt)
    {
        $this->deactivatedAt = $deactivatedAt;

        return $this;
    }

    /**
     * Get deactivatedAt
     *
     * @return \DateTime
     */
    public function getDeactivatedAt()
    {
        return $this->deactivatedAt;
    }

    /**
     * Set deactivatedBy
     *
     * @param string $deactivatedBy
     *
     * @return DataUpdateDetail
     */
    public function setDeactivatedBy($deactivatedBy)
    {
        $this->deactivatedBy = $deactivatedBy;

        return $this;
    }

    /**
     * Get deactivatedBy
     *
     * @return string
     */
    public function getDeactivatedBy()
    {
        return $this->deactivatedBy;
    }

    /**
     * Set deactivatedReason
     *
     * @param string $deactivatedReason
     *
     * @return DataUpdateDetail
     */
    public function setDeactivatedReason($deactivatedReason)
    {
        $this->deactivatedReason = $deactivatedReason;

        return $this;
    }

    /**
     * Get deactivatedReason
     *
     * @return string
     */
    public function getDeactivatedReason()
    {
        return $this->deactivatedReason;
    }
}
