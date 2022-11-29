<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Province
 *
 * @ORM\Table(name="psw_province")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProvinceRepository")
 * @UniqueEntity(fields={"name"},message="This name already exist.",errorPath="name")
 */
class Province
{
    /**
     * @var int
     *
     * @ORM\Column(name="province_code", type="string", length=15)
     * @ORM\Id
     */
    private $provinceCode;

     /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank()
     */

    private $name;

     /**
     * @var string
     *
     * @ORM\Column(name="region", type="string", length=255)
     * @Assert\NotBlank()
     */

    private $region;

    /**
     * @var string
     *
     * @ORM\Column(name="region_code", type="string", length=45)
     * @Assert\NotBlank()
     */

    private $regionCode;

    /**
     * Get provinceCode
     *
     * @return integer
     */
    public function getProvinceCode()
    {
        return $this->provinceCode;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Province
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set region
     *
     * @param string $region
     *
     * @return Province
     */
    public function setRegion($region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set regionCode
     *
     * @param string $regionCode
     *
     * @return Province
     */
    public function setRegionCode($regionCode)
    {
        $this->regionCode = $regionCode;

        return $this;
    }

    /**
     * Get regionCode
     *
     * @return string
     */
    public function getRegionCode()
    {
        return $this->regionCode;
    }
}
