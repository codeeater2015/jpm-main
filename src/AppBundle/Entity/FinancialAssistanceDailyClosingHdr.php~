<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * FinancialAssistanceDailyClosingHdr
 *
 * @ORM\Table(name="tbl_fa_daily_closing_hdr")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FinancialAssistanceDailyClosingHdrRepository")
 * @UniqueEntity(fields={"closingDate"},message="This closing date already exists.", errorPath="closingDate")
 */
class FinancialAssistanceDailyClosingHdr
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
     * @ORM\Column(name="closing_date", type="string")
     * @Assert\NotBlank()
     */
    private $closingDate;

     /**
     * @var float
     *
     * @ORM\Column(name="released_amt", type="float", scale=2)
     */
    private $releasedAmt;

    /**
     * @var float
     *
     * @ORM\Column(name="pending_amt", type="float", scale=2)
     */
    private $pendingAmt;

    /**
     * @var int
     *
     * @ORM\Column(name="total_released", type="integer")
     */
    private $totalReleased;

    /**
     * @var int
     *
     * @ORM\Column(name="total_pending", type="integer")
     */
    private $totalPending;
    
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set closingDate
     *
     * @param string $closingDate
     *
     * @return FinancialAssistanceDailyClosingHdr
     */
    public function setClosingDate($closingDate)
    {
        $this->closingDate = $closingDate;

        return $this;
    }

    /**
     * Get closingDate
     *
     * @return string
     */
    public function getClosingDate()
    {
        return $this->closingDate;
    }

    /**
     * Set releasedAmt
     *
     * @param float $releasedAmt
     *
     * @return FinancialAssistanceDailyClosingHdr
     */
    public function setReleasedAmt($releasedAmt)
    {
        $this->releasedAmt = $releasedAmt;

        return $this;
    }

    /**
     * Get releasedAmt
     *
     * @return float
     */
    public function getReleasedAmt()
    {
        return $this->releasedAmt;
    }

    /**
     * Set pendingAmt
     *
     * @param float $pendingAmt
     *
     * @return FinancialAssistanceDailyClosingHdr
     */
    public function setPendingAmt($pendingAmt)
    {
        $this->pendingAmt = $pendingAmt;

        return $this;
    }

    /**
     * Get pendingAmt
     *
     * @return float
     */
    public function getPendingAmt()
    {
        return $this->pendingAmt;
    }

    /**
     * Set totalReleased
     *
     * @param integer $totalReleased
     *
     * @return FinancialAssistanceDailyClosingHdr
     */
    public function setTotalReleased($totalReleased)
    {
        $this->totalReleased = $totalReleased;

        return $this;
    }

    /**
     * Get totalReleased
     *
     * @return integer
     */
    public function getTotalReleased()
    {
        return $this->totalReleased;
    }

    /**
     * Set totalPending
     *
     * @param integer $totalPending
     *
     * @return FinancialAssistanceDailyClosingHdr
     */
    public function setTotalPending($totalPending)
    {
        $this->totalPending = $totalPending;

        return $this;
    }

    /**
     * Get totalPending
     *
     * @return integer
     */
    public function getTotalPending()
    {
        return $this->totalPending;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return FinancialAssistanceDailyClosingHdr
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
     * @return FinancialAssistanceDailyClosingHdr
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
     * @return FinancialAssistanceDailyClosingHdr
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
     * @return FinancialAssistanceDailyClosingHdr
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
