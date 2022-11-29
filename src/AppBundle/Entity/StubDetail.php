<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * StubDetail
 *
 * @ORM\Table(name="tbl_stub_detail")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\StubDetailRepository")
 * @UniqueEntity(fields={"hdrId","dtlId"},message="This id has already been created",errorPath="dtlId")
 */
class StubDetail
{
   /**
     * @var int
     *
     * @ORM\Column(name="dtl_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $dtlId;

    /**
     * @var int
     *
     * @ORM\Column(name="hdr_id", type="integer")
     * @Assert\NotBlank()
     */

    private $hdrId;

    /**
     * @var int
     *
     * @ORM\Column(name="voter_id", type="integer")
     * @Assert\NotBlank()
     */

    private $voterId;

    /**
     * @var int
     *
     * @ORM\Column(name="pro_voter_id", type="integer")
     * @Assert\NotBlank()
     */

    private $proVoterId;

    /**
     * @var string
     *
     * @ORM\Column(name="created_by", type="string", length=150)
     * @Assert\NotBlank()
     */

    private $createdBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Assert\NotBlank() 
     */
    private $createdAt;
    
    /**
     * @var string
     *
     * @ORM\Column(name="remarks", type="string", length=255)
     */

    private $remarks;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=3)
     * @Assert\NotBlank()
     */

    private $status;
}

