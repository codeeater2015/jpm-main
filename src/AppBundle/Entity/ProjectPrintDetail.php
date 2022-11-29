<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * ProjectPrintDetail
 *
 * @ORM\Table(name="tbl_project_print_detail")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProjectPrintDetailRepository")
 */
class ProjectPrintDetail
{
    /**
     * @var int
     *
     * @ORM\Column(name="print_detail_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $printDetailId;

     /**
     * @var integer
     *
     * @ORM\Column(name="pro_voter_id", type="integer")
     * @Assert\NotBlank()
     */
    private $proVoterId;

     /**
     * @var int
     *
     * @ORM\Column(name="print_id", type="integer")
     * @Assert\NotBlank()
     */
    private $printId;
    
     /**
     * @var integer
     *
     * @ORM\Column(name="voter_id", type="integer")
     * @Assert\NotBlank()
     */
    private $voterId;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=3)
     */
    private $status;
  

    /**
     * Get printDetailId
     *
     * @return integer
     */
    public function getPrintDetailId()
    {
        return $this->printDetailId;
    }

    /**
     * Set proVoterId
     *
     * @param integer $proVoterId
     *
     * @return ProjectPrintDetail
     */
    public function setProVoterId($proVoterId)
    {
        $this->proVoterId = $proVoterId;
    
        return $this;
    }

    /**
     * Get proVoterId
     *
     * @return integer
     */
    public function getProVoterId()
    {
        return $this->proVoterId;
    }

    /**
     * Set voterId
     *
     * @param integer $voterId
     *
     * @return ProjectPrintDetail
     */
    public function setVoterId($voterId)
    {
        $this->voterId = $voterId;
    
        return $this;
    }

    /**
     * Get voterId
     *
     * @return integer
     */
    public function getVoterId()
    {
        return $this->voterId;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return ProjectPrintDetail
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
     * Set printId
     *
     * @param integer $printId
     *
     * @return ProjectPrintDetail
     */
    public function setPrintId($printId)
    {
        $this->printId = $printId;
    
        return $this;
    }

    /**
     * Get printId
     *
     * @return integer
     */
    public function getPrintId()
    {
        return $this->printId;
    }
}
