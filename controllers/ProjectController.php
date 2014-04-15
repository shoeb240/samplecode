<?php
/**
 * Project management actions
 * 
 * @category   Application
 * @package    Application_Controller
 * @author     Shoeb Abdullah <shoeb240@gmail.com>
 * @copyright  Copyright (c) 2013, Shoeb Abdullah
 * @uses       Zend_Controller_Action
 * @version    1.0
 */
class ProjectController extends Zend_Controller_Action
{
    /**
     * @var Zend_Controller_Action_Helper_Redirector Action helper Redirector
     */
    protected $_redirector = null;
    
    /**
     * @var Zend_Session_Namespace Session namespace 'login'
     */
    private $_loginNamespace;
    
    /**
     * @var Zend_Session_Namespace Session namespace 'message'
     */
    private $_messageNamespace;
    
    /**
     * Initialize object
     *
     * Called from {@link __construct()} as final step of object instantiation.
     *
     * @return void
     */    
    public function init()
    {
        $this->_redirector = $this->_helper->getHelper('Redirector');
        
        // Initializing session namespaces
        $this->_loginNamespace = new Zend_Session_Namespace('login');
        $this->_messageNamespace = new Zend_Session_Namespace('message');
    }

    /**
     * Account default page action
     *
     * @return void
     */    
    public function indexAction()
    {
        // Get logged in userId
        $sessionUserId = $this->_loginNamespace->session_user_id;
        
        // Receive searchType filter parameter
        $searchType = $this->getRequest()->getParam('searchType');
        $searchType = $searchType ? $searchType : 'latest';
        $this->view->searchType = $searchType;
        
        // Prepare pagination info
        $pageNo = $this->_getParam('page');
        if (empty($pageNo)) $pageNo = 1;
        $perPage = 8;
        $startLimit = $perPage*($pageNo-1);
                
        // Create model mapper objects
        $projectMapper = new Application_Model_ProjectMapper();
        $userMapper = new Application_Model_UserMapper();
        $primaryCategoryMapper = new Application_Model_PrimaryCategoryMapper();
        
        // Get premium projects
        $projectListPremium = $projectMapper->getProjectsPremium();
        
        // Get total default project count and default projects
        $totalRows = $projectMapper->getProjectsDefaultCount($searchType);
        $projectListDefault = $projectMapper->getProjectsDefault($searchType, $startLimit, $perPage);
        
        // fetching assigned users list by projectIds
        $projectIds = array();
        foreach($projectListPremium as $project) {
            $projectIds[] = $project->getProjectId();
        }
        foreach($projectListDefault as $project) {
            $projectIds[] = $project->getProjectId();
        }
        if ( !empty($projectIds) ) {
            $projectBidMapper = new Application_Model_ProjectBidMapper();
            $this->view->assignedProjectsUsers = $projectBidMapper->getAssignedProjectsUsers($projectIds);
        }

        $this->view->projectListPremium = $projectListPremium;
        $this->view->projectListDefault = $projectListDefault;

        // Get logged in user primary category or set default category
        if ($sessionUserId) {
            $userPrimaryCategoryId = $userMapper->getPrimaryCategoryByUser($sessionUserId);
        } else $userPrimaryCategoryId = 1;
        
        // Get user primary category name 
        $this->view->categoryName = $primaryCategoryMapper->getPrimaryCategoriyTitle($userPrimaryCategoryId);
        
        // Get projects by user primary category
        $this->view->projectsByCategory = $projectMapper->getProjectsByCategory($userPrimaryCategoryId);
        
        // pagination
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Null($totalRows));
        $paginator->setCurrentPageNumber($pageNo);
        $paginator->setItemCountPerPage($perPage);
        $this->view->pagination = $paginator;
    }

    /**
     * Active projects
     *
     * @return void
     */    
    public function activeProjectsAction()
    {
        // Get logged in userId
        $sessionUserId = $this->_loginNamespace->session_user_id;
        
        // Redirect to login page if user is not logged in
        if (empty($sessionUserId)) {
            $this->_redirector->gotoSimple('login', 'index');
        }
        
        // Receive searchType filter parameter
        $searchType = $this->getRequest()->getParam('searchType');
        $searchType = $searchType ? $searchType : 'latest';
        $this->view->searchType = $searchType;
        
        // Prepare pagination info
        $pageNo = $this->_getParam('page');
        if (empty($pageNo)) $pageNo = 1;
        $perPage = 8;
        $startLimit = $perPage*($pageNo-1);
        
        // Get total active project count and active projects
        $projectMapper = new Application_Model_ProjectMapper();
        $totalRows = $projectMapper->getActiveProjectsCount($sessionUserId);
        $activeProjects = $projectMapper->getActiveProjects($sessionUserId, $searchType, $startLimit, $perPage);
        $this->view->activeProjects = $activeProjects;
        
        // fetching assigned users list by projectIds
        $projectIds = array();
        foreach($activeProjects as $project) {
            $projectIds[] = $project->getProjectId();
        }
        if ( !empty($projectIds) ) {
            $projectBidMapper = new Application_Model_ProjectBidMapper();
            $this->view->assignedProjectsUsers = $projectBidMapper->getAssignedProjectsUsers($projectIds);
        }
        
        // pagination
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Null($totalRows));
        $paginator->setCurrentPageNumber($pageNo);
        $paginator->setItemCountPerPage($perPage);
        $this->view->pagination = $paginator;
    }

    /**
     * Projects bidded by loggedon user
     *
     * @return void
     */    
    public function biddedProjectsAction()
    {
        // Get logged in userId
        $sessionUserId = $this->_loginNamespace->session_user_id;
        
        // Redirect to login page if user is not logged in
        if (empty($sessionUserId)) {
            $this->_redirector->gotoSimple('login', 'index');
        }
        
        // Receive searchType filter parameter
        $searchType = $this->getRequest()->getParam('searchType');
        $searchType = $searchType ? $searchType : 'latest';
        $this->view->searchType = $searchType;
        
        // Prepare pagination info
        $pageNo = $this->_getParam('page');
        if (empty($pageNo)) $pageNo = 1;
        $perPage = 3;
        $startLimit = $perPage*($pageNo-1);
        
        // Get total bidded project count and bidded projects
        $projectMapper = new Application_Model_ProjectMapper();
        $totalRows = $projectMapper->getBiddedProjectsCount($sessionUserId);
        $biddedProjects = $projectMapper->getBiddedProjects($sessionUserId, $searchType, $startLimit, $perPage);
        $this->view->biddedProjects = $biddedProjects;
        
        // fetching assigned users list by projectIds
        $projectIds = array();
        foreach($biddedProjects as $project) {
            $projectIds[] = $project->getProjectId();
        }
        if ( !empty($projectIds) ) {
            $projectBidMapper = new Application_Model_ProjectBidMapper();
            $this->view->assignedProjectsUsers = $projectBidMapper->getAssignedProjectsUsers($projectIds);
        }
        
        // pagination
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Null($totalRows));
        $paginator->setCurrentPageNumber($pageNo);
        $paginator->setItemCountPerPage($perPage);
        $this->view->pagination = $paginator;
    }
    
    /**
     * Archived projects
     *
     * @return void
     */    
    public function archiveProjectsAction()
    {
        // Get logged in userId
        $sessionUserId = $this->_loginNamespace->session_user_id;
        
        // Redirect to login page if user is not logged in
        if (empty($sessionUserId)) {
            $this->_redirector->gotoSimple('login', 'index');
        }
        
        // Receive searchType filter parameter
        $searchType = $this->getRequest()->getParam('searchType');
        $searchType = $searchType ? $searchType : 'latest';
        $this->view->searchType = $searchType;
        
        // Prepare pagination info
        $pageNo = $this->_getParam('page');
        if (empty($pageNo)) $pageNo = 1;
        $perPage = 8;
        $startLimit = $perPage*($pageNo-1);
        
        // Gte total archive project count and archive projects
        $projectMapper = new Application_Model_ProjectMapper();
        $totalRows = $projectMapper->getArchiveProjectsCount($sessionUserId);
        $archiveProjects = $projectMapper->getArchiveProjects($sessionUserId, $searchType, $startLimit, $perPage);
        $this->view->archiveProjects = $archiveProjects;
        
        // fetching assigned users list by projectIds
        $projectIds = array();
        foreach($archiveProjects as $project) {
            $projectIds[] = $project->getProjectId();
        }
        if ( !empty($projectIds) ) {
            $projectBidMapper = new Application_Model_ProjectBidMapper();
            $this->view->assignedProjectsUsers = $projectBidMapper->getAssignedProjectsUsers($projectIds);
        }
        
        // pagination
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Null($totalRows));
        $paginator->setCurrentPageNumber($pageNo);
        $paginator->setItemCountPerPage($perPage);
        $this->view->pagination = $paginator;
    }
    
    /**
     * Archived projects bidded by loggedon user
     *
     * @return void
     */    
    public function archiveBiddedProjectsAction()
    {
        // Get logged in userId
        $sessionUserId = $this->_loginNamespace->session_user_id;
        
        // Redirect to login page if user is not logged in
        if (empty($sessionUserId)) {
            $this->_redirector->gotoSimple('login', 'index');
        }
        
        // Receive searchType filter parameter
        $searchType = $this->getRequest()->getParam('searchType');
        $this->view->searchType = $searchType;
        
        // Prepare pagination info
        $pageNo = $this->_getParam('page');
        if (empty($pageNo)) $pageNo = 1;
        $perPage = 8;
        $startLimit = $perPage*($pageNo-1);
        
        // Get total archive bidded project count and archive bidded projects
        $projectMapper = new Application_Model_ProjectMapper();
        $totalRows = $projectMapper->getArchiveBiddedProjectsCount($sessionUserId);
        $archiveBiddedProjects = $projectMapper->getArchiveBiddedProjects($sessionUserId, $searchType, $startLimit, $perPage);
        $this->view->archiveBiddedProjects = $archiveBiddedProjects;
        
        // fetching assigned users list by projectIds
        $projectIds = array();
        foreach($archiveBiddedProjects as $project) {
            $projectIds[] = $project->getProjectId();
        }
        if ( !empty($projectIds) ) {
            $projectBidMapper = new Application_Model_ProjectBidMapper();
            $this->view->assignedProjectsUsers = $projectBidMapper->getAssignedProjectsUsers($projectIds);
        }
        
        // pagination
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Null($totalRows));
        $paginator->setCurrentPageNumber($pageNo);
        $paginator->setItemCountPerPage($perPage);
        $this->view->pagination = $paginator;
    }
    
    /**
     * Project details
     *
     * @return void
     */    
    public function projectDetailsAction()
    {
        // Receive projectId parameter
        $projectId = $this->getRequest()->getParam('projectId');
        
        // Redirect to project page if projectId is not provided
        if (empty($projectId)) {
            $this->_redirector->gotoSimple('index', 'project');
        }
        
        // Get success messages from session for view part and then unset
        $this->view->message = $this->_messageNamespace->message;
        unset($this->_messageNamespace->message);
        
        // Get logged in userId
        $sessionUserId = $this->_loginNamespace->session_user_id;

        // Fetch project details as Project object
        $projectMapper = new Application_Model_ProjectMapper();
        $this->view->projectDetails = $projectMapper->getProjectDetails($projectId);

        // Detect if logged in user is the owner of the project
        $this->view->isProjectOwner = $this->view->projectDetails->getUserId() == $sessionUserId ? true : false;

        // Fetch project attachments as ProjectAttachment object
        $projectAttachmentMapper = new Application_Model_ProjectAttachmentMapper();
        $this->view->projectAttachments = $projectAttachmentMapper->getProjectAttachments($projectId);
        
        $projectBidMapper = new Application_Model_ProjectBidMapper();
        
        // Fetch assigned bid ProjectBid objects
        $this->view->assignedBid = $assignedBid = $projectBidMapper->getAssignedBid($projectId);
        
        // Fetch all bids of the project as ProjectBid objects
        $this->view->projectBids = $projectBidMapper->getProjectBids($projectId);

        if ($sessionUserId) {
            // User bid count for last one month
            $this->view->bidNumberCount = $projectBidMapper->getBidNumberCount($sessionUserId);
        }
        $assignedBidderUserId = $assignedBid->getBidderUserId();
        // Detect if the project is assignd to any bidder
        $this->view->isProjectAssigned = !empty($assignedBidderUserId) ? true : false;
        
        // Detect if the project accepted or declined
        $this->view->afterProjectAccept = $this->view->assignedBid->getAcceptDecline() ? true : false; // need query

        // Detect if the logged in user is a bidder of hte project
        $this->view->isCurrentUserBidder = false;
        if ($sessionUserId) {
            foreach($this->view->projectBids as $bid) {
                 if ($bid->getBidderUserId() == $sessionUserId) {
                     $this->view->isCurrentUserBidder = true;
                     break;
                 }
            }
            
            // Check if the logged in user is a paid member
            $paymentRecordMapper = new Application_Model_PaymentRecordMapper();
            $this->view->paymentCheck = $paymentRecordMapper->checkPayment($sessionUserId);
        }
        
        // Per month bid quota
        $this->view->bidNumberPerMonth = Zend_Registry::get('bidNumberPerMonth');
        
    }

    /**
     * Assign project
     * 
     * Project owner (loggedin user) assigns project to chosen bidder
     *
     * @return void
     */    
    public function assignProjectAction()
    {
        // Get logged in userId
        $projectOwnerUserId = $this->_loginNamespace->session_user_id;
        
        // Disable layout and stop view rendering
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        // Receive projectId and bidderUserId parameters
        $projectId = $this->getRequest()->getUserParam('projectId');
        $bidderUserId = $this->getRequest()->getUserParam('bidderUserId');
        
        // Creating mapper objects
        $projectBidMapper = new Application_Model_ProjectBidMapper();
        $projectMapper = new Application_Model_ProjectMapper();
        $messageMapper = new Application_Model_MessageMapper();
        
        // Prepare message object to use in model
        $message = new Application_Model_Message();
        $message->setProjectId($projectId);
        $message->setSenderUserId($projectOwnerUserId);
        $message->setReceiverUserId($bidderUserId);
        $message->setMessage('A project has been assigned to you. Please '
                           . '<a href="' . $this->view->baseUrl('project/project-details')
                           . '/' . $projectId.'/">click here</a> to see details.');
        
        // Begin sql transaction to keep updates stable
        $bootstrap = $this->getInvokeArg('bootstrap');
        $bootstrap->bootstrap('db');
        $db = $bootstrap->getResource('db');
        $db->beginTransaction();
        
        try {
            // Update bid status to assigned
            $projectBidMapper->updateBidAssigned($projectId, $bidderUserId);
            // Update project to frozen
            $projectMapper->updateProjectFrozen($projectId);
            // Save project message
            $messageMapper->saveMessage($message);
            // Commit all updates
            $db->commit();
        } catch (Exception $e) {
            // Rollback updates on failure
            $db->rollBack();
            //echo $e->getMessage();
        }
        
        // Need to send email       
        $projectUserBid = $projectBidMapper->getProjectUserBid($projectId, $bidderUserId);
        
        // Redirect to project details page
        $this->_redirector->gotoRoute(array('projectId' => $projectId), 'projectDetails');
    }
    
    /**
     * Accept won project
     *
     * Bidder (loggedin user) accepts won project
     * 
     * @return void
     */    
    public function acceptProjectAction()
    {
        // Get logged in userId and username
        $sessionUserId = $this->_loginNamespace->session_user_id;
        $sessionUserName = $this->_loginNamespace->session_username;
        
        // Disable layout and stop view rendering
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        // Receive projectId and bidderUserId parameters
        $projectId = $this->getRequest()->getUserParam('projectId');
        $bidderUserId = $this->getRequest()->getUserParam('bidderUserId');
        
        // If logged in user is not the bidder, stop him
        if ( $sessionUserId != $bidderUserId ) die('Hacking attempt');
        
        // Create mapper objects
        $projectBidMapper = new Application_Model_ProjectBidMapper();
        $projectMapper = new Application_Model_ProjectMapper();
        $messageMapper = new Application_Model_MessageMapper();
        $creditBalanceMapper = new Application_Model_CreditBalanceMapper();
        
        // Get project and project owner info
        $project = $projectMapper->getProject($projectId);
        
        // Get bidder bid amount
        $projectUserBidAmount = $projectBidMapper->getProjectUserBidAmount($projectId, $bidderUserId);
        
        // Prepare credit balance object of project owner
        $creditBalanceOwner = new Application_Model_CreditBalance();
        $creditBalanceOwner->setUserId($bidderUserId);
        $creditBalanceOwner->setTransactionForUserId($project->getProjectOwner()->getUserId());
        $creditBalanceOwner->setCreatedOn(date('Y-m-d H:i:s', time()));
        $creditBalanceOwner->setType('earned');
        $creditBalanceOwner->setBalance($projectUserBidAmount);
        $creditBalanceOwner->setStatus(1);
        
        // Prepare credit balance object of bidder
        $creditBalanceBidder = new Application_Model_CreditBalance();
        $creditBalanceBidder->setUserId($project->getProjectOwner()->getUserId());
        $creditBalanceBidder->setTransactionForUserId($bidderUserId);
        $creditBalanceBidder->setCreatedOn(date('Y-m-d H:i:s', time()));
        $creditBalanceBidder->setType('spend');
        $creditBalanceBidder->setBalance($projectUserBidAmount);
        $creditBalanceBidder->setStatus(1);
        
        // Prepare message object
        $message = new Application_Model_Message();
        $message->setProjectId($projectId);
        $message->setSenderUserId($bidderUserId);
        $message->setReceiverUserId($project->getProjectOwner()->getUserId());
        $message->setMessage('Your assign project has accepted by ' 
                           . $sessionUserName . '.To show details please ' 
                           . '<a href="' . $this->view->baseUrl('project/project-details') . '/' 
                           . $projectId . '/">click here</a>');
        
        // Begin sql transaction to keep updates stable
        $bootstrap = $this->getInvokeArg('bootstrap');
        $bootstrap->bootstrap('db');
        $db = $bootstrap->getResource('db');
        $db->beginTransaction();
        
        try {
            // Update bid status to accepted
            $projectBidMapper->setBidAcceptDecline($projectId, $bidderUserId);
            // Update project status to closed
            $projectMapper->updateProjectClose($projectId, $bidderUserId);
            // Save project owner credit balance info
            $creditBalanceMapper->saveCreditBalance($creditBalanceOwner);
            // Save bidder credit balance info
            $creditBalanceMapper->saveCreditBalance($creditBalanceBidder);
            // Save message
            $messageMapper->saveMessage($message);
            // Commit all updates
            $db->commit();
        } catch (Exception $e) {
            // Rollback updates on failure
            $db->rollBack();
            echo $e->getMessage();
        }
        
        // Redirect to project details page
        $this->_redirector->gotoRoute(array('projectId' => $projectId), 'projectDetails');
    }
    
    /**
     * Decline won project
     *
     * Bidder (loggedin user) declines won project
     *
     * @return void
     */    
    public function declineProjectAction()
    {
        // Get logged in userId and username
        $sessionUserId = $this->_loginNamespace->session_user_id;
        $sessionUserName = $this->_loginNamespace->session_username;
        
        // Disable layout and stop view rendering
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        // Receive projectId and bidderUserId parameters
        $projectId = $this->getRequest()->getUserParam('projectId');
        $bidderUserId = $this->getRequest()->getUserParam('bidderUserId');
        
        // If logged in user is not the bidder, stop him
        if ( $sessionUserId != $bidderUserId ) die('Hacking attempt');
        
        // Create mapper objects
        $projectBidMapper = new Application_Model_ProjectBidMapper();
        $projectMapper = new Application_Model_ProjectMapper();
        $messageMapper = new Application_Model_MessageMapper();
        
        // Get project and project owner info
        $project = $projectMapper->getProject($projectId);
        
        // Prepare message object
        $message = new Application_Model_Message();
        $message->setProjectId($projectId);
        $message->setSenderUserId($bidderUserId);
        $message->setReceiverUserId($project->getProjectOwner()->getUserId());
        $message->setMessage('A project has been decline by'
                           . $sessionUserName.'. To show details please ' 
                           . '<a href="' . $this->view->baseUrl('project/project-details') . '/' 
                           . $projectId.'/">click here</a>');
        
        // Begin sql transaction to keep updates stable
        $bootstrap = $this->getInvokeArg('bootstrap');
        $bootstrap->bootstrap('db');
        $db = $bootstrap->getResource('db');
        $db->beginTransaction();
        
        try {
            // Reset bid assignment and set decline
            $projectBidMapper->unsetBidAcceptDecline($projectId, $bidderUserId);
            // Update project status to open
            $projectMapper->updateProjectOpen($projectId);
            // Save message
            $messageMapper->saveMessage($message);
            // Commit updates
            $db->commit();
        } catch (Exception $e) {
            // Rollback updates on failure
            $db->rollBack();
            echo $e->getMessage();
        }
        
        // Redirect to project details page
        $this->_redirector->gotoRoute(array('projectId' => $projectId), 'projectDetails');
    }
    
    /**
     * Cancel project
     *
     * Project owner (loggedin user) cancels own project
     *
     * @return void
     */    
    public function cancelProjectAction()
    {
        // Disable layout and stop view rendering
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        // Receive projectId parameters
        $projectId = $this->getRequest()->getUserParam('projectId');
        
        // Update project status to cancel
        $projectMapper = new Application_Model_ProjectMapper();
        $projectMapper->updateProjectCancel($projectId);
        
        // Redirect to project page
        $this->_redirector->gotoSimple('index', 'project');
    }

    /**
     * Delete bid
     *
     * Bidder (loggedin user) deletes bid on a project
     *
     * @return void
     */    
    public function deleteBidAction()
    {
        // Get logged in userId
        $sessionUserId = $this->_loginNamespace->session_user_id;
        
        // Disable layout and stop view rendering
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        // Receive projectId parameters
        $projectId = $this->getRequest()->getUserParam('projectId');
        
        // Set bid status to deleted
        $projectBidMapper = new Application_Model_ProjectBidMapper();
        $projectBidMapper->setBidDeleted($projectId, $sessionUserId);
        
        // Redirect to project deyails page
        $this->_redirector->gotoRoute(array('projectId' => $projectId), 'projectDetails');
    }

    /**
     * Account default page action
     *
     * @return void
     * @todo: work when projectBidAction is done
     */    
    public function projectBidPayment()
    {
        // Disable layout and stop view rendering
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        // Receive projectId and bidderUserId parameters
        $projectId = $this->getRequest()->getUserParam('projectId');
        $paymentCheck = $this->getRequest()->getUserParam('paymentCheck');
        
        if($paymentCheck == 'add') {   
            redirect(BASEURL.'project/project-bid/'.$project_id);
            $this->_redirector->gotoSimple('project-bid', 
                                              'project', 
                                              null, 
                                              array('projectId' => $projectId));
        } else {

        }
        
    }

    /**
     * Feedback/rating page for project owner
     *
     * @return void
     */    
    public function ownerRatingAction()
    {
        // Get logged in userId and username
        $sessionUserId = $this->_loginNamespace->session_user_id;
        $this->view->sessionUsername = $this->_loginNamespace->session_username;
        
        // Redirect to login page if user is not logged in
        if (empty($sessionUserId)) {
            $this->_redirector->gotoSimple('login', 'index');
        }
        
        // Receive projectId and bidderUserId parameters
        $projectId = $this->getRequest()->getUserParam('projectId');
        $this->view->projectId = $projectId;
        $this->view->bidderUserId = $this->getRequest()->getUserParam('bidderUserId');
        
        // Get project assigned user info
        $projectMapper = new Application_Model_ProjectMapper();
        $this->view->projectAssignedUser = $projectMapper->getProjectAssignedUser($projectId);
    }

    /**
     * Feedback/rating page for bidder
     *
     * @return void
     */    
    public function bidderRatingAction()
    {
        // Get logged in userId and username
        $sessionUserId = $this->_loginNamespace->session_user_id;
        $this->view->sessionUsername = $this->_loginNamespace->session_username;
        
        // Redirect to login page if user is not logged in
        if (empty($sessionUserId)) {
            $this->_redirector->gotoSimple('login', 'index');
        }
        
        // Receive projectId and bidderUserId parameters
        $projectId = $this->getRequest()->getUserParam('projectId');
        $this->view->projectId = $projectId;
        $this->view->ownerUserId = $this->getRequest()->getUserParam('ownerUserId');
        
        // Get project and project owner info
        $projectMapper = new Application_Model_ProjectMapper();
        $this->view->projectOwnerUser = $projectMapper->getProject($projectId);
    }

    /**
     * Save feedback/rating by project owner
     *
     * @return void
     */    
    public function saveOwnerRatingAction()
    {
        // Get logged in userId
        $sessionUserId = $this->_loginNamespace->session_user_id;
        
        // Redirect to login page if user is not logged in
        if (empty($sessionUserId)) {
            $this->_redirector->gotoSimple('login', 'index');
        }
        
        // Stop view rendering
        $this->_helper->viewRenderer->setNoRender(true);
        
        // Receive rating post values
        $projectId = $this->getRequest()->getPost('project_id');
        $bidderUserId = $this->getRequest()->getPost('BIDDER_user_id');
        $rating = $this->getRequest()->getPost('rating');
        $comment = $this->getRequest()->getPost('comment');
        $postDate = date('Y-m-d H:i:s',time());
        
        // Prepare feedback object for model
        $feedback = new Application_Model_Feedback();
        $feedback->setProjectId($projectId);
        $feedback->setOwnerUserId($sessionUserId);
        $feedback->setBidderUserId($bidderUserId);
        $feedback->setOwnerFeedbackRate($rating);
        $feedback->setOwnerComment($comment);
        $feedback->setOwnerPostDate($postDate);
        $feedback->setStatus(2);
        
        // Update project owner feedback if bidder feedback was given previously
        $feedbackMapper = new Application_Model_FeedbackMapper();
        $success = $feedbackMapper->updateOwnerFeedback($feedback);
        if ($success) {
            // Set project as archived after both owner and bidder feedback is given
            $projectMapper = new Application_Model_ProjectMapper();
            $projectMapper->updateProjectArchive($projectId);
        } else {
            // Insert owner feedback
            $feedbackMapper->saveOwnerFeedback($feedback);
        }
        
        // Get project bidder and project info
        $projectBidMapper = new Application_Model_ProjectBidMapper();
        $projectUserBid = $projectBidMapper->getProjectUserBid($projectId, $bidderUserId);
        
        $subject = "3lance / " . $sessionUserId . " has written a testimonial on the project " . $projectUserBid->getProject()->getProjectTitle() . "";
        
        // TODO: email work
        
        // Prepare message
        $message = new Application_Model_Message();
        $message->setProjectId($projectId);
        $message->setSenderUserId($sessionUserId);
        $message->setReceiverUserId($bidderUserId);
        $message->setSubject($subject);
        $message->setMessage("3lance / " . $sessionUserId . " has written a testimonial on the project " . $projectUserBid->getProject()->getProjectTitle() . "");
        
        // Save message
        $messageMapper = new Application_Model_MessageMapper();
        $messageMapper->saveMessage($message);
        
        // Redirect to active-projects page
        $this->_redirector->gotoRoute(array('searchType' => 'latest'), 'activeProjects');
    }

    /**
     * Save feedback/rating by bidder
     *
     * @return void
     */    
    public function saveBidderRatingAction()
    {
        // Get logged in userId
        $sessionUserId = $this->_loginNamespace->session_user_id;
        
        // Redirect to login page if user is not logged in
        if (empty($sessionUserId)) {
            $this->_redirector->gotoSimple('login', 'index');
        }
        
        // Stop view rendering
        $this->_helper->viewRenderer->setNoRender(true);
        
        // Receive rating post values
        $projectId = $this->getRequest()->getPost('project_id');
        $ownerUserId = $this->getRequest()->getPost('owner_user_id');
        $rating = $this->getRequest()->getPost('rating');
        $comment = $this->getRequest()->getPost('comment');
        $postDate = date('Y-m-d H:i:s',time());
        
        // Prepare bidder feedback
        $feedback = new Application_Model_Feedback();
        $feedback->setProjectId($projectId);
        $feedback->setOwnerUserId($ownerUserId);
        $feedback->setBidderUserId($sessionUserId);
        $feedback->setBidderFeedbackRate($rating);
        $feedback->setBidderComment($comment);
        $feedback->setBidderPostDate($postDate);
        $feedback->setStatus(2);
        
        // Update assigned bidded feedback if project owner feedback was given previously
        $feedbackMapper = new Application_Model_FeedbackMapper();
        $success = $feedbackMapper->updateBidderFeedback($feedback);
        if ($success) {
            // Set project as archived after both owner and bidder feedback is given
            $projectMapper = new Application_Model_ProjectMapper();
            $projectMapper->updateProjectArchive($projectId);
        } else {
            // Insert bidder feedback
            $feedbackMapper->saveBidderFeedback($feedback);
        }
        
        // Get project owner and project info
        $projectMapper = new Application_Model_ProjectMapper();
        $projectUser = $projectMapper->getProject($projectId);
        
        $subject = "3lance / " . $sessionUserId . " has written a testimonial on the project " . $projectUser->getProjectTitle() . "";
        
        // TODO: email work
        
        // Prepare message
        $message = new Application_Model_Message();
        $message->setProjectId($projectId);
        $message->setSenderUserId($sessionUserId);
        $message->setReceiverUserId($ownerUserId);
        $message->setSubject($subject);
        $message->setMessage("3lance / " . $sessionUserId . " has written a testimonial on the project " . $projectUser->getProjectTitle() . "");
        
        // Save message
        $messageMapper = new Application_Model_MessageMapper();
        $messageMapper->saveMessage($message);
        
        // Redirect to bidded-projects page
        $this->_redirector->gotoRoute(array('searchType' => 'latest'), 'biddedProjects');
    }

    /**
     * Feedbacks given by loggedin user as a project owenr or bidder
     *
     * @return void
     */    
    public function feedbacksByMeAction()
    {
        // Get logged in userId
        $this->view->sessionUserId = $sessionUserId = $this->_loginNamespace->session_user_id;
        
        // Redirect to login page if user is not logged in
        if (empty($sessionUserId)) {
            $this->_redirector->gotoSimple('login', 'index');
        }
        
        // Receive searchType parameters
        $searchType = $this->getRequest()->getParam('searchType');
        
        // Prepare pagination info
        $pageNo = $this->_getParam('page');
        if (empty($pageNo)) $pageNo = 1;
        $perPage = 8;
        $startLimit = $perPage*($pageNo-1);
        
        $userMapper = new Application_Model_UserMapper();
        
        // Get total feedback count and feedbacks by logged in user both as bidder and project owner
        $feedbackMapper = new Application_Model_FeedbackMapper();
        $totalRows = $feedbackMapper->getFeedbacksByMeCount($sessionUserId);
        $this->view->givenFeedbacks = $feedbackMapper->getFeedbacksByMe($sessionUserId, $searchType, $startLimit, $perPage);
        
        // Assign feedback writer username in a array to retrieve using userId 
        $userFeedbacks = array();
        foreach($this->view->givenFeedbacks as $testimonial) {
            if($testimonial->getOwnerUserId() == $sessionUserId) { 
                $userIdFeedback = $testimonial->getBidderUserId();
                $userFeedbacks[$userIdFeedback] = $userMapper->getUsername($userIdFeedback);
            } else if($testimonial->getBidderUserId() == $sessionUserId) {  
                $userIdFeedback = $testimonial->getOwnerUserId();
                $userFeedbacks[$userIdFeedback] = $userMapper->getUsername($userIdFeedback);
            }
        }
        $this->view->userFeedbacks = $userFeedbacks;
        
        // pagination
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Null($totalRows));
        $paginator->setCurrentPageNumber($pageNo);
        $paginator->setItemCountPerPage($perPage);
        $this->view->pagination = $paginator;
    }
    
    /**
     * Feedbacks given to loggedin user as a project owenr or bidder
     *
     * @return void
     */    
    public function feedbacksForMeAction()
    {
        // Get logged in userId
        $this->view->sessionUserId = $sessionUserId = $this->_loginNamespace->session_user_id;
        
        // Redirect to login page if user is not logged in
        if (empty($sessionUserId)) {
            $this->_redirector->gotoSimple('login', 'index');
        }
        
        // Receive searchType parameters
        $searchType = $this->getRequest()->getParam('searchType');
        
        // Prepare info for pagination
        $pageNo = $this->_getParam('page');
        if (empty($pageNo)) $pageNo = 1;
        $perPage = 8;
        $startLimit = $perPage*($pageNo-1);
        
        $userMapper = new Application_Model_UserMapper();
        
        // Get total feedback count and feedbacks for logged in user both as bidder and project owner
        $feedbackMapper = new Application_Model_FeedbackMapper();
        $totalRows = $feedbackMapper->getFeedbacksForMeCount($sessionUserId);
        $this->view->givenFeedbacks = $feedbackMapper->getFeedbacksForMe($sessionUserId, $searchType, $startLimit, $perPage);
        
        // Assign feedback writer username in a array to retrieve using userId 
        $userFeedbacks = array();
        foreach($this->view->givenFeedbacks as $testimonial) {
            if($testimonial->getOwnerUserId() == $sessionUserId) { 
                $userIdFeedback = $testimonial->getBidderUserId();
                $userFeedbacks[$userIdFeedback] = $userMapper->getUsername($userIdFeedback);
            } else if($testimonial->getBidderUserId() == $sessionUserId) {  
                $userIdFeedback = $testimonial->getOwnerUserId();
                $userFeedbacks[$userIdFeedback] = $userMapper->getUsername($userIdFeedback);
            }
        }
        $this->view->userFeedbacks = $userFeedbacks;
        
        // pagination
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Null($totalRows));
        $paginator->setCurrentPageNumber($pageNo);
        $paginator->setItemCountPerPage($perPage);
        $this->view->pagination = $paginator;
    }

    /**
     * Create My_Form_JobPost form
     *
     * @param  array $params Array contains primaryCategories
     * @return My_Form_JobPost
     */    
    public function getJobPostForm(array $params = array())
    {
        $form = new My_Form_JobPost($params);
        return $form;
    }

    /**
     * Project post form page
     * 
     * Form to post project; after submiting form forwards to preview page
     *
     * @see projectSubmitPreviewAction
     * @return void
     */    
    public function projectSubmitAction()
    {
        // Get logged in userId
        $sessionUserId = $this->_loginNamespace->session_user_id;
        
        // Redirect to login page if user is not logged in
        if (empty($sessionUserId)) {
            $this->_redirector->gotoSimple('login', 'index');
        }
        
        // Get project primary categories
        $primaryCategoryMapper = new Application_Model_PrimaryCategoryMapper();
        $primaryCategories = $primaryCategoryMapper->getPrimaryCategories();
        
        // Create form, My_Form_JobPost to post project/job
        $params['primaryCategories'] = $primaryCategories;
        $form = $this->getJobPostForm($params);
        
        if ($this->getRequest()->isPost()) {
            if (!$form->isValid($_POST)) {
                // Retrieve form error messages and prepare for view part
                $errors = $form->getMessages();
                $this->view->error = '';
                foreach ($errors as $field => $fieldErrors) {
                    $this->view->error .= $field . ': ' . implode(' ', $fieldErrors) . '<br />';
                }
            } else {
                // On successful form validation forward to project-submit-preview
                $this->_forward('project-submit-preview');
            }
        }        
        
        // Assign form to view part
        $this->view->form = $form;
    }

    /**
     * Create My_Form_JobConfirm form
     *
     * @return My_Form_JobConfirm
     */   
    public function getJobConfirmForm()
    {
        $form = new My_Form_JobConfirm();
        return $form;
    }
    
    /**
     * Submitted project preview
     *
     * @see projectSubmitAction
     * @return void
     */    
    public function projectSubmitPreviewAction()
    {
        // Get logged in userId
        $sessionUserId = $this->_loginNamespace->session_user_id;
        
        // Redirect to login page if user is not logged in
        if (empty($sessionUserId)) {
            $this->_redirector->gotoSimple('login', 'index');
        }
        
        // Redirect to project-submit page user have not post project submit form
        if (!$this->getRequest()->isPost()) {
            $this->_redirector->gotoSimple('project-submit', 'project');
        }
        
        // Receive additional info check boxes input
        $additionalInfo = $this->getRequest()->getPost('additional_info');
        $meetUpRequired = in_array('Meet up required', $additionalInfo) ? 1 : 0;
        $milestonePayments = in_array('Milestone payments', $additionalInfo) ? 1 : 0;
        $requirePortfolio = in_array('Require portfolio', $additionalInfo) ? 1 : 0;
        // Receive and format bid_ending_date
        $bidEndingDate = date('Y-m-d, H:i:s',strtotime($this->getRequest()->getPost('bid_ending_date')));

        // Prepare project info for model
        $project = new Application_Model_Project();
        $project->setUserId($sessionUserId);
        $project->setCurrencyCode($this->getRequest()->getPost('CurrencyCode')); 
        $project->setProjectTitle($this->getRequest()->getPost('project_title'));  
        $project->setProjectDescription($this->getRequest()->getPost('project_description'));
        $project->setProjectCategoryId($this->getRequest()->getPost('primary_category_id'));
        $project->setCost($this->getRequest()->getPost('cost')); 
        $project->setBidEndingDate($bidEndingDate);  
        $project->setAdditionalRemarks($this->getRequest()->getPost('additional_remarks')); 
        $project->setMeetUpRequired($meetUpRequired);
        $project->setMilestonePayments($milestonePayments);
        $project->setRequirePortfolio($requirePortfolio);
        $project->setStatus(1); 
        $project->setCreatedOn(date('Y-m-d, H:i:s',time())); 
        
        // Save project to temporary table
        $temporaryProjectMapper = new Application_Model_TemporaryProjectMapper();
        $temporaryProjectId = $temporaryProjectMapper->saveProject($project);
        
        // Prepare project attachment to temporary table
        $projectAttachment = new Application_Model_ProjectAttachment();
        $projectAttachment->setProjectId($temporaryProjectId);
        $projectAttachment->setAttachment($this->getRequest()->getPost('attach1'));
        
        // Save project attachment
        $temporaryProjectAttachmentMapper = new Application_Model_TemporaryProjectAttachmentMapper();
        $temporaryProjectAttachmentMapper->saveProjectAttachment($projectAttachment);
        
        // Assign project and project attachment info for view part
        $this->view->project = $project;
        $this->view->projectAttachment = $projectAttachment;
        
        // Create and populate job confirm form, My_Form_JobConfirm
        $form = $this->getJobConfirmForm();
        $this->view->form = $form;
        $data = array();
        $data['project_id'] = $temporaryProjectId;
        $form->populate($data);
        
        // Get project primary categoriy title
        $primaryCategoryMapper = new Application_Model_PrimaryCategoryMapper();
        $this->view->primaryCategoriyTitle = $primaryCategoryMapper->getPrimaryCategoriyTitle($project->getProjectCategoryId());
        
    }
    
    /**
     * Save submitted project
     *
     * @return void
     */    
    public function projectSubmitConfirmAction()
    {
        // Get logged in userId
        $sessionUserId = $this->_loginNamespace->session_user_id;
        
        // Redirect to login page if user is not logged in
        if (empty($sessionUserId)) {
            $this->_redirector->gotoSimple('login', 'index');
        }
        
         // Redirect to project-submit page user have not post project submit form
        if (!$this->getRequest()->isPost()) {
            $this->_redirector->gotoSimple('project-submit', 'project');
        }
        
        // Receive project_id post value
        $temporaryProjectId = $this->getRequest()->getPost('project_id');

        // Get project from temporary table to save
        $temporaryProjectMapper = new Application_Model_TemporaryProjectMapper();
        $project = $temporaryProjectMapper->getProject($temporaryProjectId);
        
        // Save project to main table
        $projectMapper = new Application_Model_ProjectMapper();
        $projectId = $projectMapper->saveProject($project);
        
        // Get project attachment from temporary table
        $temporaryProjectAttachmentMapper = new Application_Model_TemporaryProjectAttachmentMapper();
        $projectAttachments = $temporaryProjectAttachmentMapper->getProjectAttachments($temporaryProjectId);
        
        // Save project attachment to main table
        $projectAttachmentMapper = new Application_Model_ProjectAttachmentMapper();
        foreach($projectAttachments AS $projectAttachment) {
            $projectAttachment->setProjectId($projectId);
            $projectAttachmentMapper->saveProjectAttachment($projectAttachment);
        }
        
        // Redirect to invite-member page
        $this->_forward('invite-member', null, null, array('projectId' => $projectId));        
    }
    
    /**
     * Create My_Form_ProjectBid form
     *
     * @param  array $params Array contains primaryCategories
     * @return My_Form_ProjectBid
     */    
    public function getProjectBidForm(array $params = array())
    {
        $form = new My_Form_ProjectBid($params);
        return $form;
    }
    
    /**
     * Project bid post form page
     * 
     * Form to post bid
     *
     * @return void
     */    
    public function projectBidAction()
    {
        // Get logged in userId
        $sessionUserId = $this->_loginNamespace->session_user_id;
        
        // Redirect to login page if user is not logged in 
        if (empty($sessionUserId)) {
            $this->_redirector->gotoSimple('login', 'index');
        }
        
        // Receive projectId parameters
        $projectId = $this->getRequest()->getParam('projectId');
        if (empty($projectId)) {
            $this->_redirector->gotoSimple('index', 'project');
        }
        
        // Get project owner and project info
        $projectMapper = new Application_Model_ProjectMapper();
        $this->view->projectInfo = $projectMapper->getProject($projectId);
        
        // Create project bid form, My_Form_ProjectBid
        $params['projectId'] = $projectId;
        $form = $this->getProjectBidForm($params);
        
        if ($this->getRequest()->isPost()) {
            if (!$form->isValid($_POST)) {
                // Retrieve form error messages and prepare for view part
                $errors = $form->getMessages();
                $this->view->error = '';
                foreach ($errors as $field => $fieldErrors) {
                    $this->view->error .= $field . ': ' . implode(' ', $fieldErrors) . '<br />';
                }
            } else {
                // Receive projectId post value
                $projectId = $this->getRequest()->getPost('projectId');
                
                // Prepare project bid info for model
                $projectBid = new Application_Model_ProjectBid();
                $projectBid->setProjectId($projectId);
                $projectBid->setBidderUserId($sessionUserId); 
                $projectBid->setBidAmount($this->getRequest()->getPost('bid_amount'));  
                $projectBid->setTimePeriod($this->getRequest()->getPost('time_period'));
                $projectBid->setStatus(1);
                $projectBid->setCreatedOn(date('Y-m-d, H:i:s',time())); 

                // Save project bid info
                $projectBidMapper = new Application_Model_ProjectBidMapper();
                $projectBidId = $projectBidMapper->saveProjectBid($projectBid);
                
                // Prepare project bid attachment info
                $projectBidAttach = new Application_Model_ProjectBidAttach();
                $projectBidAttach->setProjectId($projectId);
                $projectBidAttach->setBidderUserId($sessionUserId); 
                $projectBidAttach->setAttachment($this->getRequest()->getPost('attach1'));
                $projectBidAttach->setStatus(1);
                $projectBidAttach->setCreatedOn(date('Y-m-d, H:i:s',time())); 
                
                // Save project attachment
                $projectBidAttachMapper = new Application_Model_ProjectBidAttachMapper();
                $projectBidAttachMapper->saveProjectBidAttach($projectBidAttach);
                
                // Assign success message to session namespace
                $this->_messageNamespace->message = 'Your bid has been submitted successfully.';
                
                // redirect to project-details page
                $this->_redirector->gotoRoute(array('projectId' => $projectId), 'projectDetails');
            }
        }        
        
        $this->view->form = $form;
    }
    
    /**
     * Invite members to bid on created project
     *
     * @return void
     */    
    public function inviteMemberAction()
    {
        // Receive projectId parameter
        $this->view->projectId = $projectId = $this->getRequest()->getParam('projectId');
        
        // Get success message from session for view part and then unset
        if ($this->_messageNamespace->message) {
            $this->view->message = $this->_messageNamespace->message;
            unset($this->_messageNamespace->message);
        }
        
        // Get invited member count to bid on this project
        $invitedMapper = new Application_Model_InvitedMapper();
        $this->view->invitedMemberCount = $invitedMapper->getInvitedMemberCount($projectId)+1;
        
        // Receive userId parameter
        $userId = $this->getRequest()->getParam('userId');  

        if (!empty($projectId) && !empty($userId) 
            && $this->view->invitedMemberCount <= Zend_Registry::get('invitedMemberLimit')) {
                // Invite member $userId for the project $projectId
                $invited = new Application_Model_Invited();
                $invited->setProjectId($projectId);
                $invited->setUserId($userId);
                $invited->setStatus(1);
                $result = $invitedMapper->invitedSave($invited); 
                
                // Assign success message to session namespace
                $this->_messageNamespace->message = 'You have successfully invited this person!';
                
                // Redirect to invite-member page
                $this->_redirector->gotoRoute(array('projectId' => $projectId), 'inviteMember');
        }
        
        // Get members to invite
        $userMapper = new Application_Model_UserMapper();
        $this->view->suggestedInviteMembers = $userMapper->getMembersToInvite($this->view->projectId);
    }
    
}