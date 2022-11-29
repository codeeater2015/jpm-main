<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * FieldUploadHdr
 *
 * @ORM\Table(name="tbl_field_upload_hdr")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FieldUploadHdrRepository")
 * @UniqueEntity(fields={"id"},message="This id already exists.", errorPath="id")
 */
class FieldUploadHdr
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
     * @ORM\Column(name="username", type="string", length=150)
     * @Assert\NotBlank()
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="upload_date", type="string", length=30)
     * @Assert\NotBlank()
     */
    private $uploadDate;

    /**
     * @var string
     *
     * @ORM\Column(name="municipality_name", type="string", length=150)
     */
    private $municipalityName;

    /**
     * @var string
     *
     * @ORM\Column(name="brgy_no", type="string", length=30)
     */
    private $brgyNo;

    /**
     * @var string
     *
     * @ORM\Column(name="voter_group", type="string", length=30)
     */
    private $voterGroup;

    /**
     * @var datetime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Assert\NotBlank()
     */
    private $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="remarks", type="string", length=150)
     */
    private $remarks;
    
    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=3)
     * @Assert\NotBlank()
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
     * Set username
     *
     * @param string $username
     *
     * @return FieldUploadHdr
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set uploadDate
     *
     * @param string $uploadDate
     *
     * @return FieldUploadHdr
     */
    public function setUploadDate($uploadDate)
    {
        $this->uploadDate = $uploadDate;

        return $this;
    }

    /**
     * Get uploadDate
     *
     * @return string
     */
    public function getUploadDate()
    {
        return $this->uploadDate;
    }

    /**
     * Set municipalityName
     *
     * @param string $municipalityName
     *
     * @return FieldUploadHdr
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return FieldUploadHdr
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
     * Set remarks
     *
     * @param string $remarks
     *
     * @return FieldUploadHdr
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
     * @return FieldUploadHdr
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
     * Set brgyNo
     *
     * @param string $brgyNo
     *
     * @return FieldUploadHdr
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
     * Set voterGroup
     *
     * @param string $voterGroup
     *
     * @return FieldUploadHdr
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
}
