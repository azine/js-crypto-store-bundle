<?php

namespace Azine\JsCryptoStoreBundle\Entity;

class EncryptedFile
{
    public function setCreatedValue()
    {
        $this->created = new \DateTime();
    }

    //////////////////////////// Generated Code Below This Line ////////////////////////////////

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $token;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $expiry;

    /**
     * @var string|null
     */
    private $file;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var int|null
     */
    private $owner_id;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set token.
     *
     * @param string $token
     *
     * @return EncryptedFile
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return EncryptedFile
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set expiry.
     *
     * @param \DateTime $expiry
     *
     * @return EncryptedFile
     */
    public function setExpiry($expiry)
    {
        $this->expiry = $expiry;

        return $this;
    }

    /**
     * Get expiry.
     *
     * @return \DateTime
     */
    public function getExpiry()
    {
        return $this->expiry;
    }

    /**
     * Set file.
     *
     * @param string|null $file
     *
     * @return EncryptedFile
     */
    public function setFile($file = null)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get file.
     *
     * @return string|null
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return EncryptedFile
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set ownerId.
     *
     * @param int|null $ownerId
     *
     * @return EncryptedFile
     */
    public function setOwnerId($ownerId = null)
    {
        $this->owner_id = $ownerId;

        return $this;
    }

    /**
     * Get ownerId.
     *
     * @return int|null
     */
    public function getOwnerId()
    {
        return $this->owner_id;
    }

    /**
     * @var string
     */
    private $salt;

    /**
     * Set salt.
     *
     * @param string $salt
     *
     * @return EncryptedFile
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt.
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @var string
     */
    private $iv;

    /**
     * Set iv.
     *
     * @param string $iv
     *
     * @return EncryptedFile
     */
    public function setIv($iv)
    {
        $this->iv = $iv;

        return $this;
    }

    /**
     * Get iv.
     *
     * @return string
     */
    public function getIv()
    {
        return $this->iv;
    }

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string
     */
    private $mimeType;

    /**
     * Set fileName.
     *
     * @param string $fileName
     *
     * @return EncryptedFile
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Get fileName.
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set mimeType.
     *
     * @param string $mimeType
     *
     * @return EncryptedFile
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * Get mimeType.
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @var string
     */
    private $groupToken;

    /**
     * Set groupToken.
     *
     * @param string $groupToken
     *
     * @return EncryptedFile
     */
    public function setGroupToken($groupToken)
    {
        $this->groupToken = $groupToken;

        return $this;
    }

    /**
     * Get groupToken.
     *
     * @return string
     */
    public function getGroupToken()
    {
        return $this->groupToken;
    }
}
