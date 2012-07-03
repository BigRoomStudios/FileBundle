<?php

namespace BRS\FileBundle\Entity;

use BRS\CoreBundle\Core\SuperEntity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Request;

/**
 * BRS\FileBundle\Entity\File
 *
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
     */
    public $created_time;

    /**
     * @var datetime $modified_time
     *
     * @ORM\Column(name="modified_time", type="datetime", nullable=true)
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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $path;
	
	/**
	 * upload size set to 512 MB
	 * also need to set limit in php.ini upload_max_filesize and post_max_size
	 * 
     * @Assert\File(maxSize="536870912")
     */
    public $file;
	
	
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
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
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
		
		if(!file_exists($cache_path)){
			
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set filename
     *
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Get filename
     *
     * @return string 
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set ext
     *
     * @param string $ext
     */
    public function setExt($ext)
    {
        $this->ext = $ext;
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

    /**
     * Set size
     *
     * @param integer $size
     */
    public function setSize($size)
    {
        $this->size = $size;
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
}