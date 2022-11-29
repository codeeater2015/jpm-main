<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * ProjectEventHeader
 *
 * @ORM\Table(name="tbl_project_event_header")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProjectEventHeaderRepository")
 * @UniqueEntity(fields={"eventName","proId"},message="This event already exists.", errorPath="eventName")
 */
class ProjectEventHeader
{
    /**
     * @var int
     *
     * @ORM\Column(name="event_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $eventId;

     /**
     * @var string
     *
     * @ORM\Column(name="event_name", type="string", length=150)
     * @Assert\NotBlank()
     */
    private $eventName;

    /**
     * @var string
     *
     * @ORM\Column(name="event_desc", type="string", length=256)
     */
    private $eventDesc;

    /**
     * @var integer
     *
     * @ORM\Column(name="pro_id", type="integer")
     * @Assert\NotBlank()
     */
    private $proId;

     /**
     * @var datetime
     *
     * @ORM\Column(name="event_date", type="datetime")
     * @Assert\NotBlank()
     */
    private $eventDate;

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
     * Get eventId
     *
     * @return integer
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * Set eventName
     *
     * @param string $eventName
     *
     * @return ProjectEventHeader
     */
    public function setEventName($eventName)
    {
        $this->eventName = $eventName;
    
        return $this;
    }

    /**
     * Get eventName
     *
     * @return string
     */
    public function getEventName()
    {
        return $this->eventName;
    }

    /**
     * Set eventDesc
     *
     * @param string $eventDesc
     *
     * @return ProjectEventHeader
     */
    public function setEventDesc($eventDesc)
    {
        $this->eventDesc = $eventDesc;
    
        return $this;
    }

    /**
     * Get eventDesc
     *
     * @return string
     */
    public function getEventDesc()
    {
        return $this->eventDesc;
    }

    /**
     * Set proId
     *
     * @param integer $proId
     *
     * @return ProjectEventHeader
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
     * Set eventDate
     *
     * @param \DateTime $eventDate
     *
     * @return ProjectEventHeader
     */
    public function setEventDate($eventDate)
    {
        $this->eventDate = $eventDate;
    
        return $this;
    }

    /**
     * Get eventDate
     *
     * @return \DateTime
     */
    public function getEventDate()
    {
        return $this->eventDate;
    }

    /**
     * Set remarks
     *
     * @param string $remarks
     *
     * @return ProjectEventHeader
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
     * @return ProjectEventHeader
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
