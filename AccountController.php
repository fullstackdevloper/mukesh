<?php

namespace App\Controller;

use App\Entity\Accountactions;
use App\DependencyInjection\MailingList;
use App\Entity\Accounts;
use Doctrine\Common\Util\Debug;
use App\Entity\Trackers;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;

class AccountController extends FreshAccountAreaController
{
    public function indexAction(Request $request)
    {
        $parameters = $this->getParameters();
		$intercomData = $this->intercomData($request);
		$parameters['data_intercom'] = true;
		$parameters['intercom'] = $intercomData;
        $parameters['title']='Contact Options';
        $parameters['subheading'] = 'Control the emails that we send to you';
        $parameters['meta_title']='Contact Options';
        return $this->render('account/main.html.twig', $parameters);
    }

    public function contactAction(Request $request)
    {
        # block if not customer
        if (!in_array('ROLE_CUSTOMER', $this->getUser()->getRoles()))
            return $this->redirect($this->generateUrl('fsb_accounts_dashboard'), 301);

        $parameters = $this->getParameters();
		$intercomData = $this->intercomData($request);
		$parameters['data_intercom'] = true;
		$parameters['intercom'] = $intercomData;
        $parameters['title']='Contact Options';
        $parameters['subheading'] = 'Control the emails that we send to you';
        $parameters['meta_title']='Contact Options';
        return $this->render('account/contact-options.html.twig', $parameters);
    }

    public function updateMyDetailsAction(Request $request)
    {
        $breadcrumb = array(
            'path'=>'fsb_accounts_updatemydetails',
            'title'=>'Update My Details',
            'slug'=>null,
        );
		$intercomData = $this->intercomData($request);
        $parameters = $this->getParameters();
		$parameters['data_intercom'] = true;
		$parameters['intercom'] = $intercomData;
		$parameters['fsi_id'] = !empty($intercomData) && array_key_exists('id', $intercomData) ? $intercomData->id : '' ;
		$parameters['fsi_upgrade'] = $this->fsi_upgrade_check($request);
        $parameters['breadcrumbs'][] = $breadcrumb;
        $parameters['meta_title'] = 'My Details';
        $parameters['title'] = 'My Details';
        $parameters['subheading'] = 'Change your details here';
        $parameters['current_mail'] = $this->getUser()->getEmail();
        return $this->render('account/update-my-details.html.twig', $parameters);
    }	

    public function processUpdateMyDetailsAction(Request $request)
    {
        # If arriving at page without POST data
        if ($request->getMethod() != 'POST')
            return $this->redirect($this->generateUrl('fsb_accounts_updatemydetails'), 301);
 
		# Get New Details
        $firstname = $request->request->get('firstname');
        $lastname = $request->request->get('lastname');

        # Various determine as much as possible if is a firstname and lastname
        if (trim($firstname)=='' or $firstname == '' or $firstname == null or $firstname == false )
        {
            $msg = '<h3>Fistname not valid.</h3>
            <p>"'.$firstname.'"</p>
            <p>If you think this is a mistake please contact our Helpdesk.</p>';
            return $this->flashRedirect('fsb_accounts_updatemydetails', $msg, 'danger');
        }

        if (trim($lastname)=='' or $lastname == '' or $lastname == null or $lastname == false )
        {
            $msg = '<h3>Lastname not valid.</h3>
            <p>"'.$lastname.'"</p>
            <p>If you think this is a mistake please contact our Helpdesk.</p>';
            return $this->flashRedirect('fsb_accounts_updatemydetails', $msg, 'danger');
        }		

		# Update on API's 
		$request->attributes->set('endpoint', 'update-name');
		$request->attributes->set('_route_params', array('endpoint' => 'update-name'));
		$path = $request->attributes->all();
    	$response = $this->forward('App\Controller\InstantStoreAPIController::indexAction', $path, array('firstname' => trim($firstname), 'lastname' => trim($lastname)));
		$result = json_decode($response->getContent());

 		if($result->status == "error"){
			$msg = "<p>".$result->data."</p>"; 
			return $this->flashRedirect('fsb_accounts_updatemydetails', $msg, 'danger');
		}else if($result->status == "success"){
			# Update Users Account
			$account = $this->getUser();
			$account->setFirstname(trim($firstname));
			$account->setLastname(trim($lastname));

			# Commit to Database
			$em = $this->getDoctrine()->getManager();
			$em->persist($account);
			$em->flush();

			return $this->flashRedirect('fsb_accounts_updatemydetails', 'Name updated successfully.', 'success');
		}else{
			return $this->flashRedirect('fsb_accounts_updatemydetails', 'Something went wrong. Please try again later.', 'danger');
		}		
    }
	
