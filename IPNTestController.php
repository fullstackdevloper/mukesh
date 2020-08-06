<?php

namespace App\Controller;

use App\Entity\Accountactions;
use App\Entity\Accounts;
use App\Entity\Ipnlog;
use App\Entity\Payments;
use App\Entity\PaypalLibrary;
use App\Entity\Premiumaccounts;
use App\Entity\Premiumaccountslog;
use Symfony\Component\HttpFoundation\Request;


class IPNTestController extends FreshAccountAreaController
{
    private $account;
    private $payment;
    private $em;
    private $pp;
    private $admin;

    

    public function handleAction(Request $request)
    {
	  exit;
		$data = 'txn_type=subscr_modify&subscr_id=I-LDEPM2DX1XL7&last_name=lee&residence_country=US&mc_currency=USD&item_name=Premium Account Upgrade&business=paypal@freshdevelopment.co.uk&amount3=47.00&subscr_effective=&recurring=1&verify_sign=ADhxPC5AvCwHO8mQnFabiAUK8GTGAgg7MI.ZRlJEvVuu2KMrV-4-5JGX&payer_status=verified&payer_email=franklin.lee@fluor.com&first_name=franklin&receiver_email=paypal@freshdevelopment.co.uk&payer_id=DFN2FLEXRQZVN&reattempt=1&item_number=premium-account&subscr_date=01:14:54 Jan 19, 2015 PST&custom=252753&charset=UTF-8&notify_version=3.9&period3=1 Y&mc_amount3=47.00&ipn_track_id=70b82ea21e1a4';
		$split = explode('&', $data);
		$get_array;
		foreach ($split as $key => $value)
		{
		  $values = explode('=', $value);
		  $get_array[$values[0]] = $values[1];
		}
		
		$ch = curl_init();
		$curlConfig = array(
			CURLOPT_URL            => "https://myaccount.freshstorebuilder.com/ipn/paypal/premium/",
			CURLOPT_POST           => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTREDIR => CURL_REDIR_POST_ALL,
			CURLOPT_POSTFIELDS     => $get_array,
			CURLOPT_CONNECTTIMEOUT => 600,
			CURLOPT_TIMEOUT => 400
		);
		
		curl_setopt_array($ch, $curlConfig);
		$result = curl_exec($ch);
		//print_r($result);
		curl_close($ch);

        exit;
//        if ($request->getMethod() != 'POST') exit('Access to this script is not allowed.');

        $this->em = $this->getDoctrine()->getManager();

        $this->pp = new PaypalLibrary();

//        if (!$this->pp->validate_ipn()) exit;     # TODO RESTORE THIS CHECK WHEN NOT TESTING

//        $this->dump($_GET);

        $_POST = $_GET;

//        $this->dump($_POST);

//        $_POST = unserialize('txn_type=subscr_cancel&subscr_id=I-K8DY7MUUK6NL&last_name=Robinson&residence_country=GB&mc_currency=USD&item_name=Premium Account Upgrade&business=paypal@freshdevelopment.co.uk&amount3=47.00&recurring=1&verify_sign=ADlji3DNx-gTS2nosKPZ4JrmxeJfAs5w96bWnf75Hb64MNMyjlvpa19Q&payer_status=verified&payer_email=graham@grahamrobinsonsoftware.com&first_name=John&receiver_email=paypal@freshdevelopment.co.uk&payer_id=WYAMGWRU5XZ6W&reattempt=1&item_number=premium-account&payer_business_name=Graham Robinson Software&subscr_date=09:25:49 Sep 12, 2014 PDT&custom=247316&charset=windows-1252&notify_version=3.8&period3=1 Y&mc_amount3=47.00&ipn_track_id=d7d260669f3');

        $this->pp->validate_ipn();

        $log = new Ipnlog();

        $log
            ->setType('premium-ipn-acc-v2')
            ->setEmail(isset($this->pp->ipn_data['payer_email'])?$this->pp->ipn_data['payer_email']:'email')
            ->setTransactionid(isset($this->pp->ipn_data['txn_id']) ? $this->pp->ipn_data['txn_id'] : 'unknown')
            ->setIpn(serialize($this->pp->ipn_data))
            ->setReimport(false)
        ;
        $this->em->persist($log);
        $this->em->flush();

        if (isset($this->pp->ipn_data['payer_email']) AND isset($this->pp->ipn_data['txn_id'])){
            # Create IPN Log Entry

            $log = new Ipnlog();

            $log
                ->setType('premium-ipn-acc-v2')
                ->setEmail($this->pp->ipn_data['payer_email'])
                ->setTransactionid($this->pp->ipn_data['txn_id'])
                ->setIpn(serialize($this->pp->ipn_data))
                ->setReimport(false)
            ;
            $this->em->persist($log);
            $this->em->flush();
        }


        # Hack Attempt - unsure how value is generated.
        if (isset($this->pp->ipn_data['mc_gross']) AND ($this->pp->ipn_data['mc_gross'] >= 0 && $this->pp->ipn_data['mc_gross'] < 0.5)) exit();


        # Get the account ID
        $account_id = $this->pp->ipn_data['custom'];

        $this->account = $this->getDoctrine()->getRepository(Accounts::class)->find($account_id);

        # email Carey if account not found
        if (empty($this->account))
        {
            $this->emailData(
                $this->getAdminEmail(),
                'FSB Premium Account ACCOUNT NOT FOUND',
                print_r($this->pp->ipn_data, true)
            );
        }


        # Determine if Refund
        if (
            isset($this->pp->ipn_data['payment_status'])
            AND
            (
                ($this->pp->ipn_data['payment_status']=='Reversed')
                OR
                ($this->pp->ipn_data['payment_status']=='Refunded')
            )
        )
        {
            $this->subscriptionPaymentRefunded();       ### Refunded or Reversed
        }
        elseif(isset($this->pp->ipn_data['payment_status'])
            AND $this->pp->ipn_data['payment_status']=='Pending')
        {
            $params = array('account'=>$this->account, 'payment'=>$this->payment);
            return $this->forward('App\Controller\PaymentsAllController::paymentPendingAction', $params);
        }
        elseif ($this->pp->ipn_data['txn_type'] == 'subscr_payment')
        {
            $this->subscribeToPremium();
        }
        elseif ($this->pp->ipn_data['txn_type'] == 'subscr_cancel')
        {
            $this->subscriptionCancelled();
        }
        else if ($this->pp->ipn_data['txn_type'] == 'subscr_failed')
        {
            $this->subscriptionFailed();
        }
        else if ($this->pp->ipn_data['txn_type'] == 'subscr_eot')
        {
            # Handled Universally for all Types of Payments
            $params = array('ipn_data'  => $this->pp->ipn_data,'script_name'=>'Premium Account');
            return $this->forward('App\Controller\PaymentsAllController::subscriptionEOTAction',$params);
        }
        else if ($this->pp->ipn_data['txn_type'] == 'subscr_modify')
        {
            # Handled Universally for all Types of Payments
            $params = array('ipn_data'  => $this->pp->ipn_data, 'script_name'=>'Premium Account');
            return $this->forward('App\Controller\PaymentsAllController::subscriptionModifyAction',$params);
        }
        else if ($this->pp->ipn_data['txn_type'] == 'recurring_payment_suspended_due_to_max_failed_payment')
        {
            $this->subscriptionSuspended();
        }
        else if ($this->pp->ipn_data['txn_type'] == 'recurring_payment_outstanding_payment')
        {
            $this->subscriptionPaymentOutstanding();
        }
        else
        {
            # Should not happen...
            # subscr_signup may enact this
            if ($_POST) $this->emailData($this->getAdminEmail(), 'FSB Premium Account UNKNOWN TXN_TYPE', $this->pp->ipn_data);
        }

        return $this->render('ipn/index.html.twig');
    }


