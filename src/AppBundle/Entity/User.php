<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;

/**
 * User
 *
 * @ORM\Table(name="tbl_user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @UniqueEntity("username",message="This username has already been taken",groups={"create","edit"})
 * @UniqueEntity("email",message="This email has already been taken",groups={"create","edit"})
 */

class User implements AdvancedUserInterface
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
     * @ORM\Column(name="username", type="string", length=45, nullable=true, unique=true)
     * @Assert\NotBlank(groups={"create","edit"})
     */
    private $username;

    /**
     * @SecurityAssert\UserPassword(
     *     message = "change_password.old_password",
     *     groups = {"change_password"}
     * )
     */
    public $oldPassword;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=true)
     * @Assert\Length(min=6, minMessage="Password must be more than 6 characters or more.", groups={"create","change_password"})
     * @Assert\NotBlank(message="Password should not be blank.",groups={"create"})
     * @Assert\NotBlank(message="Please enter new password.",groups={"user_change_password"})
     */
    private $password;

    /**
     * @var string
     * @Assert\Expression("this.getPasswordRepeat() == this.getPassword()", message="Password did not match.", groups={"create","user_change_password"})
     */
    private $passwordRepeat;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"create","edit"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", length=85, nullable=true)
     * @Assert\NotBlank(message="Please select gender",groups={"create","edit"})
     * @Assert\Choice(
     *     choices = { "Male", "Female" },
     *     message = "Choose a valid gender."
     * )
     */
    private $gender;

    /**
     * @var string
     *
     * @ORM\Column(name="contact_no", type="string", length=45, nullable=true)
     */
    private $contactNo;

    /**
     * @var string
     *
     * @ORM\Column(name="access_code", type="string", length=45, nullable=true)
     */
    private $accessCode;

     /**
     * @var datetime
     *
     * @ORM\Column(name="valid_until", type="datetime", nullable=true)
     */
    private $validUntil;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=85, nullable=true, unique=true)
     * @Assert\Email(groups={"create","edit"})
     */
    private $email;

    /**
     * @var int
     *
     * @ORM\Column(name="isActive", type="integer", nullable=true)
     */
    private $isActive;

    /**
     * @var int
     *
     * @ORM\Column(name="strict_access", type="integer", nullable=true)
     */
    private $strictAccess;

     /**
     * @var int
     *
     * @ORM\Column(name="require_approval", type="integer", nullable=true)
     */
    private $requireApproval;

    /**
     * @var string
     *
     * @ORM\Column(name="isDefault", type="string", length=15, nullable=true)
     */
    private $isDefault;

    /**
     * @var string
     *
     * @ORM\Column(name="isOnline", type="string", length=15, nullable=true)
     */
    private $isOnline;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_registered", type="datetime", nullable=true)
     */
    private $dateRegistered;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_login", type="datetime", nullable=true)
     */
    private $lastLogin;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="roles", type="text", nullable=true)
     */
    private $roles;

    /**
     * @var \AppBundle\Entity\Groups
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Groups")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     * })
     * @Assert\NotBlank(message="Please select a group.",groups={"create","edit"})
     */
    private $group;

     /**
     * @var \AppBundle\Entity\Province
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Province")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="province_code", referencedColumnName="province_code")
     * })
     * 
     */

    private $province;


     /**
     * @var \AppBundle\Entity\Project
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Project")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pro_id", referencedColumnName="pro_id")
     * })
     * 
     * @Assert\NotBlank(message="Please select a project.",groups={"create","edit"})
     */

    private $project;
    
    public function __construct() {}

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
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

     /**
     * Set passwordRepeat
     *
     * @param string $passwordRepeat
     *
     * @return User
     */
    public function setPasswordRepeat($passwordRepeat)
    {
        $this->passwordRepeat = $passwordRepeat;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPasswordRepeat()
    {
        return $this->passwordRepeat;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return User
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
     * Set gender
     *
     * @param string $gender
     *
     * @return User
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set contactNo
     *
     * @param string $contactNo
     *
     * @return User
     */
    public function setContactNo($contactNo)
    {
        $this->contactNo = $contactNo;

        return $this;
    }

    /**
     * Get accessCode
     *
     * @return string
     */
    public function getAccessCode()
    {
        return $this->accessCode;
    }

    /**
     * Set accessCode
     *
     * @param string $accessCode
     *
     * @return User
     */
    public function setAccessCode($accessCode)
    {
        $this->accessCode = $accessCode;

        return $this;
    }

    /**
     * Get validUntil
     *
     * @return datetime
     */
    public function getValidUntil()
    {
        return $this->validUntil;
    }

    /**
     * Set validUntil
     *
     * @param string $validUntil
     *
     * @return User
     */
    public function setValidUntil($validUntil)
    {
        $this->validUntil = $validUntil;

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
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set isActive
     *
     * @param integer $isActive
     *
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return int
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set strictAccess
     *
     * @param integer $strictAccess
     *
     * @return User
     */
    public function setStrictAccess($strictAccess)
    {
        $this->strictAccess = $strictAccess;

        return $this;
    }

    /**
     * Get strictAccess
     *
     * @return int
     */
    public function getStrictAccess()
    {
        return $this->strictAccess;
    }

        /**
     * Set requireApproval
     *
     * @param integer $requireApproval
     *
     * @return User
     */
    public function setRequireApproval($requireApproval)
    {
        $this->requireApproval = $requireApproval;

        return $this;
    }

    /**
     * Get requireApproval
     *
     * @return int
     */
    public function getRequireApproval()
    {
        return $this->requireApproval;
    }

    /**
     * Set isDefault
     *
     * @param string $isDefault
     *
     * @return User
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * Get isDefault
     *
     * @return string
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * Set isOnline
     *
     * @param string $isOnline
     *
     * @return User
     */
    public function setIsOnline($isOnline)
    {
        $this->isOnline = $isOnline;

        return $this;
    }

    /**
     * Get isOnline
     *
     * @return string
     */
    public function getIsOnline()
    {
        return $this->isOnline;
    }

    /**
     * Set dateRegistered
     *
     * @param \DateTime $dateRegistered
     *
     * @return User
     */
    public function setDateRegistered($dateRegistered)
    {
        $this->dateRegistered = $dateRegistered;

        return $this;
    }

    /**
     * Get dateRegistered
     *
     * @return \DateTime
     */
    public function getDateRegistered()
    {
        return $this->dateRegistered;
    }

    /**
     * Set lastLogin
     *
     * @param \DateTime $lastLogin
     *
     * @return User
     */
    public function setLastLogin($lastLogin)
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    /**
     * Get lastLogin
     *
     * @return \DateTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return User
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set roles
     *
     * @param string $roles
     *
     * @return User
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get roles
     *
     * @return array
     */
    public function getRoles()
    {
        return array($this->roles);
    }

    
    /**
     * Get isAdmin
     *
     * @return boolean
     */
    public function getIsAdmin()
    {
        return ($this->getGroup()->getId() == 1 || $this->getGroup()->getId() == 22);
    }

    /**
     * Get isViewer
     *
     * @return boolean
     */
    public function getIsViewer()
    {
        return $this->getGroup()->getId() == 11;
    }

    /**
     * Get isEncoder
     *
     * @return boolean
     */
    public function getIsEncoder()
    {
        return $this->getGroup()->getId() == 12;
    }

    /**
     * Get isTopLevel
     *
     * @return boolean
     */
    public function getIsTopLevel()
    {
        return $this->getGroup()->getId() == 13;
    }

    /**
     * Set group
     *
     * @param \AppBundle\Entity\Groups $group
     *
     * @return User
     */
    public function setGroup(\AppBundle\Entity\Groups $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return \AppBundle\Entity\Groups
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set province
     *
     * @param \AppBundle\Entity\Province $province
     *
     * @return User
     */
    public function setProvince(\AppBundle\Entity\Province $province = null)
    {
        $this->province = $province;

        return $this;
    }

    /**
     * Get province
     *
     * @return \AppBundle\Entity\Province
     */
    public function getProvince()
    {
        return $this->province;
    }

    /**
     * Set project
     *
     * @param \AppBundle\Entity\Project $project
     *
     * @return User
     */
    public function setProject(\AppBundle\Entity\Project $project = null)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \AppBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Checks whether the user's account has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw an AccountExpiredException and prevent login.
     *
     * @return bool true if the user's account is non expired, false otherwise
     *
     * @see AccountExpiredException
     */
    public function isAccountNonExpired()
    {
        // TODO: Implement isAccountNonExpired() method.
        return true;
    }

    /**
     * Checks whether the user is locked.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a LockedException and prevent login.
     *
     * @return bool true if the user is not locked, false otherwise
     *
     * @see LockedException
     */
    public function isAccountNonLocked()
    {
        // TODO: Implement isAccountNonLocked() method.
        return true;
    }

    /**
     * Checks whether the user's credentials (password) has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a CredentialsExpiredException and prevent login.
     *
     * @return bool true if the user's credentials are non expired, false otherwise
     *
     * @see CredentialsExpiredException
     */
    public function isCredentialsNonExpired()
    {
        // TODO: Implement isCredentialsNonExpired() method.
        return true;
    }

    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * @return bool true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function isEnabled()
    {
        // TODO: Implement isEnabled() method.
        return $this->isActive;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }
}