    public function updateEmailAction(Request $request)
    {
        $breadcrumb = array(
            'path'=>'fsb_accounts_updateemail',
            'title'=>'Update Details',
            'slug'=>null,
        );
		
		$intercomData = $this->intercomData($request);
        $parameters = $this->getParameters();
		$parameters['data_intercom'] = true;
		$parameters['intercom'] = $intercomData;
		$parameters['fsi_id'] = !empty($intercomData) && array_key_exists('id', $intercomData) ? $intercomData->id : '' ;
		$parameters['fsi_upgrade'] = $this->fsi_upgrade_check($request);
        $parameters['breadcrumbs'][] = $breadcrumb;
        $parameters['meta_title'] = 'My Email Address';
        $parameters['title'] = 'My Email Address';
        $parameters['subheading'] = 'Change your email here';
        $parameters['current_mail'] = $this->getUser()->getEmail();
        return $this->render('account/update-email.html.twig', $parameters);
    }

    public function processUpdateEmailAction(Request $request)
    {
        # If arriving at page without POST data
        if ($request->getMethod() != 'POST')
            return $this->redirect($this->generateUrl('fsb_accounts_updateemail'), 301);

        # Get New Email Address
        $email = $request->request->get('email');

        # Various determine as much as possible if is an email address
        if (trim($email)=='' or $email == '' or $email ==null or $email == false or !strstr($email, '@') or !filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            $msg = '<h3>Email Address not valid.</h3>
            <p>"'.$email.'"</p>
            <p>If you think this is a mistake please contact our Helpdesk.</p>';
            return $this->flashRedirect('fsb_accounts_updateemail', $msg, 'danger');
        }

		# Update on API's 
		$request->attributes->set('endpoint', 'update-email');
		$request->attributes->set('_route_params', array('endpoint' => 'update-email'));		$path = $request->attributes->all();
    	$response = $this->forward('App\Controller\InstantStoreAPIController::indexAction', $path, array('email' => trim($email)));
		$result = json_decode($response->getContent());
 		if($result->status == "error"){
			$msg = "<p>".$result->data."</p>"; 
			return $this->flashRedirect('fsb_accounts_updateemail', $msg, 'danger');
		}else if($result->status == "success"){
			# Update Users Account
			$account = $this->getUser();
			$old_email = $account->getEmail();
			$account->setEmail(trim($email));

			# Update Email on Mailchimp
			$list = new MailingList();
			$res = $list->emailupdate( $old_email, $email );
			
			# Commit to Database
			$em = $this->getDoctrine()->getManager();
			$em->persist($account);
			$em->flush();

			return $this->flashRedirect('fsb_accounts_updateemail', 'Email updated successfully.', 'success');
		}else{
			return $this->flashRedirect('fsb_accounts_updateemail', 'Something went wrong. Please try again later.', 'danger');
		}
    }


    public function changePasswordAction(Request $request)
    {
        $breadcrumb = array(
            'path'=>'fsb_accounts_changepassword',
            'title'=>'Change Password',
            'slug'=>null,
        );
		
		$intercomData = $this->intercomData($request);
        $parameters = $this->getParameters();
		$parameters['data_intercom'] = true;
		$parameters['intercom'] = $intercomData;
		$parameters['fsi_id'] = !empty($intercomData) && array_key_exists('id', $intercomData) ? $intercomData->id : '' ;
		$parameters['fsi_upgrade'] = $this->fsi_upgrade_check($request);
        $parameters['breadcrumbs'][] = $breadcrumb;
        $parameters['meta_title'] = 'Change Password';
        $parameters['title'] = 'Change Password';
        $parameters['subheading'] = 'Use the form below to change your password';
        return $this->render('account/change-password.html.twig', $parameters);
    }

