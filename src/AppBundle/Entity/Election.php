<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Election
 *
 * @ORM\Table(name="tbl_election")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ElectionRepository")
 * @UniqueEntity(fields={"electName"},message="This name already exists.", errorPath="electName")
 */
class Election
{
    /**
     * @var int
     *
     * @ORM\Column(name="elect_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $electId;

    /**
     * @var string
     *
     * @ORM\Column(name="elect_name", type="string", length=150)
     * @Assert\NotBlank()
     */
    private $electName;

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
     * Get electId
     *
     * @return integer
     */
    public function getElectId()
    {
        return $this->electId;
    }

    /**
     * Set electName
     *
     * @param string $electName
     *
     * @return Election
     */
    public function setElectName($electName)
    {
        $this->electName = $electName;
    
        return $this;
    }

    /**
     * Get electName
     *
     * @return string
     */
    public function getElectName()
    {
        return $this->electName;
    }

    /**
     * Set remarks
     *
     * @param string $remarks
     *
     * @return Election
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
     * @return Election
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
