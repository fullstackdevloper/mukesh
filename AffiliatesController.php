<?php

namespace App\Controller;

use App\Entity\Accountactions;
use App\Entity\Accounts;
use Doctrine\Common\Util\Debug;
use App\Entity\Trackers;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;use Symfony\Component\Form\Extension\Core\Type\TextType;

class AffiliatesController extends FreshAccountAreaController
{
    public function indexAction(Request $request)
    {
        $parameters = $this->getParameters();
		$intercomData = $this->intercomData($request);
		$parameters['data_intercom'] = true;
		$parameters['intercom'] = $intercomData;
        $account = $this->getUser();
        /** @var Accounts $account */

        $parameters['user'] = $account;
        $parameters['email'] = $account->getEmail();

        # If affiliate show info, if not show benefits and sign up option
        if ( $this->isAffiliate() )
            return $this->forward('App\Controller\AffiliatesController::DashboardAction', array('parameters'=>$parameters));

        return $this->forward('App\Controller\AffiliatesController::SignUpAction', array('parameters'=>$parameters));
    }


    public function DashboardAction($parameters)
    {
        $account = $this->getUser();
        /** @var Accounts $account */

        $em = $this->getDoctrine()->getManager();

        $tracker_click_repo = $em->getRepository(TrackerClicks::class);

        # Get commissions for User's tracker
        $commissions = $em->getRepository(Accounts::class)
            ->findCommissions($this->getUser()->getAffiliatetracker()->getTracker());

        $parameters['tracker'] = $account->getAffiliatetracker()->getTracker();
        $parameters['link_demo'] = $this->generateDemoPageLink();
        $parameters['link_home'] = $this->generateAffiliateLink();
        $parameters['link_order'] = $this->generateOrderPageLink();
        $parameters['title'] = 'Affiliate Dashboard';
        $parameters['subheading'] = 'Promote Fresh Store Builder and make extra profit';
        $parameters['meta_title']='Affiliate Dashboard';

        # Affiliate Stats
        $parameters['commissions'] = $commissions;
        $parameters['recent_clicks'] = $tracker_click_repo->findLastXClicksForAffiliate($account->getAffiliatetracker(), 10);
        $parameters['last_month'] = $tracker_click_repo->findTotalClicksLastMonth($this->getUser()->getAffiliatetracker());
        $parameters['this_month'] = $tracker_click_repo->findTotalClicksThisMonth($this->getUser()->getAffiliatetracker());
        $parameters['click_count'] = $tracker_click_repo->findLifetimeClicks($this->getUser()->getAffiliatetracker());


        return $this->render('affiliates/index.html.twig', $parameters);
    }



    public function signUpAction(Request $request)
    {
        if ($this->isAffiliate())
            return $this->redirect($this->generateUrl('fsb_accounts_affiliates'), 301);

        $parameters = $this->getParameters();
		$intercomData = $this->intercomData($request);
		$parameters['data_intercom'] = true;
		$parameters['intercom'] = $intercomData;
        $parameters['title'] = 'Become an Affiliate';
        $parameters['subheading'] = 'Promote Fresh Store Builder and make extra profit';
        $parameters['meta_title']='Affiliate';

        return $this->render('affiliates/sign-up.html.twig', $parameters);
    }







    public function setTrackerAction(Request $request)
    {
        if ($this->isAffiliate())
            return $this->redirect($this->generateUrl('fsb_accounts_affiliates'), 301);

        $parameters = $this->getParameters();

        $parameters['title']='Affilate :: Set Tracker';
        $parameters['subheading'] = 'Choose the unique tracking code to use in your links';
        $parameters['meta_title']='Affilate :: Set Tracker';
        $parameters['form_tracker'] = $this->getAddTrackerForm();

        return $this->render('affiliates/set-tracker.html.twig', $parameters);
    }