    public function processPasswordChangeAction(Request $request)
    {
		
        # If arriving at page without POST data
        if ($request->getMethod() != 'POST')
            return $this->redirect($this->generateUrl('fsb_accounts_changepassword'), 301);

        $wrongpass = 'Old password was not correct. Password was not changed. Please try again.';
        $mismatch = 'Passwords do not match. Password was not changed. Please try again.';

        $old = $request->request->get('password_old');
        $new = $request->request->get('password');
        $rpt = $request->request->get('password_repeat');

        $cur = $this->getUser()->getPassword();

        # if old password is incorrect
        if ($cur != md5($old))
            return $this->flashRedirect('fsb_accounts_changepassword', $wrongpass, 'danger');

        # If passwords do not match
        if ($new != $rpt)
            return $this->flashRedirect('fsb_accounts_changepassword', $mismatch, 'danger');

        # Got here? You can reset you Password then!

        $em = $this->getDoctrine()->getManager();

        $account = $this->getUser();
        /** @var $account Accounts */

        $account->setPassword($new);

        $em->persist($account);
        $em->flush();

        return $this->flashRedirect('fsb_accounts_changepassword', 'Password changed successfully.', 'success');
    }

    public function upgradeAction(Request $request)
    {
        $parameters = $this->getParameters();

        # Hero means someone who's bought everything!
        # if they are, they can't see the upgrades page
        # so obvious reason (unless we ask for donation?)
        if ($this->array_lookup($parameters, 'hero'))
            return $this->redirect($this->generateUrl('fsb_accounts_dashboard'), 301);

        # Add Account Action
        $action = new Accountactions();

        $action
            ->setAccount($this->getUser())
            ->setType('Account Upgrade Page')
            ->setMessage('Viewing the account upgrade page')
        ;
        $em = $this->getDoctrine()->getManager();
        $em->persist($action);
        $em->flush();


        # Load Page Variables
        $max = $this->getUser()->getMaxWebsites();

        if ($max==0){
            $max = 'no websites';
        }elseif($max==1){
            $max = '1 website';
        }elseif($max>=100){
            $max = 'Unlimited websites';
        }else{
            $max = $max.' websites';
        }


        $breadcrumb = array(
            'path'=>'fsb_accounts_upgrade',
            'title'=>'Upgrades',
            'slug'=>null,
        );

        # Vars for general page stuff
        $parameters['breadcrumbs'][] = $breadcrumb;
        $parameters['max_websites'] = $max;
        $parameters['title'] = 'Account Upgrades';
        $parameters['subheading'] = 'See your available account upgrades below';
        $parameters['meta_title'] = 'Upgrade';
		$intercomData = $this->intercomData($request);
		$parameters['data_intercom'] = true;
		$parameters['intercom'] = $intercomData;
		$parameters['fsi_id'] = !empty($intercomData) && array_key_exists('id', $intercomData) ? $intercomData->id : '' ;
		$parameters['fsi_upgrade'] = $this->fsi_upgrade_check($request);

        # Load Form Variables
        $parameters['business'] = $this->getPaypalAddress();
        $parameters['price'] = $this->getPrice('unlimited');
        $parameters['account_id'] = $this->getUser()->getId();

        return $this->render('account/upgrade.html.twig', $parameters);
    }

    public function paymentSuccessAction(Request $request)
    {
        $parameters = $this->getParameters();
		$intercomData = $this->intercomData($request);
		$parameters['data_intercom'] = true;
		$parameters['intercom'] = $intercomData;
        $parameters['title'] = 'Upgrade Successful!';
        $parameters['meta_title'] = "Upgrade Successful";
        return $this->render('payments/upgrade-unlimited-success.html.twig', $parameters);
    }

