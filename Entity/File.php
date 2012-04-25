<?php

namespace BRS\FileBundle\Entity;

use BRS\CoreBundle\Core\SuperEntity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @Assert\File(maxSize="6000000")
     */
    public $file;
	
	

    public function getRealPath()
    {
        return $this->getUploadRootDir() . '/' . $this->id . '.' . $this->ext;
    }
	
    public function getWebPath()
    {
        return '/file/' . $this->id . '/' . $this->name;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded documents should be saved
        return __DIR__.'/../../../../files';
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw when displaying uploaded doc/image in the view.
        return '';
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
        $this->file->move($this->getRealPath());

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

    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir().'/'.$this->id.'.'.$this->ext;
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