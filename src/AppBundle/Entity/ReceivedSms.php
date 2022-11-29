<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ReceivedSms
 *
 * @ORM\Table(name="received_sms")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ReceivedSmsRepository")
 */
class ReceivedSms
{
    /**
     * @var int
     *
     * @ORM\Column(name="Id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="MessageTo", type="string", length=80)
     * @Assert\NotBlank()
     */

    private $messageTo;

    /**
     * @var string
     *
     * @ORM\Column(name="MessageFrom", type="string", length=80)
     */

    private $messageFrom;

    /**
     * @var string
     *
     * @ORM\Column(name="MessageText", type="string")
     * @Assert\NotBlank()
     */

    private $messageText;

    /**
     * @var datetime
     *
     * @ORM\Column(name="SendTime", type="datetime")
     */

    private $sendTime;

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
     * Set messageTo
     *
     * @param string $messageTo
     *
     * @return ReceivedSms
     */
    public function setMessageTo($messageTo)
    {
        $this->messageTo = $messageTo;

        return $this;
    }

    /**
     * Get messageTo
     *
     * @return string
     */
    public function getMessageTo()
    {
        return $this->messageTo;
    }

    /**
     * Set messageFrom
     *
     * @param string $messageFrom
     *
     * @return ReceivedSms
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
     * @return ReceivedSms
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
     * Set sendTime
     *
     * @param \DateTime $sendTime
     *
     * @return ReceivedSms
     */
    public function setSendTime($sendTime)
    {
        $this->sendTime = $sendTime;

        return $this;
    }

    /**
     * Get sendTime
     *
     * @return \DateTime
     */
    public function getSendTime()
    {
        return $this->sendTime;
    }
}