    private function subscribeToPremium()
    {
        # Add Payment Record
        $this->payment = new Payments();

        $this->payment
            ->setAccount($this->account)
            ->setAmount($this->pp->ipn_data['mc_gross'])
            ->setPayerid($this->pp->ipn_data['payer_id'])
            ->setPayeremail($this->pp->ipn_data['payer_email'])
            ->setTransactionid($this->pp->ipn_data['txn_id'])
            ->setPaymentstatus($this->pp->ipn_data['payment_status'])
            ->setDebug(print_r($this->pp->ipn_data, true))
            ->setPaymenttype('premium')
            ->setPaypalitemname(isset($this->pp->ipn_data['item_name']) ? $this->pp->ipn_data['item_name'] : '')
            ->setPaypalitemnumber(isset($this->pp->ipn_data['item_number']) ? $this->pp->ipn_data['item_number'] : '')
            ->setPaypaltransactionsubject(isset($this->pp->ipn_data['transaction_subject']) ? $this->pp->ipn_data['transaction_subject'] : '' )
            ->setPaypalsubscriptionid( isset($this->pp->ipn_data['transaction_subject']) ? $this->pp->ipn_data['transaction_subject'] : '' )
            ->setPaymentmethod('paypal')
            ->setItemname(isset($this->pp->ipn_data['item_number']) ? $this->pp->ipn_data['item_number'] : '')
            ->setCreated(new \DateTime($this->pp->ipn_data['payment_date']))
            ->setPaidtofsb(1)
        ;

        $this->em->persist($this->payment);
        $this->em->flush();


        # Add Account Action
        $action = new Accountactions();

        $action
            ->setAccount($this->account)
            ->setPaymentId($this->payment->getId())
            ->setType('Premium Account Payment')
            ->setMessage('Payment recieved for premium account.')
        ;

        $this->em->persist($action);
        $this->em->flush();

        # Check for 'core' Premium Account Record

        $premium_account = $this->getDoctrine()
            ->getRepository(Premiumaccounts::class)
            ->findOneByPaypalsubscriptionid($this->pp->ipn_data['subscr_id']);

        $payment_date = new \DateTime($this->pp->ipn_data['payment_date']);
        $pd = new \DateTime($this->pp->ipn_data['payment_date']);
        $next_payment = $pd->modify('+1 year');

        if ($premium_account)
        {
            # If Premium Account is already there
            $premium_account->setNextpaymentdate($next_payment);
            $this->account->setPremium(1);

            $this->em->flush();
        }
        else
        {
            # Create New Premium Account
            $premium_account = new Premiumaccounts();

            $premium_account
                ->setPaypalsubscriptionid($this->pp->ipn_data['subscr_id'])
                ->setAccount($this->account)
                ->setOffername($this->pp->ipn_data['item_number'])
                ->setCreated($payment_date)
                ->setNextpaymentdate($next_payment)
                ->setStatus('active')
                ->setDebug(serialize($this->pp->ipn_data))
            ;

            $this->em->persist($premium_account);
            $this->em->flush();


            # Add to Premium Accounts Log
            $log = new Premiumaccountslog();

            $log
                ->setPaymentId($this->payment->getId())
                ->setPremiumaccountId($premium_account->getId())
                ->setAccount($this->account)
                ->setLogtype('Subscription Started')
                ->setLogdescription('The subscription payment started on this date.')
                ->setDebug(serialize($this->pp->ipn_data))
            ;

            $this->em->persist($log);
            $this->em->flush();

            # Set User as Premium
            $this->account->setPremium(1);

            ## Send an email to welcome and thank them.. and let them know what they can do
            $this->emailTemplate($this->account->getEmail(), 'Welcome to your Premium Account', 'premium-welcome');


            # Register Account Action
            $action = new Accountactions();

            $action
                ->setAccount($this->account)
                ->setType('Premium Account Signup')
                ->setMessage('Initial signup for a premium account.')
            ;

            $this->em->persist($action);
            $this->em->flush(); # Flushes account update as well
        }

        # Insert Log Record
        $log = new Premiumaccountslog();

        $log
            ->setPremiumaccountId($premium_account->getId())
            ->setAccount($this->account)
            ->setPaymentId($this->payment->getId())
            ->setCreated($payment_date)
            ->setLogtype('Subscription Payment Received')
            ->setLogdescription('A subscription payment was received on this date.')
            ->setDebug(serialize($this->pp->ipn_data))
        ;

        $this->em->persist($log);
    }

