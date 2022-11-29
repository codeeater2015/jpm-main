<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * SmsTemplate
 *
 * @ORM\Table(name="tbl_sms_template")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SmsTemplateRepository")
 */
class SmsTemplate
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
     * @ORM\Column(name="template_name", type="string", length=150)
     * @Assert\NotBlank()
     */

    private $templateName;

    /**
     * @var string
     *
     * @ORM\Column(name="template_content", type="string")
     */

    private $templateContent;

    /**
     * @var string
     *
     * @ORM\Column(name="remarks", type="string", length=250)
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
     * Set templateName
     *
     * @param string $templateName
     *
     * @return SmsTemplate
     */
    public function setTemplateName($templateName)
    {
        $this->templateName = $templateName;

        return $this;
    }

    /**
     * Get templateName
     *
     * @return string
     */
    public function getTemplateName()
    {
        return $this->templateName;
    }

    /**
     * Set templateContent
     *
     * @param string $templateContent
     *
     * @return SmsTemplate
     */
    public function setTemplateContent($templateContent)
    {
        $this->templateContent = $templateContent;

        return $this;
    }

    /**
     * Get templateContent
     *
     * @return string
     */
    public function getTemplateContent()
    {
        return $this->templateContent;
    }

    /**
     * Set remarks
     *
     * @param string $remarks
     *
     * @return SmsTemplate
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
     * @return SmsTemplate
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
