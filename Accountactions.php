<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Accountactions
 *
 * @ORM\Table(name="accountactions", indexes={@ORM\Index(name="account_id", columns={"account_id"}), @ORM\Index(name="created", columns={"created"})})
 * @ORM\Entity
 */
class Accountactions
{
    /**
     * @var \App\Entity\Accounts
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Accounts", inversedBy="accountactions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $account;

    /**
     * @var integer
     *
     * @ORM\Column(name="website_id", type="integer", nullable=false)
     */
    private $websiteId;

    /**
     * @var integer
     *
     * @ORM\Column(name="payment_id", type="integer")
     */
    private $paymentId;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="string", length=255, nullable=false)
     */
    private $message;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var string
     *
     * @ORM\Column(name="ip", type="string", length=255, nullable=false)
     */
    private $ip;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    public function __construct()
    {
        $this->ip = $_SERVER['REMOTE_ADDR'];
        $this->created = new \DateTime('now');
    }

    /**
     * @param \DateTime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
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
     * @param string $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param int $paymentId
     */
    public function setPaymentId($paymentId)
    {
        $this->paymentId = $paymentId;
        return $this;
    }

    /**
     * @return int
     */
    public function getPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $websiteId
     */
    public function setWebsiteId($websiteId)
    {
        $this->websiteId = $websiteId;
        return $this;
    }

    /**
     * @return int
     */
    public function getWebsiteId()
    {
        return $this->websiteId;
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


}