    private function subscriptionCancelled()
    {
        # Get related Premium Account
        $premium_account = $this->getDoctrine()
            ->getRepository(Premiumaccounts::class)
            ->findOneByPaypalsubscriptionid($this->pp->ipn_data['subscr_id']);


        # Update Premium Account with Date of Cancellation
        $premium_account
            ->setStatus('cancelled')
            ->setCancelled($this->getNow())
        ;

        $this->em->flush();


        # Insert Log Record of Subscription Cancellation
        $log = new Premiumaccountslog();

        $log
            ->setPremiumaccountId($premium_account->getId())
            ->setAccount($this->account)
            ->setLogtype('Subscription Cancelled')
            ->setLogdescription('The subscription was cancelled on this date.')
            ->setDebug(serialize($this->pp->ipn_data))
            ->setPaymentId(0)
        ;
        $this->em->persist($log);
        $this->em->flush();


        # Insert Account Action
        $action = new Accountactions();

        $action
            ->setAccount($this->account)
            ->setType('Premium Account Cancelled')
            ->setMessage('Cancelled a premium account subscription.')
        ;
        $this->em->persist($action);
        $this->em->flush();


        # Notify User via Email they will no longer be a Premium Member (after XX:XX:XX)
        $mail_params = array('next_payment_date' => $premium_account->getNextpaymentdate());
//        $this->emailTemplate('stephen.algeo@gmail.com', 'Premium Account Cancelled', 'premium-cancelled', $mail_params);

        # Email and notify Carey of the cancellation
//        $this->emailData('stephen.algeo@gmail.com', 'FSB Premium Account CANCELLED', 'some info');
//        $this->emailData($this->getAdminEmail(), 'FSB Premium Account CANCELLED', $this->pp->ipn_data);
        $data = $this->pp->ipn_data;
        $this->emailData('stephen.algeo@gmail.com', 'FSB Premium Account CANCELLED', print_r($data, true));
    }

