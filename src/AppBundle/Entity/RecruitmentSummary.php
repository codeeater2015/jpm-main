<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * RecruitmentSummary
 *
 * @ORM\Table(name="tbl_recruitment_summary")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RecruitmentSummaryRepository")
 * @UniqueEntity(fields={"provinceCode","municipalityNo", "brgyNo", "precinctNo" },message="This precinct already exist.",errorPath="precinctNo")
 */
class RecruitmentSummary
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
     * @var int
     *
     * @ORM\Column(name="elect_id", type="integer")
     * @Assert\NotBlank()
     */
    private $electId;


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
     * @ORM\Column(name="province_code", type="string", length=15)
     * @Assert\NotBlank()
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
     * @Assert\NotBlank()
     */
    private $brgyNo;

    /**
     * @var string
     *
     * @ORM\Column(name="precinct_no", type="string", length=30)
     * @Assert\NotBlank()
     */
    private $precinctNo;

    /**
     * @var int
     *
     * @ORM\Column(name="total_recruits", type="integer")
     */
    private $totalRecruits;

    /**
     * @var int
     *
     * @ORM\Column(name="total_has_cellphone", type="integer")
     */
    private $totalHasCellphone;

    /**
     * @var int
     *
     * @ORM\Column(name="total_is_1", type="integer")
     */
    private $totalIs1;

     /**
     * @var int
     *
     * @ORM\Column(name="total_is_2", type="integer")
     */
    private $totalIs2;

     /**
     * @var int
     *
     * @ORM\Column(name="total_is_3", type="integer")
     */
    private $totalIs3;

    /**
     * @var int
     *
     * @ORM\Column(name="total_is_4", type="integer")
     */
    private $totalIs4;

    /**
     * @var int
     *
     * @ORM\Column(name="total_is_5", type="integer")
     */
    private $totalIs5;

    /**
     * @var int
     *
     * @ORM\Column(name="total_is_6", type="integer")
     */
    private $totalIs6;

    /**
     * @var int
     *
     * @ORM\Column(name="total_is_7", type="integer")
     */
    private $totalIs7;

     /**
     * @var datetime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */

    private $createdAt;

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
     * Set electId
     *
     * @param integer $electId
     *
     * @return RecruitmentSummary
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
     * Set proId
     *
     * @param integer $proId
     *
     * @return RecruitmentSummary
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
     * Set provinceCode
     *
     * @param string $provinceCode
     *
     * @return RecruitmentSummary
     */
    public function setProvinceCode($provinceCode)
    {
        $this->provinceCode = $provinceCode;

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

    /**
     * Set municipalityNo
     *
     * @param string $municipalityNo
     *
     * @return RecruitmentSummary
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
     * @return RecruitmentSummary
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
     * Set precinctNo
     *
     * @param string $precinctNo
     *
     * @return RecruitmentSummary
     */
    public function setPrecinctNo($precinctNo)
    {
        $this->precinctNo = $precinctNo;

        return $this;
    }

    /**
     * Get precinctNo
     *
     * @return string
     */
    public function getPrecinctNo()
    {
        return $this->precinctNo;
    }

    /**
     * Set totalRecruits
     *
     * @param integer $totalRecruits
     *
     * @return RecruitmentSummary
     */
    public function setTotalRecruits($totalRecruits)
    {
        $this->totalRecruits = $totalRecruits;

        return $this;
    }

    /**
     * Get totalRecruits
     *
     * @return integer
     */
    public function getTotalRecruits()
    {
        return $this->totalRecruits;
    }

    /**
     * Set totalHasCellphone
     *
     * @param integer $totalHasCellphone
     *
     * @return RecruitmentSummary
     */
    public function setTotalHasCellphone($totalHasCellphone)
    {
        $this->totalHasCellphone = $totalHasCellphone;

        return $this;
    }

    /**
     * Get totalHasCellphone
     *
     * @return integer
     */
    public function getTotalHasCellphone()
    {
        return $this->totalHasCellphone;
    }

    /**
     * Set totalIs1
     *
     * @param integer $totalIs1
     *
     * @return RecruitmentSummary
     */
    public function setTotalIs1($totalIs1)
    {
        $this->totalIs1 = $totalIs1;

        return $this;
    }

    /**
     * Get totalIs1
     *
     * @return integer
     */
    public function getTotalIs1()
    {
        return $this->totalIs1;
    }

    /**
     * Set totalIs2
     *
     * @param integer $totalIs2
     *
     * @return RecruitmentSummary
     */
    public function setTotalIs2($totalIs2)
    {
        $this->totalIs2 = $totalIs2;

        return $this;
    }

    /**
     * Get totalIs2
     *
     * @return integer
     */
    public function getTotalIs2()
    {
        return $this->totalIs2;
    }

    /**
     * Set totalIs3
     *
     * @param integer $totalIs3
     *
     * @return RecruitmentSummary
     */
    public function setTotalIs3($totalIs3)
    {
        $this->totalIs3 = $totalIs3;

        return $this;
    }

    /**
     * Get totalIs3
     *
     * @return integer
     */
    public function getTotalIs3()
    {
        return $this->totalIs3;
    }

    /**
     * Set totalIs4
     *
     * @param integer $totalIs4
     *
     * @return RecruitmentSummary
     */
    public function setTotalIs4($totalIs4)
    {
        $this->totalIs4 = $totalIs4;

        return $this;
    }

    /**
     * Get totalIs4
     *
     * @return integer
     */
    public function getTotalIs4()
    {
        return $this->totalIs4;
    }

    /**
     * Set totalIs5
     *
     * @param integer $totalIs5
     *
     * @return RecruitmentSummary
     */
    public function setTotalIs5($totalIs5)
    {
        $this->totalIs5 = $totalIs5;

        return $this;
    }

    /**
     * Get totalIs5
     *
     * @return integer
     */
    public function getTotalIs5()
    {
        return $this->totalIs5;
    }

    /**
     * Set totalIs6
     *
     * @param integer $totalIs6
     *
     * @return RecruitmentSummary
     */
    public function setTotalIs6($totalIs6)
    {
        $this->totalIs6 = $totalIs6;

        return $this;
    }

    /**
     * Get totalIs6
     *
     * @return integer
     */
    public function getTotalIs6()
    {
        return $this->totalIs6;
    }

    /**
     * Set totalIs7
     *
     * @param integer $totalIs7
     *
     * @return RecruitmentSummary
     */
    public function setTotalIs7($totalIs7)
    {
        $this->totalIs7 = $totalIs7;

        return $this;
    }

    /**
     * Get totalIs7
     *
     * @return integer
     */
    public function getTotalIs7()
    {
        return $this->totalIs7;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return RecruitmentSummary
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
}
