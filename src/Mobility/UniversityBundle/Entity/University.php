<?php

namespace Mobility\UniversityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * University
 *
 * @ORM\Table(name="universities")
 * @ORM\Entity
 */
class University
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=255)
     */
    private $country;

    /**
     * @var boolean
     *
     * @ORM\Column(name="europe", type="boolean")
     */
    private $europe;

    /**
     * @var boolean
     *
     * @ORM\Column(name="dual_degree", type="boolean")
     */
    private $dualDegree;

    /**
     * @var integer
     *
     * @ORM\Column(name="places", type="integer")
     */
    private $places;

    /**
     * @var boolean
     *
     * @ORM\Column(name="partnership_state", type="boolean")
     */
    private $partnershipState;


    public function __construct() {
        $this->europe = true;
        $this->dualDegree = false;
        $this->places = -1;
        $this->partnershipState = true;
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
     * Set name
     *
     * @param string $name
     * @return University
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
     * Set country
     *
     * @param string $country
     * @return University
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string 
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set places
     *
     * @param integer $places
     * @return University
     */
    public function setPlaces($places)
    {
        $this->places = $places;

        return $this;
    }

    /**
     * Get places
     *
     * @return integer 
     */
    public function getPlaces()
    {
        return $this->places;
    }

    /**
     * Set europe
     *
     * @param boolean $europe
     * @return University
     */
    public function setEurope($europe)
    {
        $this->europe = $europe;

        return $this;
    }

    /**
     * Get europe
     *
     * @return boolean 
     */
    public function getEurope()
    {
        return $this->europe;
    }

    /**
     * Set dualDegree
     *
     * @param boolean $dualDegree
     * @return University
     */
    public function setDualDegree($dualDegree)
    {
        $this->dualDegree = $dualDegree;

        return $this;
    }

    /**
     * Get dualDegree
     *
     * @return boolean 
     */
    public function getDualDegree()
    {
        return $this->dualDegree;
    }

    /**
     * Set partnershipState
     *
     * @param boolean $partnershipState
     * @return University
     */
    public function setPartnershipState($partnershipState)
    {
        $this->partnershipState = $partnershipState;

        return $this;
    }

    /**
     * Get partnershipState
     *
     * @return boolean 
     */
    public function getPartnershipState()
    {
        return $this->partnershipState;
    }
}
