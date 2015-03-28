<?php

namespace Mobility\StudentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Mobility\UniversityBundle\Entity\University;

/**
 * Wish
 *
 * @ORM\Table(name="wishes")
 * @ORM\Entity(repositoryClass="Mobility\StudentBundle\Entity\WishRepository")
 */
class Wish
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Mobility\StudentBundle\Entity\Student", inversedBy="wishes")
     * @Assert\Valid()  
     */
    private $student;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Mobility\UniversityBundle\Entity\University")
     * @Assert\Valid()
     */
    private $University;

    /**
     * @var integer
     *
     * @ORM\Column(name="priority", type="integer")
     */
    private $priority;

    /**
     * Set priority
     *
     * @param integer $priority
     * @return Wish
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority
     *
     * @return integer 
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set student
     *
     * @param \Mobility\StudentBundle\Entity\Student $student
     * @return Wish
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
     * @return Wish
     */
    public function setUniversity(\Mobility\UniversityBundle\Entity\University $university)
    {
        $this->University = $university;

        return $this;
    }

    /**
     * Get University
     *
     * @return \Mobility\UniversityBundle\Entity\University 
     */
    public function getUniversity()
    {
        return $this->University;
    }
}
