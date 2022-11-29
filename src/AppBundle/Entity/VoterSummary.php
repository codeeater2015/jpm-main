<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * VoterSummary
 *
 * @ORM\Table(name="tbl_voter_summary")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\VoterSummaryRepository")
 * @UniqueEntity(fields={"provinceCode","municipalityNo", "brgyNo", "precinctNo" },message="This precinct already exist.",errorPath="precinctNo")
 */
class VoterSummary
{
 /**
     * @var int
     *
     * @ORM\Column(name="sum_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $sumId;

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
     * @ORM\Column(name="total_voters", type="integer")
     */
    private $totalVoters;

    /**
     * @var int
     *
     * @ORM\Column(name="total_leaders", type="integer")
     */
    private $totalLeaders;

    /**
     * @var int
     *
     * @ORM\Column(name="total_members", type="integer")
     */
    private $totalMembers;

    /**
     * @var int
     *
     * @ORM\Column(name="total_recruited", type="integer")
     */
    private $totalRecruited;

     /**
     * @var int
     *
     * @ORM\Column(name="total_voted", type="integer")
     */
    private $totalVoted;

    /**
     * @var int
     *
     * @ORM\Column(name="total_voted_recruits", type="integer")
     */
    private $totalVotedRecruits;

    /**
     * @var float
     *
     * @ORM\Column(name="recruited_percentage", type="float", scale=2)
     */
    private $totalPercentage;

     /**
     * @var int
     *
     * @ORM\Column(name="total_has_cellphone", type="integer")
     */
    private $totalHasCellphone;

    /**
     * @var datetime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */

    private $updatedAt;

    /**
     * Get sumId
     *
     * @return integer
     */
    public function getSumId()
    {
        return $this->sumId;
    }

    /**
     * Set provinceCode
     *
     * @param string $provinceCode
     *
     * @return VoterSummary
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
     * @return VoterSummary
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
     * @return VoterSummary
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
     * @return VoterSummary
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
     * Set totalVoters
     *
     * @param integer $totalVoters
     *
     * @return VoterSummary
     */
    public function setTotalVoters($totalVoters)
    {
        $this->totalVoters = $totalVoters;

        return $this;
    }

    /**
     * Get totalVoters
     *
     * @return integer
     */
    public function getTotalVoters()
    {
        return $this->totalVoters;
    }

    /**
     * Set totalLeaders
     *
     * @param integer $totalLeaders
     *
     * @return VoterSummary
     */
    public function setTotalLeaders($totalLeaders)
    {
        $this->totalLeaders = $totalLeaders;

        return $this;
    }

    /**
     * Get totalLeaders
     *
     * @return integer
     */
    public function getTotalLeaders()
    {
        return $this->totalLeaders;
    }

    /**
     * Set totalMembers
     *
     * @param integer $totalMembers
     *
     * @return VoterSummary
     */
    public function setTotalMembers($totalMembers)
    {
        $this->totalMembers = $totalMembers;

        return $this;
    }

    /**
     * Get totalMembers
     *
     * @return integer
     */
    public function getTotalMembers()
    {
        return $this->totalMembers;
    }

    /**
     * Set totalRecruited
     *
     * @param integer $totalRecruited
     *
     * @return VoterSummary
     */
    public function setTotalRecruited($totalRecruited)
    {
        $this->totalRecruited = $totalRecruited;

        return $this;
    }

    /**
     * Get totalRecruited
     *
     * @return integer
     */
    public function getTotalRecruited()
    {
        return $this->totalRecruited;
    }

    /**
     * Set totalPercentage
     *
     * @param float $totalPercentage
     *
     * @return VoterSummary
     */
    public function setTotalPercentage($totalPercentage)
    {
        $this->totalPercentage = $totalPercentage;

        return $this;
    }

    /**
     * Get totalPercentage
     *
     * @return float
     */
    public function getTotalPercentage()
    {
        return $this->totalPercentage;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return VoterSummary
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
     * Set totalVoted
     *
     * @param integer $totalVoted
     *
     * @return VoterSummary
     */
    public function setTotalVoted($totalVoted)
    {
        $this->totalVoted = $totalVoted;

        return $this;
    }

    /**
     * Get totalVoted
     *
     * @return integer
     */
    public function getTotalVoted()
    {
        return $this->totalVoted;
    }

    /**
     * Set totalVotedRecruits
     *
     * @param integer $totalVotedRecruits
     *
     * @return VoterSummary
     */
    public function setTotalVotedRecruits($totalVotedRecruits)
    {
        $this->totalVotedRecruits = $totalVotedRecruits;

        return $this;
    }

    /**
     * Get totalVotedRecruits
     *
     * @return integer
     */
    public function getTotalVotedRecruits()
    {
        return $this->totalVotedRecruits;
    }

    /**
     * Set totalHasCellphone
     *
     * @param integer $totalHasCellphone
     *
     * @return VoterSummary
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
     * Set electId
     *
     * @param integer $electId
     *
     * @return VoterSummary
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
     * @return VoterSummary
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
}