    public function proTrainingAction(Request $request)
    {
        # Block if not customer
        if (!in_array('ROLE_CUSTOMER', $this->getUser()->getRoles()))
            return $this->redirect($this->generateUrl('fsb_accounts_dashboard'), 301);

        $breadcrumb = array(
            'path'=>'fsb_accounts_protraining',
            'title'=>'Pro Training',
            'slug'=>null,
        );

        $parameters = $this->getParameters();
		$intercomData = $this->intercomData($request);
		$parameters['data_intercom'] = true;
		$parameters['intercom'] = $intercomData;
        $parameters['breadcrumbs'][] = $breadcrumb;
        $parameters['title'] = 'FSB Pro Training';
        $parameters['subheading'] = 'Reactivate your Pro Training email course';
        $parameters['meta_title'] = 'Pro Training';
        return $this->render('account/pro-training.html.twig', $parameters);
    }

    public function restartProTrainingAction()
    {
        $em = $this->getDoctrine()->getManager();

        # Set start today to today!
        $account = $this->getUser();
        $account->setProtrainingstart(new \DateTime('now'));

        $em->flush();


        # Add Account Action
        $action = new Accountactions();
        $action
            ->setAccount($account)
            ->setType('Pro Training Restarted')
            ->setMessage('The user restarted the Pro Training email course.')
        ;
        $em->persist($action);
        $em->flush();

        return $this->flashRedirect('fsb_accounts_protraining', 'Your Pro Training has been restarted.');
    }

    public function paymentRequiredAction()
    {
        # Doesn't pull parent array as likely not logged in
        $parameters = array();
        $parameters['title'] = 'Payment Required';
        $parameters['meta_title'] = 'Payment Required';
        $parameters['upgrade'] = 'none';
        return $this->render('payments/payment-required.html.twig', $parameters);
    }

    public function initialPaymentSuccessAction()
    {
        # Log them in from Cookie TODO

        exit;

        $parameters = $this->getParameters();
        $parameters['title'] = 'Order Successful!';
        $parameters['meta_title'] = 'Order Success';
        $parameters['guide_cats'] = $categories;
        $parameters['upgrade'] = 'none';

        return $this->render('payments/initial-order-success.html.twig', $parameters);
    }

    protected function getParameters()
    {
        $parameters = parent::getParameters();
        $parameters['menu']['account'] = true;
        return $parameters;
    }

    public function warriorSuccessAction(Request $request)
    {
        $parameters = $this->getParameters();
		$intercomData = $this->intercomData($request);
		$parameters['data_intercom'] = true;
		$parameters['intercom'] = $intercomData;
        $parameters['title'] = 'Order Successful - Welcome Warrior!';
        $parameters['meta_title'] = "Order Successful - Welcome to your Hosting Account!";
        return $this->render('payments/wso-success.html.twig', $parameters);
    }

    public function fwhotoSuccessAction()
    {
        $parameters = $this->getParameters();
        $parameters['title'] = 'Order Successful - Congratulations on Ordering Super Fast Hosting!';
        $parameters['meta_title'] = "Order Successful - Congratulations on Ordering Super Fast Hosting!";
        return $this->render('payments/fwhoto-success.html.twig', $parameters);
    }

    public function setfwhaccountkeyAction($key,Request $request)
    {
        $account = $this->getUser();
//        $account = new Accounts();
        $account->setFwhaccountkey($key);

        $action = new Accountactions();
        $action
            ->setAccount($account)
            ->setType('FWH Account Key Updated')
            ->setMessage('Account key updated to '.$key)
        ;


        $em = $this->getDoctrine()->getManager();

        $em->persist($account);
        $em->persist($action);
        $em->flush();
        $this->fwhotoSuccessAction();

        $data = $this->getParameters();
		$intercomData = $this->intercomData($request);
		$data['data_intercom'] = true;
		$data['intercom'] = $intercomData;
        $data['meta_title']='FWH Account Key Success';
        $data['title']='FWH Account Key Success';

        return $this->render('account/setfwhaccountkey.html.twig', $data);
    }	
}