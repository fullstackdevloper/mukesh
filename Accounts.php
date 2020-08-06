<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Security\Core\User\UserInterface;
/** 
 * Accounts
 * 
 * @ORM\Table(name="accounts") 
 * @ORM\Entity(repositoryClass="App\Repository\AccountsRepository")
 */
class Accounts implements UserInterface
{
	/**     * @var string     *     * @ORM\Column(name="firstname", type="string", length=255, nullable=false)     */
    protected $firstname;
	/**     * @var string     *     * @ORM\Column(name="lastname", type="string", length=255, nullable=false)     */
    protected $lastname;
	 /**

     * @var string

     *

     * @ORM\Column(name="email", type="string", length=255, nullable=false)

     */
    protected $email;
	 /**

     * @var string

     *

     * @ORM\Column(name="password", type="string", length=255, nullable=false)

     */
    
    protected $password;
	 /**
     * @var string
     *
     * @ORM\Column(name="passwordplain", type="string", length=255, nullable=false)
     */
    protected $passwordplain;
	 /**
     * @var integer
     *
     * @ORM\Column(name="enabled", type="integer", nullable=false)
     */
    protected $enabled;
	 /**
     * @var integer
     *
     * @ORM\Column(name="premium", type="integer", nullable=false)
     */
    protected $premium;
//    protected $recurring;
  /**
     * @var integer
     *
     * @ORM\Column(name="developer", type="integer", nullable=false)
     */
    protected $developer;
	 /**
     * @var integer
     *
     * @ORM\Column(name="refundperiod", type="integer", nullable=false)
     */
    protected $refundperiod;
	 /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    protected $created;
	/**
     * @var \DateTime
     *
     * @ORM\Column(name="paid", type="datetime", nullable=true)
     */
    protected $paid;
	/**
     * @var \DateTime
     *
     * @ORM\Column(name="refunded", type="datetime", nullable=true)
     */
    protected $refunded;
	/**
     * @var \DateTime
     *
     * @ORM\Column(name="protrainingstart", type="datetime", nullable=true)
     */
    protected $protrainingstart;	
	/**
     * @var string
     *
     * @ORM\Column(name="ip", type="string", length=255, nullable=false)
     */
    protected $ip;
	/**
     * @var text
     *
     * @ORM\Column(name="debug", type="text",  nullable=false)
     */
    protected $debug;
	/**
     * @var string
     *
     * @ORM\Column(name="referralcode", type="string", length=255, nullable=false)
     */
    protected $referralcode;
	/**
     * @var string
     *
     * @ORM\Column(name="package", type="string", length=255, nullable=false)
     */
    protected $package;
	/**
     * @var string
     *
     * @ORM\Column(name="tracker", type="string", length=255, nullable=false)
     */
    protected $tracker;
	/**
     * @var string
     *
     * @ORM\Column(name="signuppage", type="string", length=255, nullable=false)
     */
    protected $signuppage;
	/**
     * @var string
     *
     * @ORM\Column(name="wsohash", type="string", length=255, nullable=false)
     */
    protected $wsohash;
	 /**
     * @var integer
     *
     * @ORM\Column(name="wsohashused", type="integer", nullable=false)
     */
    protected $wsohashused;
	/**
     * @var string
     *
     * @ORM\Column(name="mailchimp_main_unsubscribed", type="string", length=255, nullable=false)
     */
    protected $mailchimpMainUnsubscribed;
	/**
     * @var string
     *
     * @ORM\Column(name="avatar", type="string", length=255, nullable=false)
     */
    protected $avatar;
	 /**
     * @var integer
     *
     * @ORM\Column(name="forumid", type="integer", nullable=false)
     */
    protected $forumid;
	 /**
     * @var integer
     *
     * @ORM\Column(name="jvzoohash", type="integer", nullable=false)
     */
    protected $jvzoohash;
	/**
     * @var string
     *
     * @ORM\Column(name="jvzoohashused", type="string", length=255, nullable=false)
     */
    protected $jvzoohashused;
	 /**
     * @var integer
     *
     * @ORM\Column(name="paypalhash", type="integer", nullable=false)
     */
	protected $paypalhash;
	/**
     * @var string
     *
     * @ORM\Column(name="paypalhashused", type="string", length=255, nullable=false)
     */
	protected $paypalhashused;
	/**
     * @var \DateTime
     *
     * @ORM\Column(name="termsaccepted", type="datetime", nullable=true)
     */
	protected $termsaccepted;
	/**
     * @var string
     *
     * @ORM\Column(name="jvzooref", type="string", length=255, nullable=false)
     */
    protected $jvzooref;
	/**
     * @var string
     *
     * @ORM\Column(name="fwhaccountkey", type="string", length=255, nullable=false)
     */
    protected $fwhaccountkey;	
	/**     * @var integer     *     * @ORM\Column(name="id", type="integer")     * @ORM\Id     * @ORM\GeneratedValue(strategy="IDENTITY")     */
    protected $id;
	
