<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * ProjectVoterSummary
 *
 * @ORM\Table(name="tbl_project_voter_summary")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProjectVoterSummaryRepository")
 * @UniqueEntity(fields={"provinceCode","municipalityNo", "brgyNo", "precinctNo", "createdAt" },message="This precinct already exist.",errorPath="precinctNo")
 */
class ProjectVoterSummary
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
     * @ORM\Column(name="total_member", type="integer")
     */
    private $totalMember;

    /**
     * @var int
     *
     * @ORM\Column(name="clustered_precinct", type="integer")
     * @Assert\NotBlank()
     */
    private $clusteredPrecinct;

    /**
     * @var string
     *
     * @ORM\Column(name="voting_center", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $votingCenter;

      /**
     * @var int
     *
     * @ORM\Column(name="total_has_cellphone", type="integer")
     */
    private $totalHasCellphone;

    /**
     * @var int
     *
     * @ORM\Column(name="total_level_1", type="integer")
     */
    private $totalLevel1;

     /**
     * @var int
     *
     * @ORM\Column(name="total_level_2", type="integer")
     */
    private $totalLevel2;

     /**
     * @var int
     *
     * @ORM\Column(name="total_level_3", type="integer")
     */
    private $totalLevel3;

    /**
     * @var int
     *
     * @ORM\Column(name="total_level_4", type="integer")
     */
    private $totalLevel4;

    /**
     * @var int
     *
     * @ORM\Column(name="total_level_5", type="integer")
     */
    private $totalLevel5;

    /**
     * @var int
     *
     * @ORM\Column(name="total_level_6", type="integer")
     */
    private $totalLevel6;

    /**
     * @var int
     *
     * @ORM\Column(name="total_level_7", type="integer")
     */
    private $totalLevel7;

    /**
     * @var int
     *
     * @ORM\Column(name="total_level_8", type="integer")
     */
    private $totalLevel8;


    /**
     * @var int
     *
     * @ORM\Column(name="total_level_9", type="integer")
     */
    private $totalLevel9;

    /**
     * @var int
     *
     * @ORM\Column(name="total_level_10", type="integer")
     */
    private $totalLevel10;

    /**
     * @var int
     *
     * @ORM\Column(name="total_staff", type="integer")
     */
    private $totalStaff;

    /**
     * @var int
     *
     * @ORM\Column(name="total_others", type="integer")
     */
    private $totalOthers;

    /**
     * @var int
     *
     * @ORM\Column(name="total_with_id_level_1", type="integer")
     */
    private $totalWithIdLevel1;

     /**
     * @var int
     *
     * @ORM\Column(name="total_with_id_level_2", type="integer")
     */
    private $totalWithIdLevel2;

     /**
     * @var int
     *
     * @ORM\Column(name="total_with_id_level_3", type="integer")
     */
    private $totalWithIdLevel3;

    /**
     * @var int
     *
     * @ORM\Column(name="total_with_id_level_4", type="integer")
     */
    private $totalWithIdLevel4;

    /**
     * @var int
     *
     * @ORM\Column(name="total_with_id_level_5", type="integer")
     */
    private $totalWithIdLevel5;

    /**
     * @var int
     *
     * @ORM\Column(name="total_with_id_level_6", type="integer")
     */
    private $totalWithIdLevel6;

    /**
     * @var int
     *
     * @ORM\Column(name="total_with_id_level_7", type="integer")
     */
    private $totalWithIdLevel7;

    /**
     * @var int
     *
     * @ORM\Column(name="total_with_id_level_8", type="integer")
     */
    private $totalWithIdLevel8;


    /**
     * @var int
     *
     * @ORM\Column(name="total_with_id_level_9", type="integer")
     */
    private $totalWithIdLevel9;

    /**
     * @var int
     *
     * @ORM\Column(name="total_with_id_level_10", type="integer")
     */
    private $totalWithIdLevel10;

    /**
     * @var int
     *
     * @ORM\Column(name="total_with_id_staff", type="integer")
     */
    private $totalWithIdStaff;

    /**
     * @var int
     *
     * @ORM\Column(name="total_with_id_others", type="integer")
     */
    private $totalWithIdOthers;

    /**
     * @var int
     *
     * @ORM\Column(name="total_with_id_member", type="integer")
     */
    private $totalWithIdMember;

    /**
     * @var int
     *
     * @ORM\Column(name="total_with_id_cellphone", type="integer")
     */
    private $totalWithIdCellphone;

    /**
     * @var int
     *
     * @ORM\Column(name="total_submitted", type="integer")
     */
    private $totalSubmitted;

     /**
     * @var int
     *
     * @ORM\Column(name="total_has_submitted_level_1", type="integer")
     */
    private $totalHasSubmittedLevel1;

    /**
     * @var int
     *
     * @ORM\Column(name="total_has_submitted_level_2", type="integer")
     */
    private $totalHasSubmittedLevel2;

    /**
     * @var int
     *
     * @ORM\Column(name="total_has_submitted_level_3", type="integer")
     */
    private $totalHasSubmittedLevel3;

    /**
     * @var int
     *
     * @ORM\Column(name="total_has_submitted_level_4", type="integer")
     */
    private $totalHasSubmittedLevel4;

    /**
     * @var int
     *
     * @ORM\Column(name="total_has_submitted_level_5", type="integer")
     */
    private $totalHasSubmittedLevel5;

    /**
     * @var int
     *
     * @ORM\Column(name="total_has_submitted_level_6", type="integer")
     */
    private $totalHasSubmittedLevel6;

    /**
     * @var int
     *
     * @ORM\Column(name="total_has_submitted_level_7", type="integer")
     */
    private $totalHasSubmittedLevel7;

    /**
     * @var int
     *
     * @ORM\Column(name="total_has_submitted_level_8", type="integer")
     */
    private $totalHasSubmittedLevel8;

    /**
     * @var int
     *
     * @ORM\Column(name="total_has_submitted_level_9", type="integer")
     */
    private $totalHasSubmittedLevel9;

    /**
     * @var int
     *
     * @ORM\Column(name="total_has_submitted_level_10", type="integer")
     */
    private $totalHasSubmittedLevel10;
    

    /**
     * @var int
     *
     * @ORM\Column(name="total_has_submitted_others", type="integer")
     */
    private $totalHasSubmittedOthers;

    /**
     * @var int
     *
     * @ORM\Column(name="total_has_submitted_cellphone", type="integer")
     */
    private $totalHasSubmittedCellphone;

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
     * @var int
     *
     * @ORM\Column(name="total_is_8", type="integer")
     */
    private $totalIs8;

    /**
     * @var int
     *
     * @ORM\Column(name="total_has_ast", type="integer")
     */
    private $totalHasAst;

     /**
     * @var int
     *
     * @ORM\Column(name="total_has_ast_level_1", type="integer")
     */
    private $totalHasAstLevel1;

     /**
     * @var int
     *
     * @ORM\Column(name="total_has_ast_level_2", type="integer")
     */
    private $totalHasAstLevel2;

    /**
     * @var int
     *
     * @ORM\Column(name="total_has_ast_level_3", type="integer")
     */
    private $totalHasAstLevel3;

    /**
     * @var int
     *
     * @ORM\Column(name="total_has_ast_level_4", type="integer")
     */
    private $totalHasAstLevel4;

    /**
     * @var int
     *
     * @ORM\Column(name="total_has_ast_level_5", type="integer")
     */
    private $totalHasAstLevel5;

    /**
     * @var int
     *
     * @ORM\Column(name="total_has_ast_level_6", type="integer")
     */
    private $totalHasAstLevel6;

    /**
     * @var int
     *
     * @ORM\Column(name="total_has_ast_level_7", type="integer")
     */
    private $totalHasAstLevel7;

    /**
     * @var int
     *
     * @ORM\Column(name="total_has_ast_level_8", type="integer")
     */
    private $totalHasAstLevel8;

    /**
     * @var int
     *
     * @ORM\Column(name="total_has_ast_level_9", type="integer")
     */
    private $totalHasAstLevel9;

    /**
     * @var int
     *
     * @ORM\Column(name="total_has_ast_level_10", type="integer")
     */
    private $totalHasAstLevel10;

    /**
     * @var int
     *
     * @ORM\Column(name="total_has_ast_staff", type="integer")
     */
    private $totalHasAstStaff;

    /**
     * @var int
     *
     * @ORM\Column(name="total_has_ast_others", type="integer")
     */
    private $totalHasAstOthers;

    /**
     * @var int
     *
     * @ORM\Column(name="total_has_ast_cellphone", type="integer")
     */
    private $totalHasAstCellphone;

     /**
     * @var datetime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */

    private $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="created_by", type="string")
     */
    private $createdBy;

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
     * Set electId
     *
     * @param integer $electId
     *
     * @return ProjectVoterSummary
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
     * @return ProjectVoterSummary
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
     * @return ProjectVoterSummary
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
     * @return ProjectVoterSummary
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
     * @return ProjectVoterSummary
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
     * @return ProjectVoterSummary
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
     * Set totalMember
     *
     * @param integer $totalMember
     *
     * @return ProjectVoterSummary
     */
    public function setTotalMember($totalMember)
    {
        $this->totalMember = $totalMember;

        return $this;
    }

    /**
     * Get totalMember
     *
     * @return integer
     */
    public function getTotalMember()
    {
        return $this->totalMember;
    }

    /**
     * Set totalHasCellphone
     *
     * @param integer $totalHasCellphone
     *
     * @return ProjectVoterSummary
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
     * Set totalLevel1
     *
     * @param integer $totalLevel1
     *
     * @return ProjectVoterSummary
     */
    public function setTotalLevel1($totalLevel1)
    {
        $this->totalLevel1 = $totalLevel1;

        return $this;
    }

    /**
     * Get totalLevel1
     *
     * @return integer
     */
    public function getTotalLevel1()
    {
        return $this->totalLevel1;
    }

    /**
     * Set totalLevel2
     *
     * @param integer $totalLevel2
     *
     * @return ProjectVoterSummary
     */
    public function setTotalLevel2($totalLevel2)
    {
        $this->totalLevel2 = $totalLevel2;

        return $this;
    }

    /**
     * Get totalLevel2
     *
     * @return integer
     */
    public function getTotalLevel2()
    {
        return $this->totalLevel2;
    }

    /**
     * Set totalLevel3
     *
     * @param integer $totalLevel3
     *
     * @return ProjectVoterSummary
     */
    public function setTotalLevel3($totalLevel3)
    {
        $this->totalLevel3 = $totalLevel3;

        return $this;
    }

    /**
     * Get totalLevel3
     *
     * @return integer
     */
    public function getTotalLevel3()
    {
        return $this->totalLevel3;
    }

    /**
     * Set totalLevel4
     *
     * @param integer $totalLevel4
     *
     * @return ProjectVoterSummary
     */
    public function setTotalLevel4($totalLevel4)
    {
        $this->totalLevel4 = $totalLevel4;

        return $this;
    }

    /**
     * Get totalLevel4
     *
     * @return integer
     */
    public function getTotalLevel4()
    {
        return $this->totalLevel4;
    }

    /**
     * Set totalLevel5
     *
     * @param integer $totalLevel5
     *
     * @return ProjectVoterSummary
     */
    public function setTotalLevel5($totalLevel5)
    {
        $this->totalLevel5 = $totalLevel5;

        return $this;
    }

    /**
     * Get totalLevel5
     *
     * @return integer
     */
    public function getTotalLevel5()
    {
        return $this->totalLevel5;
    }

    /**
     * Set totalLevel6
     *
     * @param integer $totalLevel6
     *
     * @return ProjectVoterSummary
     */
    public function setTotalLevel6($totalLevel6)
    {
        $this->totalLevel6 = $totalLevel6;

        return $this;
    }

    /**
     * Get totalLevel6
     *
     * @return integer
     */
    public function getTotalLevel6()
    {
        return $this->totalLevel6;
    }

    /**
     * Set totalLevel7
     *
     * @param integer $totalLevel7
     *
     * @return ProjectVoterSummary
     */
    public function setTotalLevel7($totalLevel7)
    {
        $this->totalLevel7 = $totalLevel7;

        return $this;
    }

    /**
     * Get totalLevel7
     *
     * @return integer
     */
    public function getTotalLevel7()
    {
        return $this->totalLevel7;
    }

    /**
     * Set totalLevel8
     *
     * @param integer $totalLevel8
     *
     * @return ProjectVoterSummary
     */
    public function setTotalLevel8($totalLevel8)
    {
        $this->totalLevel8 = $totalLevel8;

        return $this;
    }

    /**
     * Get totalLevel8
     *
     * @return integer
     */
    public function getTotalLevel8()
    {
        return $this->totalLevel8;
    }

    /**
     * Set totalLevel9
     *
     * @param integer $totalLevel9
     *
     * @return ProjectVoterSummary
     */
    public function setTotalLevel9($totalLevel9)
    {
        $this->totalLevel9 = $totalLevel9;

        return $this;
    }

    /**
     * Get totalLevel9
     *
     * @return integer
     */
    public function getTotalLevel9()
    {
        return $this->totalLevel9;
    }

    /**
     * Set totalLevel10
     *
     * @param integer $totalLevel10
     *
     * @return ProjectVoterSummary
     */
    public function setTotalLevel10($totalLevel10)
    {
        $this->totalLevel10 = $totalLevel10;

        return $this;
    }

    /**
     * Get totalLevel10
     *
     * @return integer
     */
    public function getTotalLevel10()
    {
        return $this->totalLevel10;
    }

    /**
     * Set totalStaff
     *
     * @param integer $totalStaff
     *
     * @return ProjectVoterSummary
     */
    public function setTotalStaff($totalStaff)
    {
        $this->totalStaff = $totalStaff;

        return $this;
    }

    /**
     * Get totalStaff
     *
     * @return integer
     */
    public function getTotalStaff()
    {
        return $this->totalStaff;
    }

    /**
     * Set totalOthers
     *
     * @param integer $totalOthers
     *
     * @return ProjectVoterSummary
     */
    public function setTotalOthers($totalOthers)
    {
        $this->totalOthers = $totalOthers;

        return $this;
    }

    /**
     * Get totalOthers
     *
     * @return integer
     */
    public function getTotalOthers()
    {
        return $this->totalOthers;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ProjectVoterSummary
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
     * Set totalWithIdLevel1
     *
     * @param integer $totalWithIdLevel1
     *
     * @return ProjectVoterSummary
     */
    public function setTotalWithIdLevel1($totalWithIdLevel1)
    {
        $this->totalWithIdLevel1 = $totalWithIdLevel1;

        return $this;
    }

    /**
     * Get totalWithIdLevel1
     *
     * @return integer
     */
    public function getTotalWithIdLevel1()
    {
        return $this->totalWithIdLevel1;
    }

    /**
     * Set totalWithIdLevel2
     *
     * @param integer $totalWithIdLevel2
     *
     * @return ProjectVoterSummary
     */
    public function setTotalWithIdLevel2($totalWithIdLevel2)
    {
        $this->totalWithIdLevel2 = $totalWithIdLevel2;

        return $this;
    }

    /**
     * Get totalWithIdLevel2
     *
     * @return integer
     */
    public function getTotalWithIdLevel2()
    {
        return $this->totalWithIdLevel2;
    }

    /**
     * Set totalWithIdLevel3
     *
     * @param integer $totalWithIdLevel3
     *
     * @return ProjectVoterSummary
     */
    public function setTotalWithIdLevel3($totalWithIdLevel3)
    {
        $this->totalWithIdLevel3 = $totalWithIdLevel3;

        return $this;
    }

    /**
     * Get totalWithIdLevel3
     *
     * @return integer
     */
    public function getTotalWithIdLevel3()
    {
        return $this->totalWithIdLevel3;
    }

    /**
     * Set totalWithIdLevel4
     *
     * @param integer $totalWithIdLevel4
     *
     * @return ProjectVoterSummary
     */
    public function setTotalWithIdLevel4($totalWithIdLevel4)
    {
        $this->totalWithIdLevel4 = $totalWithIdLevel4;

        return $this;
    }

    /**
     * Get totalWithIdLevel4
     *
     * @return integer
     */
    public function getTotalWithIdLevel4()
    {
        return $this->totalWithIdLevel4;
    }

    /**
     * Set totalWithIdLevel5
     *
     * @param integer $totalWithIdLevel5
     *
     * @return ProjectVoterSummary
     */
    public function setTotalWithIdLevel5($totalWithIdLevel5)
    {
        $this->totalWithIdLevel5 = $totalWithIdLevel5;

        return $this;
    }

    /**
     * Get totalWithIdLevel5
     *
     * @return integer
     */
    public function getTotalWithIdLevel5()
    {
        return $this->totalWithIdLevel5;
    }

    /**
     * Set totalWithIdLevel6
     *
     * @param integer $totalWithIdLevel6
     *
     * @return ProjectVoterSummary
     */
    public function setTotalWithIdLevel6($totalWithIdLevel6)
    {
        $this->totalWithIdLevel6 = $totalWithIdLevel6;

        return $this;
    }

    /**
     * Get totalWithIdLevel6
     *
     * @return integer
     */
    public function getTotalWithIdLevel6()
    {
        return $this->totalWithIdLevel6;
    }

    /**
     * Set totalWithIdLevel7
     *
     * @param integer $totalWithIdLevel7
     *
     * @return ProjectVoterSummary
     */
    public function setTotalWithIdLevel7($totalWithIdLevel7)
    {
        $this->totalWithIdLevel7 = $totalWithIdLevel7;

        return $this;
    }

    /**
     * Get totalWithIdLevel7
     *
     * @return integer
     */
    public function getTotalWithIdLevel7()
    {
        return $this->totalWithIdLevel7;
    }

    /**
     * Set totalWithIdLevel8
     *
     * @param integer $totalWithIdLevel8
     *
     * @return ProjectVoterSummary
     */
    public function setTotalWithIdLevel8($totalWithIdLevel8)
    {
        $this->totalWithIdLevel8 = $totalWithIdLevel8;

        return $this;
    }

    /**
     * Get totalWithIdLevel8
     *
     * @return integer
     */
    public function getTotalWithIdLevel8()
    {
        return $this->totalWithIdLevel8;
    }

    /**
     * Set totalWithIdLevel9
     *
     * @param integer $totalWithIdLevel9
     *
     * @return ProjectVoterSummary
     */
    public function setTotalWithIdLevel9($totalWithIdLevel9)
    {
        $this->totalWithIdLevel9 = $totalWithIdLevel9;

        return $this;
    }

    /**
     * Get totalWithIdLevel9
     *
     * @return integer
     */
    public function getTotalWithIdLevel9()
    {
        return $this->totalWithIdLevel9;
    }

    /**
     * Set totalWithIdLevel10
     *
     * @param integer $totalWithIdLevel10
     *
     * @return ProjectVoterSummary
     */
    public function setTotalWithIdLevel10($totalWithIdLevel10)
    {
        $this->totalWithIdLevel10 = $totalWithIdLevel10;

        return $this;
    }

    /**
     * Get totalWithIdLevel10
     *
     * @return integer
     */
    public function getTotalWithIdLevel10()
    {
        return $this->totalWithIdLevel10;
    }

    /**
     * Set totalWithIdStaff
     *
     * @param integer $totalWithIdStaff
     *
     * @return ProjectVoterSummary
     */
    public function setTotalWithIdStaff($totalWithIdStaff)
    {
        $this->totalWithIdStaff = $totalWithIdStaff;

        return $this;
    }

    /**
     * Get totalWithIdStaff
     *
     * @return integer
     */
    public function getTotalWithIdStaff()
    {
        return $this->totalWithIdStaff;
    }

    /**
     * Set totalWithIdOthers
     *
     * @param integer $totalWithIdOthers
     *
     * @return ProjectVoterSummary
     */
    public function setTotalWithIdOthers($totalWithIdOthers)
    {
        $this->totalWithIdOthers = $totalWithIdOthers;

        return $this;
    }

    /**
     * Get totalWithIdOthers
     *
     * @return integer
     */
    public function getTotalWithIdOthers()
    {
        return $this->totalWithIdOthers;
    }

    /**
     * Set totalWithIdMember
     *
     * @param integer $totalWithIdMember
     *
     * @return ProjectVoterSummary
     */
    public function setTotalWithIdMember($totalWithIdMember)
    {
        $this->totalWithIdMember = $totalWithIdMember;

        return $this;
    }

    /**
     * Get totalWithIdMember
     *
     * @return integer
     */
    public function getTotalWithIdMember()
    {
        return $this->totalWithIdMember;
    }

    /**
     * Set totalWithIdCellphone
     *
     * @param integer $totalWithIdCellphone
     *
     * @return ProjectVoterSummary
     */
    public function setTotalWithIdCellphone($totalWithIdCellphone)
    {
        $this->totalWithIdCellphone = $totalWithIdCellphone;

        return $this;
    }

    /**
     * Get totalWithIdCellphone
     *
     * @return integer
     */
    public function getTotalWithIdCellphone()
    {
        return $this->totalWithIdCellphone;
    }

    /**
     * Set totalSubmitted
     *
     * @param integer $totalSubmitted
     *
     * @return ProjectVoterSummary
     */
    public function setTotalSubmitted($totalSubmitted)
    {
        $this->totalSubmitted = $totalSubmitted;

        return $this;
    }

    /**
     * Get totalSubmitted
     *
     * @return integer
     */
    public function getTotalSubmitted()
    {
        return $this->totalSubmitted;
    }

    /**
     * Set totalHasSubmittedLevel1
     *
     * @param integer $totalHasSubmittedLevel1
     *
     * @return ProjectVoterSummary
     */
    public function setTotalHasSubmittedLevel1($totalHasSubmittedLevel1)
    {
        $this->totalHasSubmittedLevel1 = $totalHasSubmittedLevel1;

        return $this;
    }

    /**
     * Get totalHasSubmittedLevel1
     *
     * @return integer
     */
    public function getTotalHasSubmittedLevel1()
    {
        return $this->totalHasSubmittedLevel1;
    }

    /**
     * Set totalHasSubmittedLevel2
     *
     * @param integer $totalHasSubmittedLevel2
     *
     * @return ProjectVoterSummary
     */
    public function setTotalHasSubmittedLevel2($totalHasSubmittedLevel2)
    {
        $this->totalHasSubmittedLevel2 = $totalHasSubmittedLevel2;

        return $this;
    }

    /**
     * Get totalHasSubmittedLevel2
     *
     * @return integer
     */
    public function getTotalHasSubmittedLevel2()
    {
        return $this->totalHasSubmittedLevel2;
    }

    /**
     * Set totalHasSubmittedLevel3
     *
     * @param integer $totalHasSubmittedLevel3
     *
     * @return ProjectVoterSummary
     */
    public function setTotalHasSubmittedLevel3($totalHasSubmittedLevel3)
    {
        $this->totalHasSubmittedLevel3 = $totalHasSubmittedLevel3;

        return $this;
    }

    /**
     * Get totalHasSubmittedLevel3
     *
     * @return integer
     */
    public function getTotalHasSubmittedLevel3()
    {
        return $this->totalHasSubmittedLevel3;
    }

    /**
     * Set totalHasSubmittedLevel4
     *
     * @param integer $totalHasSubmittedLevel4
     *
     * @return ProjectVoterSummary
     */
    public function setTotalHasSubmittedLevel4($totalHasSubmittedLevel4)
    {
        $this->totalHasSubmittedLevel4 = $totalHasSubmittedLevel4;

        return $this;
    }

    /**
     * Get totalHasSubmittedLevel4
     *
     * @return integer
     */
    public function getTotalHasSubmittedLevel4()
    {
        return $this->totalHasSubmittedLevel4;
    }

    /**
     * Set totalHasSubmittedLevel5
     *
     * @param integer $totalHasSubmittedLevel5
     *
     * @return ProjectVoterSummary
     */
    public function setTotalHasSubmittedLevel5($totalHasSubmittedLevel5)
    {
        $this->totalHasSubmittedLevel5 = $totalHasSubmittedLevel5;

        return $this;
    }

    /**
     * Get totalHasSubmittedLevel5
     *
     * @return integer
     */
    public function getTotalHasSubmittedLevel5()
    {
        return $this->totalHasSubmittedLevel5;
    }

    /**
     * Set totalHasSubmittedLevel6
     *
     * @param integer $totalHasSubmittedLevel6
     *
     * @return ProjectVoterSummary
     */
    public function setTotalHasSubmittedLevel6($totalHasSubmittedLevel6)
    {
        $this->totalHasSubmittedLevel6 = $totalHasSubmittedLevel6;

        return $this;
    }

    /**
     * Get totalHasSubmittedLevel6
     *
     * @return integer
     */
    public function getTotalHasSubmittedLevel6()
    {
        return $this->totalHasSubmittedLevel6;
    }

    /**
     * Set totalHasSubmittedLevel7
     *
     * @param integer $totalHasSubmittedLevel7
     *
     * @return ProjectVoterSummary
     */
    public function setTotalHasSubmittedLevel7($totalHasSubmittedLevel7)
    {
        $this->totalHasSubmittedLevel7 = $totalHasSubmittedLevel7;

        return $this;
    }

    /**
     * Get totalHasSubmittedLevel7
     *
     * @return integer
     */
    public function getTotalHasSubmittedLevel7()
    {
        return $this->totalHasSubmittedLevel7;
    }

    /**
     * Set totalHasSubmittedLevel8
     *
     * @param integer $totalHasSubmittedLevel8
     *
     * @return ProjectVoterSummary
     */
    public function setTotalHasSubmittedLevel8($totalHasSubmittedLevel8)
    {
        $this->totalHasSubmittedLevel8 = $totalHasSubmittedLevel8;

        return $this;
    }

    /**
     * Get totalHasSubmittedLevel8
     *
     * @return integer
     */
    public function getTotalHasSubmittedLevel8()
    {
        return $this->totalHasSubmittedLevel8;
    }

    /**
     * Set totalHasSubmittedLevel9
     *
     * @param integer $totalHasSubmittedLevel9
     *
     * @return ProjectVoterSummary
     */
    public function setTotalHasSubmittedLevel9($totalHasSubmittedLevel9)
    {
        $this->totalHasSubmittedLevel9 = $totalHasSubmittedLevel9;

        return $this;
    }

    /**
     * Get totalHasSubmittedLevel9
     *
     * @return integer
     */
    public function getTotalHasSubmittedLevel9()
    {
        return $this->totalHasSubmittedLevel9;
    }

    /**
     * Set totalHasSubmittedLevel10
     *
     * @param integer $totalHasSubmittedLevel10
     *
     * @return ProjectVoterSummary
     */
    public function setTotalHasSubmittedLevel10($totalHasSubmittedLevel10)
    {
        $this->totalHasSubmittedLevel10 = $totalHasSubmittedLevel10;

        return $this;
    }

    /**
     * Get totalHasSubmittedLevel10
     *
     * @return integer
     */
    public function getTotalHasSubmittedLevel10()
    {
        return $this->totalHasSubmittedLevel10;
    }

    /**
     * Set totalHasSubmittedOthers
     *
     * @param integer $totalHasSubmittedOthers
     *
     * @return ProjectVoterSummary
     */
    public function setTotalHasSubmittedOthers($totalHasSubmittedOthers)
    {
        $this->totalHasSubmittedOthers = $totalHasSubmittedOthers;

        return $this;
    }

    /**
     * Get totalHasSubmittedOthers
     *
     * @return integer
     */
    public function getTotalHasSubmittedOthers()
    {
        return $this->totalHasSubmittedOthers;
    }

    /**
     * Set totalIs1
     *
     * @param integer $totalIs1
     *
     * @return ProjectVoterSummary
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
     * @return ProjectVoterSummary
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
     * @return ProjectVoterSummary
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
     * @return ProjectVoterSummary
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
     * @return ProjectVoterSummary
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
     * @return ProjectVoterSummary
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
     * @return ProjectVoterSummary
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
     * Set totalIs8
     *
     * @param integer $totalIs8
     *
     * @return ProjectVoterSummary
     */
    public function setTotalIs8($totalIs8)
    {
        $this->totalIs8 = $totalIs8;

        return $this;
    }

    /**
     * Get totalIs8
     *
     * @return integer
     */
    public function getTotalIs8()
    {
        return $this->totalIs8;
    }

    /**
     * Set totalHasAst
     *
     * @param integer $totalHasAst
     *
     * @return ProjectVoterSummary
     */
    public function setTotalHasAst($totalHasAst)
    {
        $this->totalHasAst = $totalHasAst;

        return $this;
    }

    /**
     * Get totalHasAst
     *
     * @return integer
     */
    public function getTotalHasAst()
    {
        return $this->totalHasAst;
    }

    /**
     * Set totalHasAstLevel1
     *
     * @param integer $totalHasAstLevel1
     *
     * @return ProjectVoterSummary
     */
    public function setTotalHasAstLevel1($totalHasAstLevel1)
    {
        $this->totalHasAstLevel1 = $totalHasAstLevel1;

        return $this;
    }

    /**
     * Get totalHasAstLevel1
     *
     * @return integer
     */
    public function getTotalHasAstLevel1()
    {
        return $this->totalHasAstLevel1;
    }

    /**
     * Set totalHasAstLevel2
     *
     * @param integer $totalHasAstLevel2
     *
     * @return ProjectVoterSummary
     */
    public function setTotalHasAstLevel2($totalHasAstLevel2)
    {
        $this->totalHasAstLevel2 = $totalHasAstLevel2;

        return $this;
    }

    /**
     * Get totalHasAstLevel2
     *
     * @return integer
     */
    public function getTotalHasAstLevel2()
    {
        return $this->totalHasAstLevel2;
    }

    /**
     * Set totalHasAstLevel3
     *
     * @param integer $totalHasAstLevel3
     *
     * @return ProjectVoterSummary
     */
    public function setTotalHasAstLevel3($totalHasAstLevel3)
    {
        $this->totalHasAstLevel3 = $totalHasAstLevel3;

        return $this;
    }

    /**
     * Get totalHasAstLevel3
     *
     * @return integer
     */
    public function getTotalHasAstLevel3()
    {
        return $this->totalHasAstLevel3;
    }

    /**
     * Set totalHasAstLevel4
     *
     * @param integer $totalHasAstLevel4
     *
     * @return ProjectVoterSummary
     */
    public function setTotalHasAstLevel4($totalHasAstLevel4)
    {
        $this->totalHasAstLevel4 = $totalHasAstLevel4;

        return $this;
    }

    /**
     * Get totalHasAstLevel4
     *
     * @return integer
     */
    public function getTotalHasAstLevel4()
    {
        return $this->totalHasAstLevel4;
    }

    /**
     * Set totalHasAstLevel5
     *
     * @param integer $totalHasAstLevel5
     *
     * @return ProjectVoterSummary
     */
    public function setTotalHasAstLevel5($totalHasAstLevel5)
    {
        $this->totalHasAstLevel5 = $totalHasAstLevel5;

        return $this;
    }

    /**
     * Get totalHasAstLevel5
     *
     * @return integer
     */
    public function getTotalHasAstLevel5()
    {
        return $this->totalHasAstLevel5;
    }

    /**
     * Set totalHasAstLevel6
     *
     * @param integer $totalHasAstLevel6
     *
     * @return ProjectVoterSummary
     */
    public function setTotalHasAstLevel6($totalHasAstLevel6)
    {
        $this->totalHasAstLevel6 = $totalHasAstLevel6;

        return $this;
    }

    /**
     * Get totalHasAstLevel6
     *
     * @return integer
     */
    public function getTotalHasAstLevel6()
    {
        return $this->totalHasAstLevel6;
    }

    /**
     * Set totalHasAstLevel7
     *
     * @param integer $totalHasAstLevel7
     *
     * @return ProjectVoterSummary
     */
    public function setTotalHasAstLevel7($totalHasAstLevel7)
    {
        $this->totalHasAstLevel7 = $totalHasAstLevel7;

        return $this;
    }

    /**
     * Get totalHasAstLevel7
     *
     * @return integer
     */
    public function getTotalHasAstLevel7()
    {
        return $this->totalHasAstLevel7;
    }

    /**
     * Set totalHasAstLevel8
     *
     * @param integer $totalHasAstLevel8
     *
     * @return ProjectVoterSummary
     */
    public function setTotalHasAstLevel8($totalHasAstLevel8)
    {
        $this->totalHasAstLevel8 = $totalHasAstLevel8;

        return $this;
    }

    /**
     * Get totalHasAstLevel8
     *
     * @return integer
     */
    public function getTotalHasAstLevel8()
    {
        return $this->totalHasAstLevel8;
    }

    /**
     * Set totalHasAstLevel9
     *
     * @param integer $totalHasAstLevel9
     *
     * @return ProjectVoterSummary
     */
    public function setTotalHasAstLevel9($totalHasAstLevel9)
    {
        $this->totalHasAstLevel9 = $totalHasAstLevel9;

        return $this;
    }

    /**
     * Get totalHasAstLevel9
     *
     * @return integer
     */
    public function getTotalHasAstLevel9()
    {
        return $this->totalHasAstLevel9;
    }

    /**
     * Set totalHasAstLevel10
     *
     * @param integer $totalHasAstLevel10
     *
     * @return ProjectVoterSummary
     */
    public function setTotalHasAstLevel10($totalHasAstLevel10)
    {
        $this->totalHasAstLevel10 = $totalHasAstLevel10;

        return $this;
    }

    /**
     * Get totalHasAstLevel10
     *
     * @return integer
     */
    public function getTotalHasAstLevel10()
    {
        return $this->totalHasAstLevel10;
    }

    /**
     * Set totalHasAstOthers
     *
     * @param integer $totalHasAstOthers
     *
     * @return ProjectVoterSummary
     */
    public function setTotalHasAstOthers($totalHasAstOthers)
    {
        $this->totalHasAstOthers = $totalHasAstOthers;

        return $this;
    }

    /**
     * Get totalHasAstOthers
     *
     * @return integer
     */
    public function getTotalHasAstOthers()
    {
        return $this->totalHasAstOthers;
    }

    /**
     * Set totalHasAstCellphone
     *
     * @param integer $totalHasAstCellphone
     *
     * @return ProjectVoterSummary
     */
    public function setTotalHasAstCellphone($totalHasAstCellphone)
    {
        $this->totalHasAstCellphone = $totalHasAstCellphone;

        return $this;
    }

    /**
     * Get totalHasAstCellphone
     *
     * @return integer
     */
    public function getTotalHasAstCellphone()
    {
        return $this->totalHasAstCellphone;
    }

    /**
     * Set totalHasSubmittedCellphone
     *
     * @param integer $totalHasSubmittedCellphone
     *
     * @return ProjectVoterSummary
     */
    public function setTotalHasSubmittedCellphone($totalHasSubmittedCellphone)
    {
        $this->totalHasSubmittedCellphone = $totalHasSubmittedCellphone;

        return $this;
    }

    /**
     * Get totalHasSubmittedCellphone
     *
     * @return integer
     */
    public function getTotalHasSubmittedCellphone()
    {
        return $this->totalHasSubmittedCellphone;
    }

    /**
     * Set totalHasAstStaff
     *
     * @param integer $totalHasAstStaff
     *
     * @return ProjectVoterSummary
     */
    public function setTotalHasAstStaff($totalHasAstStaff)
    {
        $this->totalHasAstStaff = $totalHasAstStaff;

        return $this;
    }

    /**
     * Get totalHasAstStaff
     *
     * @return integer
     */
    public function getTotalHasAstStaff()
    {
        return $this->totalHasAstStaff;
    }

    /**
     * Set createdBy
     *
     * @param string $createdBy
     *
     * @return ProjectVoterSummary
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
     * Set clusteredPrecinct
     *
     * @param integer $clusteredPrecinct
     *
     * @return ProjectVoterSummary
     */
    public function setClusteredPrecinct($clusteredPrecinct)
    {
        $this->clusteredPrecinct = $clusteredPrecinct;

        return $this;
    }

    /**
     * Get clusteredPrecinct
     *
     * @return integer
     */
    public function getClusteredPrecinct()
    {
        return $this->clusteredPrecinct;
    }

    /**
     * Set votingCenter
     *
     * @param string $votingCenter
     *
     * @return ProjectVoterSummary
     */
    public function setVotingCenter($votingCenter)
    {
        $this->votingCenter = $votingCenter;

        return $this;
    }

    /**
     * Get votingCenter
     *
     * @return string
     */
    public function getVotingCenter()
    {
        return $this->votingCenter;
    }
}
