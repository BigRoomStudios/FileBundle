<?php

namespace BRS\FileBundle\Core;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use BRS\CoreBundle\Core\SuperEntity;
use BRS\FileBundle\Entity\File;
use BRS\CoreBundle\Core\Utility as BRS;


/**
 * BRS\FileBundle\Core\AttachedFileEntity
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 */
abstract class AttachedFileEntity extends SuperEntity
{
	/*
	 * name of root folder that holds all entity sub-folders
	 */
    private $root_folder_name;
	
	/**
     * @var integer $dir_id
     *
     * @ORM\Column(name="dir_id", type="integer", nullable=TRUE)
     */
    public $dir_id;
	
	/*
	 * @ORM\OneToOne(targetEntity="BRS\FileBundle\Entity\File", cascade={"all"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="dir_id", referencedColumnName="id")
	 */
	public $directory;
	
	/**
     * Get folder title as id if no getTitle function exists on child
     *
     * @return string
     */
	public function getTitle(){
		
		return $this->id;
	}
	
	/**
     * Get name of folder to create for this entity  
     *
     * @return string
     */
	public function getFolderName(){
		
		return $this->getTitle();
	}
	
	/**
	 * @ORM\PreRemove
	 */
	public function removeDirectory()
	{
		die('remove');
					
		$dir_id = $this->getDirId();
		
		if($dir_id){
		
			$dir = $this->em->getReference('\BRS\FileBundle\Entity\File', $dir_id);
			
			$this->em->remove($dir);
		}
	}
	
	/**
	 * @ORM\PostUpdate
	 */
	public function updateDirectory()
	{
			
		die('update');
					
		$dir_id = $this->getDirId();
		
		if($dir_id){
				
			$dir = $this->em->getReference('\BRS\FileBundle\Entity\File', $dir_id);
			
			$folder_name = $this->getFolderName();
		
			$dir->setName($folder_name);
			
			$this->em->persist($dir);
			
			$this->em->flush();
		}
	}
	
	/**
	 * @ORM\PrePersist
	 */
	public function createDirectory()
	{	
		$dir = new File();
		
		//$parent_id = self::PARENT_DIR_ID;
		
		die('here');
		
		$parent = $this->em->getRepository('BRSFileBundle:File')->getRootByName($this->root_folder_name);
		
		if($parent){
		
			$dir->setParent($parent);
		
			$dir->setIsDir(true);
			
			$folder_name = $this->getFolderName();
			
			$dir->setName($folder_name);
				
			$this->directory = $dir;
			
			$this->em->persist($dir);
			
			$this->em->flush();
			
			$this->setDirId($dir->id);
		}
	}
	
	/**
     * Get driectory
     *
     * @return BRS\FileBundle\Entity\File $dir
     */
    public function getDirectory()
    {
    	$dir_id = $this->getDirId();
		
		if($dir_id){
				
			$dir = $this->em->getRepository('BRSFileBundle:File')->findOneById($dir_id);
			
			return $dir;
		}
    }
	
    /**
     * Set dir_id
     *
     * @param integer $dirId
     */
    public function setDirId($dirId)
    {
        $this->dir_id = $dirId;
    }

    /**
     * Get dir_id
     *
     * @return integer 
     */
    public function getDirId()
    {
        return $this->dir_id;
    }
    public function __construct()
    {
        $this->members = new \Doctrine\Common\Collections\ArrayCollection();
    }
}