    protected $affiliatetracker;

    protected $trackerclicks;
	
    protected $accountaddresses;
	
    protected $accountactions;
	/**   
	* @ORM\OneToMany(targetEntity="App\Entity\Developeraccounts", mappedBy="account")   
	*/	
    protected $developeraccounts;
	/**   
	* @ORM\OneToMany(targetEntity="App\Entity\Developeraccountslog", mappedBy="account")   
	*/	
    protected $developeraccountslog;
	
    protected $premiumaccounts;
//    protected $recurringaccounts;

    protected $premiumaccountslog;
	
    protected $payments;
	/**   
	* @ORM\OneToMany(targetEntity="App\Entity\Developeraccountclients", mappedBy="client")   
	*/	
    protected $developeraccountclients;
	/**   
	* @ORM\OneToMany(targetEntity="App\Entity\Websites", mappedBy="account")   
	*/	
    protected $websites;
	/**
     * @var integer
     *
     * @ORM\Column(name="websites", type="integer", nullable=false)
     */
    protected $maxWebsites;
	 /**
     * @ORM\OneToMany(targetEntity="App\Entity\OtoShown", mappedBy="account")
     */
    protected $otoshown;
	/**
     * @var string
     *
     * @ORM\Column(name="versionbought", type="string", length=255, nullable=false)
     */
    protected $versionbought;


    public function __construct($version=null)
    {
        $this->websites = new \Doctrine\Common\Collections\ArrayCollection();
        $this->premiumaccounts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->developeraccountclients = new \Doctrine\Common\Collections\ArrayCollection();
        $this->created = new \DateTime('now');
        $this->passwordplain='';
        $this->enabled = 1;
        $this->premium = 0;
        $this->developer = 0;
        $this->maxWebsites = 0;
        $this->refundperiod = 0;
        $this->paid = NULL;
        $this->refunded = NULL;
        $this->protrainingstart = NULL;
        $this->ip = $_SERVER['REMOTE_ADDR'];
        $this->debug = '';
        $this->referralcode = '';
        $this->package = '';
        $this->tracker='';
        $this->signuppage='';
        $this->wsohash='';
        $this->wsohashused=0;
        $this->mailchimpMainUnsubscribed=0;
        $this->avatar = '';
        $this->forumid='';
        $this->jvzoohash='';
        $this->jvzoohashused=0;
        $this->jvzooref='';
        $this->fwhaccountkey='';
		$this->versionbought = $version;
		$this->paypalhash = '';
		$this->paypalhashused = '0';
		$this->termsaccepted = NULL;
    }



    public function getRoles()
    {
        # Determine what rights the user has
        # based on various account information

        $roles = array();

        if ($this->getEnabled()=='1') $roles[] = 'ROLE_USER';
        if ($this->getPremium()=='1') $roles[] = 'ROLE_PREMIUM';
//        if (($this->getMaxWebsites()>0 )) $roles[] = 'ROLE_CUSTOMER';
        if ($this->getAffiliatetracker()) $roles[] = 'ROLE_AFFILIATE';
        if ($this->getMaxWebsites()>=100) $roles[] = 'ROLE_UNLIMITED';
//        if ($this->getRecurring()==1) $roles[] = 'ROLE_SUBSCRIBER';

        # Determine if the User is a customer
        $zerowebsites = ($this->getMaxWebsites()<=0);
//        $paid = ($this->getPaid() != new \DateTime('0000-00-00 00:00:00'));
        $enabled = ($this->enabled==1);

        if ($enabled AND !$zerowebsites) $roles[] = 'ROLE_CUSTOMER';


        if($this->getDeveloper()==1)
        {
            $roles[] = 'ROLE_RESELLER';
            $roles[] = 'ROLE_DEVELOPER';
            $roles[] = 'ROLE_CUSTOMER';
        }

        # some logical test for this - may need to be added later
        $roles[] = 'ROLE_FOUNDER';


        # If this exist in the Database they are a user
        # This role is required to authenticate
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles($roles)
    {
        $this->roles = $roles;
    }


    public function getUsername()
    {
        return $this->email;
    }
    public function getSalt()
    {
        return '';
    }
    public function eraseCredentials()
    {

    }


    public function addWebsite(\App\Entity\Websites $websites)
    {
        $this->websites[] = $websites;
        $websites->setAccount($this);
        return $this;
    }

    public function removeWebsite(\App\Entity\Websites $websites)
    {
        $this->websites->removeElement($websites);
    }

    public function getWebsiteCount()
    {
        return sizeof($this->websites);
    }

    public function canUpgrade()
    {
        return ($this->getMaxWebsites() < 100);
    }

    public function getLicenseDetails(){
        $unlimited = false;
        if ($this->getMaxWebsites() >=100) $unlimited= true;

        if ($unlimited){
            $total = 'Unlimited';
            $used = count($this->getWebsites());
            $remaining = 'Unlimited';
        }else {
            $total = $this->getMaxWebsites();
            $used = count($this->getWebsites());
            $remaining = intval($total) - intval($used);
            $remaining = ($remaining<0) ? 0 : $remaining;
        }

        return array(
            'total' => $total,
            'remaining' => $remaining,
            'used' => $used
        );
    }

    public function hasStoresRemaining()
    {
        $details = $this->getLicenseDetails();

        if ($details['remaining'] === 'Unlimited') return true;
        if (is_numeric($details['remaining']) AND $details['remaining'] > 0) return true;

        return false;
    }


    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
        return $this;
    }


