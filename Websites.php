<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Websites
 *
 * @ORM\Table(name="websites", uniqueConstraints={@ORM\UniqueConstraint(name="idx_url", columns={"url"})}, indexes={@ORM\Index(name="account_id", columns={"account_id"})})
  * @ORM\Entity(repositoryClass="App\Repository\WebsitesRepository")
 */
class Websites
{
/**     * @var integer     *     * @ORM\Column(name="hostedpackage_id", type="integer", nullable=false)     */
    private $hostedpackageId;/**     * @var string     *     * @ORM\Column(name="url", type="string", length=255, nullable=false)     */
    private $url;/**     * @var integer     *     * @ORM\Column(name="enabled", type="integer", nullable=false)     */
    private $enabled;/**     * @var boolean     *     * @ORM\Column(name="suspended", type="boolean", nullable=false)     */
    private $suspended;/**     * @var datetime     *     * @ORM\Column(name="added", type="datetime")     */
    private $added;/**     * @var datetime     *     * @ORM\Column(name="lastcallbacktime", type="datetime")     */
    private $lastcallbacktime;	/**     * @var integer     *     * @ORM\Column(name="id", type="integer")     * @ORM\Id     * @ORM\GeneratedValue(strategy="IDENTITY")     */
    private $id;	 /**     * @var \App\Entity\Accounts     * @ORM\ManyToOne(targetEntity="App\Entity\Accounts", inversedBy="websites")     * @ORM\JoinColumns({     *   @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)     * })     */
    private $account;

    public function __construct()
    {
        $this->hostedpackageId = 0;
        $this->added = new \DateTime('now');
        $this->lastcallbacktime = new \DateTime(strtotime('0000-00-00 00:00:00'));
        $this->enabled = 1;
        $this->suspended = 0;
    }


    /**
     * @param \App\Entity\Accounts $account
     */
    public function setAccount($account)
    {
        $this->account = $account;
        return $this;
    }

    /**
     * @return \App\Entity\Accounts
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param \DateTime $added
     */
    public function setAdded($added)
    {
        $this->added = $added;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getAdded()
    {
        return $this->added;
    }

    /**
     * @param int $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return int
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param int $hostedpackageId
     */
    public function setHostedpackageId($hostedpackageId)
    {
        $this->hostedpackageId = $hostedpackageId;
        return $this;
    }

    /**
     * @return int
     */
    public function getHostedpackageId()
    {
        return $this->hostedpackageId;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \DateTime $lastcallbacktime
     */
    public function setLastcallbacktime($lastcallbacktime)
    {
        $this->lastcallbacktime = $lastcallbacktime;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastcallbacktime()
    {
        return $this->lastcallbacktime;
    }

    /**
     * @param boolean $suspended
     */
    public function setSuspended($suspended)
    {
        $this->suspended = $suspended;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getSuspended()
    {
        return $this->suspended;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }





}