    private function subscriptionFailed()
    {
        # What actually happens? Trying again? If it trys again do we get a cancel message once it has failed?
        $this->emailData($this->getAdminEmail(), 'FSB Premium Account FAILED', $this->pp->ipndata);

        # Get related Premium Account
        $premium_account = $this->getDoctrine()
            ->getRepository(Premiumaccounts::class)
            ->findOneByPaypalsubscriptionid($this->pp->ipn_data['subscr_id']);

        # Insert Premium Account Log
        $log = new Premiumaccountslog();

        $log
            ->setPremiumaccountId($premium_account->getId())
            ->setAccount($this->account)
            ->setLogtype('Subscription Payment Failed')
            ->setLogdescription('The subscription payment failed on this date.')
            ->setDebug(serialize($this->pp->ipn_data))
        ;

        $this->em->persist($log);
        $this->em->flush();

        # Set User's Account not not-premium
        $this->account->setPremium(0);
        $this->em->flush();


        # Notify the user that they have be removed from premium
        $this->emailTemplate($this->account->getEmail(), 'Premium Account Payment Failed', 'premium-failed');


        # Email and notify Carey of the cancellation
        $this->emailData($this->getAdminEmail(), 'FSB Premium Account FAILED', $this->pp->ipn_data);
    }


    private function subscriptionSuspended()
    {
        # Get related Premium Account
        $premium_account = $this->getDoctrine()
            ->getRepository(Premiumaccounts::class)
            ->findOneByPaypalsubscriptionid($this->pp->ipn_data['subscr_id']);

        $this->account->setPremium(0);
        $this->em->flush();


        # Notify User
        $this->emailTemplate($this->account->getEmail(), 'Premium Account Suspended', 'premium-suspended');


        # Create Premium Account Log
        $log = new Premiumaccountslog();

        $log
            ->setPremiumaccountId($premium_account->getId())
            ->setAccount($this->account)
            ->setLogtype('Subscription Max Failed Payments')
            ->setLogdescription('The subscription has been suspended due to maximum failed payments.')
            ->setDebug(serialize($this->pp->ipn_data))
        ;
        $this->em->persist($log);
        $this->em->flush();


        # Email Carey
        $this->emailData($this->getAdminEmail(), 'FSB Premium Account SUSPENDED', $this->pp->ipn_data);
    }

