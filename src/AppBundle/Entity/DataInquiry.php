<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * DataInquiry
 *
 * @ORM\Table(name="tbl_data_inquiry")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DataInquiryRepository")
 */
class DataInquiry
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
     * @ORM\Column(name="message_from", type="string", length=255)
     */
    private $messageFrom;

    /**
     * @var string
     *
     * @ORM\Column(name="message_text", type="string", length=255)
     */
    private $messageText;

    /**
     * @var string
     *
     * @ORM\Column(name="source_pro_id_code", type="string", length=10)
     */
    private $sourceProIdCode;

    /**
     * @var string
     *
     * @ORM\Column(name="target_pro_id_code", type="string", length=10)
     */
    private $targetProIdCode;
    
    /**
     * @var datetime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=10)
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
     * Set messageFrom
     *
     * @param string $messageFrom
     *
     * @return DataInquiry
     */
    public function setMessageFrom($messageFrom)
    {
        $this->messageFrom = $messageFrom;

        return $this;
    }

    /**
     * Get messageFrom
     *
     * @return string
     */
    public function getMessageFrom()
    {
        return $this->messageFrom;
    }

    /**
     * Set messageText
     *
     * @param string $messageText
     *
     * @return DataInquiry
     */
    public function setMessageText($messageText)
    {
        $this->messageText = $messageText;

        return $this;
    }

    /**
     * Get messageText
     *
     * @return string
     */
    public function getMessageText()
    {
        return $this->messageText;
    }

    /**
     * Set sourceProIdCode
     *
     * @param string $sourceProIdCode
     *
     * @return DataInquiry
     */
    public function setSourceProIdCode($sourceProIdCode)
    {
        $this->sourceProIdCode = $sourceProIdCode;

        return $this;
    }

    /**
     * Get sourceProIdCode
     *
     * @return string
     */
    public function getSourceProIdCode()
    {
        return $this->sourceProIdCode;
    }

    /**
     * Set targetProIdCode
     *
     * @param string $targetProIdCode
     *
     * @return DataInquiry
     */
    public function setTargetProIdCode($targetProIdCode)
    {
        $this->targetProIdCode = $targetProIdCode;

        return $this;
    }

    /**
     * Get targetProIdCode
     *
     * @return string
     */
    public function getTargetProIdCode()
    {
        return $this->targetProIdCode;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return DataInquiry
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
     * Set status
     *
     * @param string $status
     *
     * @return DataInquiry
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
