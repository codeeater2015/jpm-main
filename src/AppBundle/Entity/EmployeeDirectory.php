<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * EmployeeDirectory
 *
 * @ORM\Table(name="tbl_employee_directory")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EmployeeDirectoryRepository")
 */
class EmployeeDirectory
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
     * @ORM\Column(name="name_a", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $nameA;
        
    /**
     * @var string
     *
     * @ORM\Column(name="name_b", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $nameB;

    /**
     * @var string
     *
     * @ORM\Column(name="name_c", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $nameC;

    /**
     * @var string
     *
     * @ORM\Column(name="contact_no", type="string", length=15)
     * @Assert\NotBlank()
     */
    private $contactNo;

      /**
     * @var string
     *
     * @ORM\Column(name="emp_position", type="string", length=150)
     * @Assert\NotBlank()
     */
    private $empPosition;

    /**
     * @var string
     *
     * @ORM\Column(name="municipality", type="string", length=150)
     * @Assert\NotBlank()
     */
    private $municipality;

    /**
     * @var string
     *
     * @ORM\Column(name="barangay", type="string", length=150)
     * @Assert\NotBlank()
     */
    private $barangay;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=150)
     * @Assert\NotBlank()
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="emp_group", type="string", length=30)
     * @Assert\NotBlank()
     */
    private $empGroup;

     /**
     * @var string
     *
     * @ORM\Column(name="office", type="string", length=150)
     * @Assert\NotBlank()
     */
    private $office;

     /**
     * @var string
     *
     * @ORM\Column(name="remarks", type="string", length=255)
     * @Assert\NotBlank()
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nameA
     *
     * @param string $nameA
     *
     * @return EmployeeDirectory
     */
    public function setNameA($nameA)
    {
        $this->nameA = $nameA;

        return $this;
    }

    /**
     * Get nameA
     *
     * @return string
     */
    public function getNameA()
    {
        return $this->nameA;
    }

    /**
     * Set nameB
     *
     * @param string $nameB
     *
     * @return EmployeeDirectory
     */
    public function setNameB($nameB)
    {
        $this->nameB = $nameB;

        return $this;
    }

    /**
     * Get nameB
     *
     * @return string
     */
    public function getNameB()
    {
        return $this->nameB;
    }

    /**
     * Set nameC
     *
     * @param string $nameC
     *
     * @return EmployeeDirectory
     */
    public function setNameC($nameC)
    {
        $this->nameC = $nameC;

        return $this;
    }

    /**
     * Get nameC
     *
     * @return string
     */
    public function getNameC()
    {
        return $this->nameC;
    }

    /**
     * Set contactNo
     *
     * @param string $contactNo
     *
     * @return EmployeeDirectory
     */
    public function setContactNo($contactNo)
    {
        $this->contactNo = $contactNo;

        return $this;
    }

    /**
     * Get contactNo
     *
     * @return string
     */
    public function getContactNo()
    {
        return $this->contactNo;
    }

    /**
     * Set empPosition
     *
     * @param string $empPosition
     *
     * @return EmployeeDirectory
     */
    public function setEmpPosition($empPosition)
    {
        $this->empPosition = $empPosition;

        return $this;
    }

    /**
     * Get empPosition
     *
     * @return string
     */
    public function getEmpPosition()
    {
        return $this->empPosition;
    }

    /**
     * Set municipality
     *
     * @param string $municipality
     *
     * @return EmployeeDirectory
     */
    public function setMunicipality($municipality)
    {
        $this->municipality = $municipality;

        return $this;
    }

    /**
     * Get municipality
     *
     * @return string
     */
    public function getMunicipality()
    {
        return $this->municipality;
    }

    /**
     * Set barangay
     *
     * @param string $barangay
     *
     * @return EmployeeDirectory
     */
    public function setBarangay($barangay)
    {
        $this->barangay = $barangay;

        return $this;
    }

    /**
     * Get barangay
     *
     * @return string
     */
    public function getBarangay()
    {
        return $this->barangay;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return EmployeeDirectory
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set empGroup
     *
     * @param string $empGroup
     *
     * @return EmployeeDirectory
     */
    public function setEmpGroup($empGroup)
    {
        $this->empGroup = $empGroup;

        return $this;
    }

    /**
     * Get empGroup
     *
     * @return string
     */
    public function getEmpGroup()
    {
        return $this->empGroup;
    }

    /**
     * Set office
     *
     * @param string $office
     *
     * @return EmployeeDirectory
     */
    public function setOffice($office)
    {
        $this->office = $office;

        return $this;
    }

    /**
     * Get office
     *
     * @return string
     */
    public function getOffice()
    {
        return $this->office;
    }

    /**
     * Set remarks
     *
     * @param string $remarks
     *
     * @return EmployeeDirectory
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
     * @return EmployeeDirectory
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
