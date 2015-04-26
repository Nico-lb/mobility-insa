<?php

namespace Mobility\PlacementBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Mobility\StudentBundle\Entity\Student;
use Mobility\UniversityBundle\Entity\University;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Placement
 *
 * @ORM\Table(name="placements")
 * @ORM\Entity(repositoryClass="Mobility\PlacementBundle\Entity\PlacementRepository")
 * @UniqueEntity(fields={"student"}, message="Il existe déjà une affectation pour cet étudiant.")
 */
class Placement
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Mobility\StudentBundle\Entity\Student")
     * @Assert\Valid()  
     */
    private $student;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Mobility\UniversityBundle\Entity\University")
     * @Assert\Valid()
     */
    private $university;

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


    public function __construct() {
        $now = new \DateTime();
        // Si mois >= sept, on prend l'année suivante
        if ($now->format('n') >= 9) {
            $this->year = $now->format('Y') + 1;
        } else {
            $this->year = $now->format('Y');
        }
    }

    /**
     * Set student
     *
     * @param \Mobility\StudentBundle\Entity\Student $student
     * @return Placement
     */
    public function setStudent(\Mobility\StudentBundle\Entity\Student $student)
    {
        $this->student = $student;

        return $this;
    }

    /**
     * Get student
     *
     * @return \Mobility\StudentBundle\Entity\Student 
     */
    public function getStudent()
    {
        return $this->student;
    }

    /**
     * Set University
     *
     * @param \Mobility\UniversityBundle\Entity\University $university
     * @return Placement
     */
    public function setUniversity(\Mobility\UniversityBundle\Entity\University $university)
    {
        $this->university = $university;

        return $this;
    }

    /**
     * Get University
     *
     * @return \Mobility\UniversityBundle\Entity\University 
     */
    public function getUniversity()
    {
        return $this->university;
    }

    /**
     * Set state
     *
     * @param integer $state
     * @return Placement
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
     * Set year
     *
     * @param integer $year
     * @return Placement
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
}