    private function subscriptionPaymentOutstanding()
    {
        # Get related Premium Account
        $premium_account = $this->getDoctrine()
            ->getRepository(Premiumaccounts::class)
            ->findOneByPaypalsubscriptionid($this->pp->ipn_data['subscr_id']);

        $this->account->setPremium(0);
        $this->em->flush();

        # Create Premium Account Log
        $log = new Premiumaccountslog();

        $log
            ->setPremiumaccountId($premium_account->getId())
            ->setAccount($this->account)
            ->setLogtype('Subscription Outstanding Payment')
            ->setLogdescription('The subscription has an outstanding payment and has been suspended.')
            ->setDebug(serialize($this->pp->ipn_data))
        ;
        $this->em->persist($log);
        $this->em->flush();


        # Email Carey
        $this->emailData($this->getAdminEmail(), 'FSB Premium Account OUTSTANDING PAYMENT', $this->pp->ipn_data);
    }
    private function subscriptionPaymentRefunded()
    {
        ### Refunded or Reversed

        # Email Carey
        $data = print_r($_POST, true);

        $this->emailData($this->getAdminEmail(), 'FSB Premium Account Refund', $data);


        # Record the transaction and save transaction_id in Payments Table
        $payment = new Payments();

        $payment
            ->setAccount($this->account)
            ->setAmount($this->pp->ipn_data['mc_gross'])
            ->setPayerid($this->pp->ipn_data['payer_id'])
            ->setPayeremail($this->pp->ipn_data['payer_email'])
            ->setTransactionid($this->pp->ipn_data['txn_id'])
            ->setPaymentstatus($this->pp->ipn_data['payment_status'])
            ->setDebug(print_r($this->pp->ipn_data, true))
            ->setCreated(new \DateTime($this->pp->ipn_data['payment_date']))
            ->setPaymenttype('refund')
            ->setWsoref('')
            ->setPaymentmethod('paypal')
            ->setPaidtofsb(1)
            ->setPaypalitemname($this->pp->ipn_data['item_name'])
            ->setPaypalitemnumber($this->pp->ipn_data['item_number'])
            ->setPaypaltransactionsubject( isset($this->pp->ipn_data['transaction_subject']) ? $this->pp->ipn_data['transaction_subject'] : '' )
            ->setItemname($this->pp->ipn_data['item_number'])
        ;
        $this->em->persist($payment);
        $this->em->flush();


        # Record an Account Action
        $action = new Accountactions();

        $action
            ->setAccount($this->account)
            ->setPaymentId($payment->getId())
            ->setType('Account Payment Refunded')
            ->setMessage('Payment refunded for the Premium Account')
        ;
        $this->em->persist($action);
        $this->em->flush();


        # Revoke Premium Status
        $this->account->setPremium(0);
        $this->em->flush();
    }
}