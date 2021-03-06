<?php

namespace BRS\FileBundle\Entity;

use BRS\CoreBundle\Core\SuperEntity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Request;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * BRS\FileBundle\Entity\File
 * 
 * @Gedmo\Tree(type="nested")
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="BRS\FileBundle\Repository\FileRepository")
 * @ORM\HasLifecycleCallbacks
 */
class File extends SuperEntity
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    public $name;
    
    /**
     * @var string $class_root
     *
     * @ORM\Column(name="class_root", type="string", length=255, nullable=true, unique = true)
     */
    public $class_root;

    /**
     * @var string $ext
     *
     * @ORM\Column(name="ext", type="string", length=4, nullable=true)
     */
    public $ext;

    /**
     * @var integer $size
     *
     * @ORM\Column(name="size", type="integer", nullable=true)
     */
    public $size;

    /**
     * @var string $type
     *
     * @ORM\Column(name="type", type="string", length=50, nullable=true)
     */
    public $type;

    /**
     * @var integer $width
     *
     * @ORM\Column(name="width", type="integer", nullable=true)
     */
    public $width;

    /**
     * @var integer $height
     *
     * @ORM\Column(name="height", type="integer", nullable=true)
     */
    public $height;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    public $title;

    /**
     * @var text $description
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    public $description;

    /**
     * @var string $url
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     */
    public $url;

    /**
     * @var datetime $created_time
     *
     * @ORM\Column(name="created_time", type="datetime", nullable=true)
	 * @Gedmo\Timestampable(on="create")
     */
    public $created_time;

    /**
     * @var datetime $modified_time
     *
     * @ORM\Column(name="modified_time", type="datetime", nullable=true)
	 * @Gedmo\Timestampable(on="update")
     */
    public $modified_time;

    /**
     * @var string $permissions
     *
     * @ORM\Column(name="permissions", type="string", length=10, nullable=true)
     */
    public $permissions;

    /**
     * @var integer $owner_id
     *
     * @ORM\Column(name="owner_id", type="integer", nullable=true)
     */
    public $owner_id;

    /**
     * @var integer $group_id
     *
     * @ORM\Column(name="group_id", type="integer", nullable=true)
     */
    public $group_id;
	
	/**
     * @var integer $parent_id
     *
     * @ORM\Column(name="parent_id", type="integer", nullable=true)
     */
    public $parent_id;
	
	/**
     * @var boolean $is_dir
     *
     * @ORM\Column(name="is_dir", type="boolean", nullable=true)
     */
    public $is_dir;
	
	
	/**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="tree_left", type="integer")
     */
    public $tree_left;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="tree_level", type="integer")
     */
    public $tree_level;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="tree_right", type="integer")
     */
    public $tree_right;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     */
    public $root;
  
    /**
	 * @Gedmo\TreeParent
	 * @ORM\ManyToOne(targetEntity="File", inversedBy="children", cascade={"persist"})
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
	 * })
	 * 
	 * I think onDelete="SET NULL" fixes problems with removing tree nodes and their children
	 * I had onDelete="DELETE" and that did not seem to work right
	 * 
	 */
    public $parent;

    /**
     * @ORM\OneToMany(targetEntity="File", mappedBy="parent")
     * @ORM\OrderBy({"tree_left" = "ASC"})
     */
    public $children;
	
	
	/**
	 * upload size set to 512 MB
	 * also need to set limit in php.ini upload_max_filesize and post_max_size
	 * 
     * @Assert\File(maxSize="536870912")
     */
    public $file;
	
	
	public $is_tree = true;
	
	
	public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
    }
	
	public function getAbsolutePath()
    {
        return $this->getOriginalsDir() . '/' . $this->id . '.' . $this->ext;
    }
	
    public function getWebPath()
    {
        return '/file/' . $this->id . '/' . $this->name;
    }

    public function getFilesRootDir()
    {
        return __DIR__.'/../../../../files';
    }
	
    public function getCacheDir()
    {
        return $this->getFilesRootDir() . '/cache';
    }
	
    public function getOriginalsDir()
    {
        return $this->getFilesRootDir() . '/originals';
    }

    
	/**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->file) {
            
            $this->ext = $this->file->guessExtension();
			$this->name = $this->file->getClientOriginalName();
			$this->type = $this->file->getMimeType();
			$this->size = $this->file->getClientSize();
			
			if($image_size = getimagesize($this->file->getRealPath())) {
				
				$this->width = $image_size[0];
				$this->height = $image_size[1];
				
			}
			
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->file) {
            return;
        }

        // you must throw an exception here if the file cannot be moved
        // so that the entity is not persisted to the database
        // which the UploadedFile move() method does
        $this->file->move($this->getOriginalsDir(), $this->id . '.' . $this->ext);

        unset($this->file);
    }

    /**
     * @ORM\PreRemove()
     */
    public function removeUpload()
    {
        if (!$this->is_dir && $file = $this->getAbsolutePath()) {
            if(is_file($file)){
            	unlink($file);
			}
        }
    }

    /**
     * Returns a cache name based on parameters
     *
     * @return string 
     */
    public function getCacheName($params)
    {
    	$ext = $this->ext;	
			
    	if($params['ext']){
    		
    		$ext = $params['ext'];
    	}
					
    	return $this->id . '_' . md5(implode('', $params)) . '.' . $ext;
	}
	
    /**
     * Returns a cache name based on parameters
     *
     * @return string 
     */
    public function getCachePath($params)
    {
    	$cache_dir = $this->getCacheDir();
			
    	$cache_name = $this->getCacheName($params);
		
		return $cache_dir . '/' . $cache_name;
	}
	
	/**
     * Returns a path to a resized image for a specified widht and height
     *
     * @return string 
     */
    public function getResizedCachePath($width, $height, $params = null)
    {
		if(!isset($params['ext'])){
			
			$params['ext'] = 'jpg';
		}	
			
		return $this->getCachePath(array_merge((array)$params, array('resize', $width, $height)));
	}
	
    /**
     * Creates a new resized image if necessary and returns a path to the cached file
     *
     * @return string 
     */
    public function getResizedImage($width, $height, $params = null)
    {
		
		if(!$params){
			
			$params = array();
		}
			
		if(!isset($params['ext'])){
			
			$params['ext'] = 'jpg';
		}
			
		if(!isset($params['quality'])){
			
			$params['quality'] = 80;
		}
		
		if(!isset($params['blur'])){
			
			$params['blur'] = 0.9;
		}
			
		$real_path = $this->getAbsolutePath();
		
		$cache_path = $this->getResizedCachePath($width, $height, $params);
		
		if(!file_exists($cache_path) && $this->getWidth()){
			
			$image = new \Imagick($real_path);
			
			$bestfit = false;
			
			if($width && $height){
				
				$bestfit = true;
			}
			
			if(isset($params['crop'])){
				
				if(!($width && $height)){
				
					return false;
				}
				
				// crop the image
				
				$geo = $image->getImageGeometry();
				
				if(($geo['width']/$width) < ($geo['height']/$height)){
					
				    $image->cropImage($geo['width'], floor($height*$geo['width']/$width), 0, (($geo['height']-($height*$geo['width']/$width))/2));
				
				}else{
							
				    $image->cropImage(ceil($width*$geo['height']/$height), $geo['height'], (($geo['width']-($width*$geo['height']/$height))/2), 0);
				}
				
				$bestfit = false;
			}
		
			$image->resizeImage($width, $height, \Imagick::FILTER_CATROM, $params['blur'], $bestfit);
			
			$image->setCompression(\Imagick::COMPRESSION_JPEG);
			
			$image->setCompressionQuality($params['quality']);
			
			$image->setImageFormat($params['ext']);
			
			$image->stripImage(); 
			
			$image->writeImage($cache_path);
		}
		
		if(file_exists($cache_path)){
			
			return $cache_path;
		}
    }

    /**
     * Set class_root
     * 
     * Can only be done on a root node that is NEW.
     * 
     * @param Object
     */
    public function setClassRoot($object = null)
    {
    	if (empty($object) || !is_object($object))
    		throw new \Exception('$page->setClassRoot($param1) expects parameter 1 to be an object, '.gettype($object).' given.');
    	elseif (!empty($this->id))
    		throw new \Exception('$page->setClassRoot($param1) can only be called on a new Entity.  Tried to call on entity: '.$this->id);
    	
    	$class = get_class($object);
    	
    	$class_title = substr($class, strrpos($class, "\\")+1);
    	
    	switch (substr($class_title,-1)){
    		case 'y':
    			$class_title = substr($class_title,0,-1).'ies';
    			break;
    		case 'h':
    		case 's':
    			$class_title .= 'es';
    			break;
    		default:
    			$class_title .= 's';
    			break;
    	}
    	
    	$this->name = ucfirst($class_title).' Root';
    	$this->class_root = $class;
    	
    	return $this;
    }
    
    /**
     * Set class_root
     *
     * Can only be done on a root node that is NEW.
     *
     * @param Object
     */
    public function isClassRoot($object = null)
    {
    	if (empty($object) || !is_object($object))
    		throw new \Exception('$page->setClassRoot($param1) expects parameter 1 to be an object, '.gettype($object).' given.');
    	
    	if (get_class($object) == $this->class_root)
    		return true;
    	return false;
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
     * Set ext
     *
     * @param string $ext
     */
    public function setExt($ext)
    {
        $this->ext = $ext;
        
        return $this;
    }

    /**
     * Get ext
     *
     * @return string 
     */
    public function getExt()
    {
        return $this->ext;
    }
	
	public function isImage() {
		
		$ext = $this->getExt();
		
		if($this->getWidth()) {
			return true;
		}
		
		return false;
		
	}
	
    /**
     * Set size
     *
     * @param integer $size
     */
    public function setSize($size)
    {
        $this->size = $size;
        
        return $this;
    }

    /**
     * Get size
     *
     * @return integer 
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
        
        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set width
     *
     * @param integer $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
        
        return $this;
    }

    /**
     * Get width
     *
     * @return integer 
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height
     *
     * @param integer $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
        
        return $this;
    }

    /**
     * Get height
     *
     * @return integer 
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
        
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param text $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
        
        return $this;
    }

    /**
     * Get description
     *
     * @return text 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set url
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
        
        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set created_time
     *
     * @param datetime $createdTime
     */
    public function setCreatedTime($createdTime)
    {
        $this->created_time = $createdTime;
        
        return $this;
    }

    /**
     * Get created_time
     *
     * @return datetime 
     */
    public function getCreatedTime()
    {
        return $this->created_time;
    }

    /**
     * Set modified_time
     *
     * @param datetime $modifiedTime
     */
    public function setModifiedTime($modifiedTime)
    {
        $this->modified_time = $modifiedTime;
        
        return $this;
    }

    /**
     * Get modified_time
     *
     * @return datetime 
     */
    public function getModifiedTime()
    {
        return $this->modified_time;
    }

    /**
     * Set permissions
     *
     * @param string $permissions
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
        
        return $this;
    }

    /**
     * Get permissions
     *
     * @return string 
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * Set owner_id
     *
     * @param integer $ownerId
     */
    public function setOwnerId($ownerId)
    {
        $this->owner_id = $ownerId;
        
        return $this;
    }

    /**
     * Get owner_id
     *
     * @return integer 
     */
    public function getOwnerId()
    {
        return $this->owner_id;
    }

    /**
     * Set group_id
     *
     * @param integer $groupId
     */
    public function setGroupId($groupId)
    {
        $this->group_id = $groupId;
        
        return $this;
    }

    /**
     * Get group_id
     *
     * @return integer 
     */
    public function getGroupId()
    {
        return $this->group_id;
    }

    /**
     * Set is_dir
     *
     * @param integer $is_dir
     */
    public function setIsDir($is_dir)
    {
        $this->is_dir = $is_dir;
        
        return $this;
    }

    /**
     * Get is_dir
     *
     * @return is_dir 
     */
    public function getIsDir()
    {
        return $this->is_dir;
    }
	
    /**
     * Set parent
     *
     * @param BRS\FileBundle\Entity\File $parent
     */
    public function setParent(\BRS\FileBundle\Entity\File $parent)
    {
        $this->parent = $parent;
        $this->parent_id = $parent->getId();
        
        return $this;
    }

    /**
     * Get parent
     *
     * @return BRS\FileBundle\Entity\File 
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add children
     *
     * @param BRS\FileBundle\Entity\File $children
     */
    public function addFile(\BRS\FileBundle\Entity\File $children)
    {
        $this->children[] = $children;
        
        return $this;
    }

    /**
     * Get children
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set parent_id
     *
     * @param integer $parentId
     */
    public function setParentId($parentId)
    {
        $this->parent_id = $parentId;
        
        return $this;
    }

    /**
     * Get parent_id
     *
     * @return integer 
     */
    public function getParentId()
    {
        return $this->parent_id;
    }

    /**
     * Set tree_left
     *
     * @param integer $treeLeft
     */
    public function setTreeLeft($treeLeft)
    {
        $this->tree_left = $treeLeft;
    }

    /**
     * Get tree_left
     *
     * @return integer 
     */
    public function getTreeLeft()
    {
        return $this->tree_left;
    }

    /**
     * Set tree_level
     *
     * @param integer $treeLevel
     */
    public function setTreeLevel($treeLevel)
    {
        $this->tree_level = $treeLevel;
    }

    /**
     * Get tree_level
     *
     * @return integer 
     */
    public function getTreeLevel()
    {
        return $this->tree_level;
    }

    /**
     * Set tree_right
     *
     * @param integer $treeRight
     */
    public function setTreeRight($treeRight)
    {
        $this->tree_right = $treeRight;
    }

    /**
     * Get tree_right
     *
     * @return integer 
     */
    public function getTreeRight()
    {
        return $this->tree_right;
    }

    /**
     * Set root
     *
     * @param integer $root
     */
    public function setRoot($root)
    {
        $this->root = $root;
    }

    /**
     * Get root
     *
     * @return integer 
     */
    public function getRoot()
    {
        return $this->root;
    }
	
	/**
	 * Convert this file into a swift attachment
	 * 
	 * @return a swift attachment
	 */
	public function toSwiftAttachment() {
		
		//create the swift attachment
		$attachment = \Swift_Attachment::fromPath($this->getAbsolutePath())->setFilename($this->getName());
		
		//return the attachment
		return $attachment;
		
	}
	
}