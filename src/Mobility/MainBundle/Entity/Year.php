<?php

namespace Mobility\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Year
 *
 * @ORM\Table(name="years")
 * @ORM\Entity(repositoryClass="Mobility\MainBundle\Entity\YearRepository")
 */
class Year
{
    /**
     * @var integer
     *
     * @ORM\Column(name="year", type="integer")
     * @ORM\Id
     */
    private $year;

    /**
     * @var boolean
     *
     * @ORM\Column(name="placements_public", type="boolean")
     */
    private $placementsPublic;

    /**
     * @var boolean
     *
     * @ORM\Column(name="placements_locked", type="boolean")
     */
    private $placementsLocked;



    public function __construct() {
        $this->placementsPublic = false;
        $this->placementsLocked = false;
    }

    /**
     * Set year
     *
     * @param integer $year
     * @return Year
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
     * Set placementsPublic
     *
     * @param boolean $placementsPublic
     * @return Year
     */
    public function setPlacementsPublic($placementsPublic)
    {
        $this->placementsPublic = $placementsPublic;

        return $this;
    }

    /**
     * Get placementsPublic
     *
     * @return boolean 
     */
    public function getPlacementsPublic()
    {
        return $this->placementsPublic;
    }

    /**
     * Set placementsLocked
     *
     * @param boolean $placementsLocked
     * @return Year
     */
    public function setPlacementsLocked($placementsLocked)
    {
        $this->placementsLocked = $placementsLocked;

        return $this;
    }

    /**
     * Get placementsLocked
     *
     * @return boolean 
     */
    public function getPlacementsLocked()
    {
        return $this->placementsLocked;
    }
}