    public function processTrackerAction(Request $request)
    {
        # post only
        if ($request->getMethod() != 'POST')
            return $this->redirect($this->generateUrl('fsb_accounts_affiliates'), 301);

        # do something.. then go to affiliate dashboard or get started guide of somekind
        if ($this->isAffiliate())
            return $this->flashRedirect('fsb_accounts_affiliates', 'You are already an affiliate.');

        $desired_tracker = trim($request->request->get('tracker_requested'));

        # No empties
        if ($desired_tracker == '')
            return $this->flashRedirect('fsb_accounts_affiliatesettracker', 'Please enter a valid tracker code.','error');

        # No spaces
        if (strpos($desired_tracker, ' ') > 0)
            return $this->flashRedirect('fsb_accounts_affiliatesettracker', 'Please enter a valid tracker code.', 'error');

        $em = $this->getDoctrine()->getManager();

        # Check to see if this tracker is available
        $existing_tracker = $em->getRepository(Trackers::class)
            ->findByTracker($desired_tracker);

        if ($existing_tracker)
            return $this->flashRedirect('fsb_accounts_affiliatesettracker', 'Tracker already in use by another affiliate. Please select a different tracker.', 'error');

        $tracker = new Trackers();

        $tracker
            ->setAccount($this->getUser())
            ->setTracker($desired_tracker)
        ;

        $action = new Accountactions();

        $action
            ->setAccount($this->getUser())
            ->setType('Affiliate Tracker Added')
            ->setMessage('Registered an affiliate tracker ('.$tracker->getTracker().') via their account area.')
        ;

        $em->persist($tracker);
        $em->persist($action);
        $em->flush();

        return $this->flashRedirect('fsb_accounts_affiliates', 'You are now an affiliate!');
    }


    public function showDetailsAction(Request $request)
    {
        # Show form of user's affiliate details
        # currently just their paypal address

        # Get variables needed to load parameters
        $account = $this->getUser();

        # block route if not not affiliate - shouldn't happen if app is working
        if (!$account->getAffiliatetracker())
            return $this->redirect($this->generateUrl('fsb_accounts_affiliates'), 301);

        $crumb = array(
            'path'=>'fsb_accounts_affiliatedetails',
            'title'=>'Payment Details',
            'slug'=>null,
        );


        $parameters = $this->getParameters();
		$intercomData = $this->intercomData($request);
		$parameters['data_intercom'] = true;
		$parameters['intercom'] = $intercomData;
        $parameters['breadcrumbs'][]=$crumb;
        $parameters['paypal_mail']=$account->getAffiliatetracker()->getPaypalemail();
        $parameters['title']='Affiliate Payment Details';
        $parameters['subheading'] = 'Set your payment details and tell us how to pay you';
        $parameters['meta_title'] = 'Affiliate Payment Details';

        return $this->render('affiliates/details.html.twig', $parameters);
    }



    public function processDetailsAction(Request $request)
    {
        # post only
        if ($request->getMethod() != 'POST')
            return $this->redirect($this->generateUrl('fsb_accounts_account'), 301);

        $paypal = trim($request->request->get('paypal_email'));

        # check valid email address
        if (!filter_var($paypal, FILTER_VALIDATE_EMAIL))
            $this->flashRedirect('fsb_accounts_affiliatedetails', 'This is not a valid email address.', 'error');

        # check not same as previous
        if ($paypal == $this->getUser()->getAffiliatetracker()->getPaypalemail())
            $this->flashRedirect('fsb_accounts_affiliatedetails', 'Email address was not changed.', 'error');

        $tracker = $this->getUser()->getAffiliateTracker();
        /** @var Trackers $tracker */

        $tracker->setPaypalemail($paypal);

        $this->getDoctrine()->getManager()->flush();

        return $this->flashRedirect('fsb_accounts_affiliatedetails', 'Email address updated.');
    }







