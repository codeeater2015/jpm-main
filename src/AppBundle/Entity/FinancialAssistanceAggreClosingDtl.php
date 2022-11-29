<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FinancialAssistanceAggreClosingDtl
 *
 * @ORM\Table(name="financial_assistance_aggre_closing_dtl")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FinancialAssistanceAggreClosingDtlRepository")
 */
class FinancialAssistanceAggreClosingDtl
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
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
