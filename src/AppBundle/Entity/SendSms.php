<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * SendSms
 *
 * @ORM\Table(name="tbl_send_sms")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SendSmsRepository")
 */

class SendSms
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
     * @return SendSms
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
     * @return SendSms
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
     * @return SendSms
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
}
