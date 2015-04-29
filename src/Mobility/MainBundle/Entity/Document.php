<?php

namespace Mobility\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Document
 *
 * @ORM\Table(name="files")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class Document
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
     * @Assert\NotBlank()
     */
    private $name;
    
    /**
     * @Assert\File(maxSize="20M")
     */
    private $file;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $location;


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
     * @return Document
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
     * Set location
     *
     * @param string $location
     * @return Document
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return string 
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set file
     *
     * @param Document $file
     * @return Document
     */
    public function setFile(File $file = null)
    {
        $this->file = $file;
    
        return $this;
    }

    /**
     * Get file
     *
     * @return Document 
     */
    public function getFile()
    {
        return $this->file;
    }
    
    public function getAbsolutePath() {
        return null === $this->name ? null : $this->getUploadRootDir() . '/' . $this->name;
    }
    
    public function getWebPath() {
        return null === $this->name ? null : $this->getUploadDir() . '/' . $this->name;
    }
    
    protected function getUploadRootDir() {
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }
    
    protected function getUploadDir() {
        return 'assets/files';
    }
    
    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function saveUpload() {
        if (null === $this->file) {
            return;
        }
        
        $this->file->move($this->getUploadRootDir(), $this->name);
        unset($this->file);
    }
    
    /**
     * @ORM\PostRemove()
     */
    public function removeUpload() {
        $file = $this->getAbsolutePath();
        if ($file) {
            unlink($file);
        }
    }
}