    public function statsAction(Request $request)
    {
        # If affiliate show stats, if not show benefits and sign up option
        if ( !$this->isAffiliate() )
            return $this->redirect($this->generateUrl('fsb_accounts_affiliates'), 301);

        $account = $this->getUser();
        /** @var Accounts $account */

        $em = $this->getDoctrine()->getManager();

        $tracker_click_repo = $em->getRepository(TrackerClicks::class);

        # Get commissions for User's tracker
        $commissions = $em->getRepository(Accounts::class)
            ->findCommissions($this->getUser()->getAffiliatetracker()->getTracker());

        $breadcrumb = array(
            'slug'=>null,
            'title'=>'Stats',
            'path'=>'fsb_accounts_affiliatestats'
        );

        $parameters = $this->getParameters();
		$intercomData = $this->intercomData($request);
		$parameters['data_intercom'] = true;
		$parameters['intercom'] = $intercomData;
        $parameters['title'] = 'Affiliate Statistics';
        $parameters['subheading'] = 'Your statistics and commissions made so far';
        $parameters['meta_title'] = 'Affiliate Statistics';
        $parameters['breadcrumbs'][]=$breadcrumb;

        # Affiliate Stats
        $parameters['commissions'] = $commissions;
        $parameters['recent_clicks'] = $tracker_click_repo->findLastXClicksForAffiliate($account->getAffiliatetracker(), 10);
        $parameters['last_month'] = $tracker_click_repo->findTotalClicksLastMonth($this->getUser()->getAffiliatetracker());
        $parameters['this_month'] = $tracker_click_repo->findTotalClicksThisMonth($this->getUser()->getAffiliatetracker());
        $parameters['click_count'] = $tracker_click_repo->findLifetimeClicks($this->getUser()->getAffiliatetracker());

        return $this->render('affiliates/stats.html.twig', $parameters);

        # gets total of payments linked to accounts linked to your tracker
        # select sum(amount) from payments where account_id in (SELECT id from accounts where tracker = (SELECT tracker FROM trackers WHERE account_id =2))

        # select paymenttype, sum(amount) from payments where account_id in (SELECT id from accounts where tracker = (SELECT tracker FROM trackers WHERE account_id =2)) GROUP BY paymenttype
        # select account_id, sum(amount) from payments where account_id in (SELECT id from accounts where tracker = (SELECT tracker FROM trackers WHERE account_id =2)) GROUP BY account_id
    }



    public function bannersAction(Request $request)
    {
        $parameters = $this->getParameters();
		$intercomData = $this->intercomData($request);
		$parameters['data_intercom'] = true;
		$parameters['intercom'] = $intercomData;
        $account = $this->getUser();
        /** @var Accounts $account */

        # If affiliate show banners, if not show benefits and sign up option
        if ( !$this->isAffiliate() )
            return $this->redirect($this->generateUrl('fsb_accounts_affiliates'), 301);

        $breadcrumb = array(
            'slug'=>null,
            'title'=>'Banners',
            'path'=>'fsb_accounts_affiliatedbanners'
        );

        # Retreive from API - best not to cache for now
        # See how it goes with symfony caching first
        $banners = $this->getAffiliateBanners();

        $parameters['breadcrumbs'][]=$breadcrumb;
        $parameters['tracker']=$this->getUser()->getAffiliatetracker()->getTracker();
        $parameters['title']='Get Affiliate Banners';
        $parameters['subheading'] = 'Use these banners for more clicks and better conversions';
        $parameters['meta_title']='Affiliate Banners';

        $parameters['banners'] = $banners;

        # Banners Logic
        return $this->render('affiliates/banners.html.twig', $parameters);
    }


    public function conditionsAction(Request $request)
    {
        # Accessible to all users
        $breadcrumb = array(
            'path'=>'fsb_accounts_affiliateconditions',
            'title'=>'T&Cs',
            'slug'=>null,
        );

        $parameters = $this->getParameters();
		$intercomData = $this->intercomData($request);
		$parameters['data_intercom'] = true;
		$parameters['intercom'] = $intercomData;
        $parameters['title'] = 'Affiliate Program Terms & Conditions';
        $parameters['subheading'] = 'The small print';
        $parameters['meta_title'] = 'Affiliate Terms';
        $parameters['breadcrumbs'][]=$breadcrumb;

        return $this->render('affiliates/conditions.html.twig', $parameters);
    }

    private function generateAffiliateLink()
    {
        return 'http://www.freshstorebuilder.com/r/' . $this->getUser()->getAffiliateTracker()->getTracker() . '/h';
    }

    private function generateOrderPageLink()
    {
        return 'http://www.freshstorebuilder.com/r/' . $this->getUser()->getAffiliateTracker()->getTracker() . '/o';
    }

    private function generateDemoPageLink()
    {
        return 'http://www.freshstorebuilder.com/r/' . $this->getUser()->getAffiliateTracker()->getTracker() . '/ll';
    }

    private function getAddTrackerForm(){

        $form = $this->createFormBuilder(new Trackers())
            ->setAction($this->generateUrl('fsb_accounts_becomeaffiliate'))
            ->setMethod('POST')						 ->add('tracker', TextType::class)            ->add('save', SubmitType::class)
            ->getForm();

        return $form->createView();
    }

    protected function getParameters()
    {
        $breadcrumb = array(
            'slug'=>null,
            'title'=>'Affiliates',
            'path'=>'fsb_accounts_affiliates'
        );

        $parameters = parent::getParameters();

        $parameters['breadcrumbs'][]=$breadcrumb;

        $parameters['menu']['affiliates'] = true;
        return $parameters;
    }
}