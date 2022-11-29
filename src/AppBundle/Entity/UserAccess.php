<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * UserAccess
 *
 * @ORM\Table(name="tbl_user_access")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserAccessRepository")
 * @UniqueEntity(fields={"userId", "provinceCode" , "municipalityNo", "brgyNo"},message="This barangay already exists.",errorPath="brgyNo")
 */

class UserAccess
{
    /**
     * @var int
     *
     * @ORM\Column(name="access_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $accessId;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer")
     * @Assert\NotBlank()
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="province_code", type="string", length=15)
     */
    private $provinceCode;

    /**
     * @var string
     *
     * @ORM\Column(name="municipality_no", type="string", length=15)
     * @Assert\NotBlank()
     */
    private $municipalityNo;

    /**
     * @var string
     *
     * @ORM\Column(name="brgy_no", type="string", length=15)
     */
    private $brgyNo;

    /**
     * @var datetime
     *
     * @ORM\Column(name="valid_until", type="datetime")
     */
    private $validUntil;

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
     * Get accessId
     *
     * @return integer
     */
    public function getAccessId()
    {
        return $this->accessId;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     *
     * @return UserAccess
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set municipalityNo
     *
     * @param string $municipalityNo
     *
     * @return UserAccess
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
     * Set brgyNo
     *
     * @param string $brgyNo
     *
     * @return UserAccess
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
     * Set remarks
     *
     * @param string $remarks
     *
     * @return UserAccess
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
     * @return UserAccess
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
     * Set validUntil
     *
     * @param \DateTime $validUntil
     *
     * @return UserAccess
     */
    public function setValidUntil($validUntil)
    {
        $this->validUntil = $validUntil;

        return $this;
    }

    /**
     * Get validUntil
     *
     * @return \DateTime
     */
    public function getValidUntil()
    {
        return $this->validUntil;
    }

    /**
     * Set provinceCode
     *
     * @param string $provinceCode
     *
     * @return UserAccess
     */
    public function setProvinceCode($provinceCode)
    {
        $this->provinceCode = empty($provinceCode) ? 53 : $provinceCode;

        return $this;
    }

    /**
     * Get provinceCode
     *
     * @return string
     */
    public function getProvinceCode()
    {
        return $this->provinceCode;
    }
}