    public function getAvatar()
    {
        return $this->avatar;
    }


    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }


    public function getCreated()
    {
        return $this->created;
    }

    public function setDebug($debug)
    {
        $this->debug = $debug;
        return $this;
    }

    public function getDebug()
    {
        return $this->debug;
    }


    public function setDeveloper($developer)
    {
        $this->developer = $developer;
        return $this;
    }


    public function getDeveloper()
    {
        return $this->developer;
    }


    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
        return $this;
    }

    public function getFirstname()
    {
        return $this->firstname;
    }

    public function setForumid($forumid)
    {
        $this->forumid = $forumid;
        return $this;
    }

    public function getForumid()
    {
        return $this->forumid;
    }

    public function setFwhaccountkey($fwhaccountkey)
    {
        $this->fwhaccountkey = $fwhaccountkey;
        return $this;
    }

    public function getFwhaccountkey()
    {
        return $this->fwhaccountkey;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function setJvzoohash($jvzoohash)
    {
        $this->jvzoohash = $jvzoohash;
        return $this;
    }

    public function getJvzoohash()
    {
        return $this->jvzoohash;
    }

    public function setJvzoohashused($jvzoohashused)
    {
        $this->jvzoohashused = $jvzoohashused;
        return $this;
    }

    public function getJvzoohashused()
    {
        return $this->jvzoohashused;
    }

    public function setJvzooref($jvzooref)
    {
        $this->jvzooref = $jvzooref;
        return $this;
    }

    public function getJvzooref()
    {
        return $this->jvzooref;
    }

    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
        return $this;
    }

    public function getLastname()
    {
        return $this->lastname;
    }

    public function getFullName()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function setMailchimpMainUnsubscribed($mailchimpMainUnsubscribed)
    {
        $this->mailchimpMainUnsubscribed = $mailchimpMainUnsubscribed;
        return $this;
    }

    public function getMailchimpMainUnsubscribed()
    {
        return $this->mailchimpMainUnsubscribed;
    }

    public function setPackage($package)
    {
        $this->package = $package;
        return $this;
    }

    public function getPackage()
    {
        return $this->package;
    }

    public function setPaid($paid)
    {
        $this->paid = $paid;
        return $this;
    }

    public function getPaid()
    {
        return $this->paid;
    }

    public function setPassword($password)
    {
        $this->password = md5($password);
		$this->passwordplain = $password;
        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getUserHash()
    {
	    return hash_hmac(
		    'sha256',
		    $this->getEmail(),
		    'tJo9R-B1bzbxyeHYWvdsO9bYvjB5RNbxuqkxDz-6'
	    );
    }

    public function setPasswordplain($passwordplain)
    {
        $this->passwordplain = $passwordplain;
        return $this;
    }

    public function getPasswordplain()
    {
        return $this->passwordplain;
    }

    public function setPremium($premium)
    {
        $this->premium = $premium;
        return $this;
    }

    public function getPremium()
    {
        return $this->premium;
    }

    public function setPremiumaccounts($premiumaccounts)
    {
        $this->premiumaccounts = $premiumaccounts;
        return $this;
    }

    public function getPremiumaccounts()
    {
        return $this->premiumaccounts;
    }

    public function setProtrainingstart($protrainingstart)
    {
        $this->protrainingstart = $protrainingstart;
        return $this;
    }

    public function getProtrainingstart()
    {
        return $this->protrainingstart;
    }

    public function setReferralcode($referralcode)
    {
        $this->referralcode = $referralcode;
        return $this;
    }

    public function getReferralcode()
    {
        return $this->referralcode;
    }

    public function setRefunded($refunded)
    {
        $this->refunded = $refunded;
        return $this;
    }

    public function getRefunded()
    {
        return $this->refunded;
    }

    public function setRefundperiod($refundperiod)
    {
        $this->refundperiod = $refundperiod;
        return $this;
    }

    public function getRefundperiod()
    {
        return $this->refundperiod;
    }

    public function setSignuppage($signuppage)
    {
        $this->signuppage = $signuppage;
        return $this;
    }

    public function getSignuppage()
    {
        return $this->signuppage;
    }

    public function setWebsites($websites)
    {
        $this->websites = $websites;
        return $this;
    }

    public function getWebsites()
    {
        return $this->websites;
    }

    public function setWsohash($wsohash)
    {
        $this->wsohash = $wsohash;
        return $this;
    }

    public function getWsohash()
    {
        return $this->wsohash;
    }

    public function setWsohashused($wsohashused)
    {
        $this->wsohashused = $wsohashused;
        return $this;
    }

    public function getWsohashused()
    {
        return $this->wsohashused;
    }

    public function setPremiumaccountslog($premiumaccountslog)
    {
        $this->premiumaccountslog = $premiumaccountslog;
        return $this;
    }

    public function getPremiumaccountslog()
    {
        return $this->premiumaccountslog;
    }

    public function setPayments($payments)
    {
        $this->payments = $payments;
        return $this;
    }

    public function getPayments()
    {
        return $this->payments;
    }

    public function setTrackerclicks($trackerclicks)
    {
        $this->trackerclicks = $trackerclicks;
        return $this;
    }

    public function getTrackerclicks()
    {
        return $this->trackerclicks;
    }

    public function setAccountactions($accountactions)
    {
        $this->accountactions = $accountactions;
        return $this;
    }

    public function getAccountactions()
    {
        return $this->accountactions;
    }

    public function setAffiliatetracker($affiliatetracker)
    {
        $this->affiliatetracker = $affiliatetracker;
        return $this;
    }

    public function getAffiliatetracker()
    {
        # Account's tracker
        return $this->affiliatetracker;
    }

    public function setTracker($tracker)
    {
        # Tracker of the Affiliate for the creation of this account.
        $this->tracker = $tracker;
        return $this;
    }

    public function getTracker()
    {
        # Tracker of the Affiliate for the creation of this account.
        return $this->tracker;
    }

    public function getAffiliate()
    {
        if ( in_array('ROLE_AFFILIATE', $this->getRoles())) return true;

        return false;
    }

    public function setAccountaddresses($accountaddresses)
    {
        $this->accountaddresses = $accountaddresses;
        return $this;
    }

    public function getAccountaddresses()
    {
        return $this->accountaddresses;
    }

    public function setDeveloperaccounts($developeraccounts)
    {
        $this->developeraccounts = $developeraccounts;
        return $this;
    }

    public function getDeveloperaccounts()
    {
        return $this->developeraccounts;
    }

    public function setDeveloperaccountslog($developeraccountslog)
    {
        $this->developeraccountslog = $developeraccountslog;
        return $this;
    }

    public function getDeveloperaccountslog()
    {
        return $this->developeraccountslog;
    }

    public function setDeveloperaccountclients($developeraccountclients)
    {
        $this->developeraccountclients = $developeraccountclients;
        return $this;
    }

    public function getDeveloperaccountclients()
    {
        return $this->developeraccountclients;
    }

    public function setMaxWebsites($maxWebsites)
    {
        $this->maxWebsites = $maxWebsites;
        return $this;
    }

    public function getMaxWebsites()
    {
        return $this->maxWebsites;
    }

    /**
     * @param mixed $otoshown
     */
    public function setOtoshown($otoshown)
    {
        $this->otoshown = $otoshown;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOtoshown()
    {
        return $this->otoshown;
    }

	/**
	 * @return mixed
	 */
	public function getVersionBought()
	{
		return $this->versionbought;
	}

	/**
	 * @param mixed $versionbought
	 */
	public function setVersionBought($versionbought)
	{
		$this->versionbought = $versionbought;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPaypalhash()
	{
		return $this->paypalhash;
	}

	/**
	 * @param mixed $paypalhash
	 */
	public function setPaypalhash($paypalhash)
	{
		$this->paypalhash = $paypalhash;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPaypalhashused()
	{
		return $this->paypalhashused;
	}

	/**
	 * @param mixed $paypalhashused
	 */
	public function setPaypalhashused($paypalhashused)
	{
		$this->paypalhashused = $paypalhashused;
		return $this;
	}
	/**
	 * @return mixed
	 */
	public function getTermsAccepted()
	{
		return $this->termsaccepted;
	}

	/**
	 * @param mixed $termsaccepted
	 */
	public function setTermsAccepted($termsaccepted)
	{
		$this->termsaccepted = $termsaccepted;
		return $this;
	}

}
