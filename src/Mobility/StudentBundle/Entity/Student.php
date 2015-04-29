<?php

namespace Mobility\StudentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Student
 *
 * @ORM\Table(name="students")
 * @ORM\Entity(repositoryClass="Mobility\StudentBundle\Entity\StudentRepository")
 */
class Student
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="surname", type="string", length=255)
     */
    private $surname;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var integer
     *
     * @ORM\Column(name="promo", type="integer")
     */
    private $promo;

    /**
     * @var string
     *
     * @ORM\Column(name="auth", type="string", length=255)
     */
    private $auth;

    /**
     * @var integer
     *
     * @ORM\Column(name="rank", type="integer")
     */
    private $rank;

    /**
     * @var integer
     *
     * @ORM\Column(name="state", type="integer")
     */
    private $state;

    /**
     * @var integer
     *
     * @ORM\Column(name="year", type="integer")
     */
    private $year;
    
    /**
     * @ORM\OneToMany(targetEntity="Mobility\StudentBundle\Entity\Wish", mappedBy="student", cascade={"remove"})
     * @ORM\OrderBy({"priority" = "ASC"})
     */
    private $wishes;
    
    /**
     * @ORM\OneToMany(targetEntity="Mobility\PlacementBundle\Entity\Placement", mappedBy="student", cascade={"remove"})
     */
    private $placements;

    
    public function __construct() {
        $this->state = 0;
        $this->rank = 0;
        $this->wishes = new ArrayCollection();

        srand(time());
        $this->auth = sha1(rand());
        
        $now = new \DateTime();
        // Si mois >= sept, on prend l'année suivante
        if ($now->format('n') >= 9) {
            $this->year = $now->format('Y') + 1;
        } else {
            $this->year = $now->format('Y');
        }
    }

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
     * Set surname
     *
     * @param string $surname
     * @return Student
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * Get surname
     *
     * @return string 
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     * @return Student
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string 
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->surname . ' ' . $this->firstname;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Student
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
     * Set auth
     *
     * @param string $auth
     * @return Student
     */
    public function setAuth($auth)
    {
        $this->auth = $auth;

        return $this;
    }

    /**
     * Get auth
     *
     * @return string 
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * Set state
     *
     * @param integer $state
     * @return Student
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return integer 
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set promo
     *
     * @param integer $promo
     * @return Student
     */
    public function setPromo($promo)
    {
        $this->promo = $promo;

        return $this;
    }

    /**
     * Get promo
     *
     * @return integer 
     */
    public function getPromo()
    {
        return $this->promo;
    }

    /**
     * Set rank
     *
     * @param integer $rank
     * @return Student
     */
    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * Get rank
     *
     * @return integer 
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Set year
     *
     * @param integer $year
     * @return Student
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return integer 
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Add wishes
     *
     * @param \Mobility\StudentBundle\Entity\Wish $wishes
     * @return Student
     */
    public function addWish(\Mobility\StudentBundle\Entity\Wish $wishes)
    {
        $this->wishes[] = $wishes;

        return $this;
    }

    /**
     * Remove wishes
     *
     * @param \Mobility\StudentBundle\Entity\Wish $wishes
     */
    public function removeWish(\Mobility\StudentBundle\Entity\Wish $wishes)
    {
        $this->wishes->removeElement($wishes);
    }

    /**
     * Get wishes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getWishes()
    {
        return $this->wishes;
    }
}
