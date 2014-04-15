<?php
/**
 * All account management actions
 * 
 * @category   Application
 * @package    Application_Controller
 * @author     Shoeb Abdullah <shoeb240@gmail.com>
 * @copyright  Copyright (c) 2013, Shoeb Abdullah
 * @uses       Zend_Controller_Action
 * @version    1.0
 */
class AccountController extends Zend_Controller_Action
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
        
        // Allowing Actions to Respond To Ajax Requests
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('bookmark-user', 'html')
                    ->addActionContext('cancel-bookmark', 'html')
                    ->initContext();
    }
    
    
    /**
     * Profile page action
     *
     * @return void
     */
    public function profileAction()
    {
        // Get logged in userId
        $this->view->sessionUserId = $sessionUserId = $this->_loginNamespace->session_user_id;
        
        // Get success or error messages from session namespace for view and then unset
        if ($this->_messageNamespace->message) {
            $this->view->message = $this->_messageNamespace->message;
            unset($this->_messageNamespace->message);
        }
        if ($this->_messageNamespace->error) {
            $this->view->error = $this->_messageNamespace->error;
            unset($this->_messageNamespace->error);
        }

        // Receive username parameter
        $username = $this->getRequest()->getParam('username');
        
        if (empty($username)) {
            // If username parameter is not passed, get logged in username
            $username = $this->_loginNamespace->session_username;
        }
        
        if (empty($username)) {
            // Redirect user to home page if profile owner 'username' is not found as param or in session  
            $this->_redirector->gotoSimple('index', 'index');
        }
        
        // Initialize model mapper objects
        $userMapper = new Application_Model_UserMapper();
        $projectMapper = new Application_Model_ProjectMapper();
        $projectBidMapper = new Application_Model_ProjectBidMapper();
        $feedbackMapper = new Application_Model_FeedbackMapper();
        $portfolioMapper = new Application_Model_PortfolioMapper();
        $bookmarkMapper = new Application_Model_BookmarkMapper();

        // Get userId from username
        $userId = $userMapper->getUserId($username);
        $this->view->userId = $userId;
        $this->view->user = $userMapper->getUserInfo($userId);
        
        // Get projects of profile owner
        $this->view->projectsOwn = $projectMapper->getProfileProjects($userId);

        // Get assigned bidders of profile owner projects
        $projectIds = array();
        foreach($this->view->projectsOwn as $project) {
            $projectIds[] = $project->getProjectId();
        }
        
        if ( !empty($projectIds) ) {
            // Get assigned bidder userId and username in an array indexed by project id
            $this->view->assignedProjectsUsers = $projectBidMapper->getAssignedProjectsUsers($projectIds);
        }

        // Get feedbacks of profile owner given as project owner or assigned bidder
        $this->view->testimonials = $feedbackMapper->getTestimonialsByUserId($userId);

        $userTestimonials = array();
        foreach($this->view->testimonials as $testimonial) {
            if($testimonial->getOwnerUserId() == $userId) { 
                $userIdTestimonial = $testimonial->getBidderUserId();
                // Get username by userId
                $userTestimonials[$userIdTestimonial] = $userMapper->getUsername($userIdTestimonial);
            } else if($testimonial->getBidderUserId() == $userId) {  
                $userIdTestimonial = $testimonial->getOwnerUserId();
                // Get username by userId
                $userTestimonials[$userIdTestimonial] = $userMapper->getUsername($userIdTestimonial);
            }
        }
        $this->view->userTestimonials = $userTestimonials;
        
        // Get portfolio of the profile owner
        $this->view->portfolioList = $portfolioMapper->getPortfolioByUserId($userId);
        
        // Check if logged in user already bookmarked profile owner
        $this->view->getIfBookmarked = $bookmarkMapper->getIfBookmarked($sessionUserId, $userId);
    }
    
    /**
     * Cancel bookmark
     * 
     * Cancel bookmark created by session user. This is an ajax response action.
     *
     * @return void
     */
    public function cancelBookmarkAction()
    {
        // Get logged in userId
        $this->view->sessionUserId = $sessionUserId = $this->_loginNamespace->session_user_id;
        
        // Stop view rendering
        $this->_helper->viewRenderer->setNoRender(true);

        // Get selected user id, selected_id to cancel bookmark
        $selectedId = $this->getRequest()->getPost('selected_id');

        $bookmarkMapper = new Application_Model_BookmarkMapper();
        
        // Cancel $selectedId from logged in user bookmark
        $success = $bookmarkMapper->deleteBookmark($sessionUserId, $selectedId);

        if ($success) echo 'yes';
        else echo 'no';
    }
    
    /**
     * Bookmark user
     * 
     * Bookmark user by sesson user. This is an ajax response action.
     *
     * @return void
     */
    public function bookmarkUserAction()
    {
        // Get logged in userId
        $this->view->sessionUserId = $sessionUserId = $this->_loginNamespace->session_user_id;
        
        // Stop view rendering
        $this->_helper->viewRenderer->setNoRender(true);

        // Get selected user id, selected_id to bookmark
        $selectedId = $this->getRequest()->getPost('selected_id');
        
        // Prepare bookmark info for model
        $bookmark = new Application_Model_Bookmark();
        $bookmark->setUserId($sessionUserId);
        $bookmark->setSelectedId($selectedId);
        $bookmark->setStatus(1);
        
        $bookmarkMapper = new Application_Model_BookmarkMapper();
        
        // Bookmark $selectedId for logged in user
        $success = $bookmarkMapper->bookmarkUser($bookmark);
        
        if ($success) echo 'yes';
        else echo 'no';
    }
    
    /**
     * User testimonials
     *
     * @return void
     */
    public function testimonialsAction()
    {
        // Get logged in userId
        $this->view->sessionUserId = $this->_loginNamespace->session_user_id;
        
        // Receive username parameter
        $username = $this->getRequest()->getParam('username');
        $this->view->username = $username;

        // Initialize model mapper objects
        $userMapper = new Application_Model_UserMapper();
        $feedbackMapper = new Application_Model_FeedbackMapper();

        // Get userid from username
        $userId = $userMapper->getUserId($username);
        $this->view->userId = $userId;

        // Prepare pagination info
        $pageNo = $this->getRequest()->getParam('pageNo');
        $perPage = 5;
        $startLimit = ($pageNo) ? $perPage*($pageNo - 1) : 0;
        
        // Get testimonials of a profile owner
        $this->view->testimonials = $feedbackMapper->getTestimonialsByUserId($userId, 'ASC', $startLimit, $perPage);
        
        $userTestimonials = array();
        foreach($this->view->testimonials as $testimonial) {
            if($testimonial->getOwnerUserId() == $userId) { 
                $userIdTestimonial = $testimonial->getBidderUserId();
                // Get username by userId
                $userTestimonials[$userIdTestimonial] = $userMapper->getUsername($userIdTestimonial);
            } else if($testimonial->getBidderUserId() == $userId) {  
                $userIdTestimonial = $testimonial->getOwnerUserId();
                // Get username by userId
                $userTestimonials[$userIdTestimonial] = $userMapper->getUsername($userIdTestimonial);
            }
        }
        $this->view->userTestimonials = $userTestimonials;
    }
    
    /**
     * User portfolio
     *
     * @return void
     */
    public function portfolioAction()
    {
        // Get logged in userId
        $this->view->sessionUserId = $this->_loginNamespace->session_user_id;
        
        // Get success or error messages from session namespace for view and then unset
        if ($this->_messageNamespace->message) {
            $this->view->message = $this->_messageNamespace->message;
            unset($this->_messageNamespace->message);
        }
        if ($this->_messageNamespace->error) {
            $this->view->error = $this->_messageNamespace->error;
            unset($this->_messageNamespace->error);
        }

        // Receive username parameter
        $username = $this->getRequest()->getParam('username');
        
        if (empty($username)) {
            // If username parameter is not passed, get logged in username
            $username = $this->_loginNamespace->session_username;
        }
        
        if (empty($username)) {
            // Redirect user to home page if profile owner 'username' is not found as param or in session
            $this->_redirector->gotoSimple('index', 'index');
        }
        
        $this->view->username = $username;

        $userMapper = new Application_Model_UserMapper();
        $portfolioMapper = new Application_Model_PortfolioMapper();

        // Get userId by username
        $userId = $userMapper->getUserId($username);
        $this->view->userId = $userId;

        // Get testimonials of a profile owner
        $this->view->portfolioList = $portfolioMapper->getPortfolioByUserId($userId);
    }
    
    /**
     * User portfolio details
     *
     * @return void
     */
    public function portfolioDetailsAction()
    {
        // Receive portfolioId parameter
        $portfolioId = $this->getRequest()->getParam('portfolioId');

        $portfolioMapper = new Application_Model_PortfolioMapper();

        // Get portfolio details
        $this->view->portfolioDetails = $portfolioMapper->getPortfolioDetails($portfolioId);
    }
    
    /**
     * Delete portfolio
     *
     * @return void
     */
    public function deletePortfolioAction()
    {
        // Get logged in userId and username
        $this->view->sessionUserId = $sessionUserId = $this->_loginNamespace->session_user_id;
        $sessionUsername = $this->_loginNamespace->session_username;
        
        // Redirect to login page if user is not logged in
        if (empty($sessionUserId)) {
            $this->_redirector->gotoSimple('login', 'index');
        }

        // Receive portfolioId parameter
        $portfolioId = $this->getRequest()->getParam('portfolioId');

        $portfolioMapper = new Application_Model_PortfolioMapper();
        
        // Get deleting profilio details to use after deletion
        $portfolioDetails = $portfolioMapper->getPortfolioDetails($portfolioId);
        
        // Delete portfolio
        $success = $portfolioMapper->deletePortfolio($portfolioId);

        if ($success) {
            $imageName = $portfolioDetails->getPortfolioImage();
            if ($imageName != '') {
                $imageDestination = $this->view->baseUrl('images/profile_image') . '/' . $imageName;
                if (file_exists($imageDestination)) {
                    // Remove deleted portfolio image
                    unlink($imageDestination);
                }
            }
            
            // Assign success message to session namespace
            $this->_messageNamespace->message = 'Portfolio deleted successfully.';
        } else {
            // Assign failure message to session namespace
            $this->_messageNamespace->error = 'Portfolio deletion failed.';
        }
        
        // Redirect to portfolio page
        $this->_redirector->gotoRoute(array('username' => $sessionUsername), 'portfolio');
    }
    
    /**
     * Account inbox
     *
     * @return void
     */
    public function inboxAction()
    {
        // Get logged in userId and username
        $this->view->sessionUserId = $sessionUserId = $this->_loginNamespace->session_user_id;
        $this->view->sessionUsername = $this->_loginNamespace->session_username;
        
        // Redirect to login page if user is not logged in
        if (empty($sessionUserId)) {
            $this->_redirector->gotoSimple('login', 'index');
        }
        
        if ($sessionUserId != '') {
            // Get messageSearchUser parameter 
            $this->view->messageSearchUser = $messageSearchUser = $this->_getParam('messageSearchUser');
            
            $messageMapper = new Application_Model_MessageMapper();

            // Count unread message in inbox
            $this->view->unreadMessageCount = $messageMapper->unreadMessageCount($sessionUserId);
            
            // Total inbox message count for pagination
            if ($messageSearchUser != '') {
                $totalRows = $messageMapper->getInboxSearchedCount($sessionUserId, $messageSearchUser);
            } else {
                $totalRows = $messageMapper->getInboxCount($sessionUserId);
            }

            // Prepare pagination info
            $pageNo = $this->_getParam('page');
            if (empty($pageNo)) $pageNo = 1;
            $perPage = 8;
            $startLimit = $perPage*($pageNo-1);
            
            // Get inbox messages
            if ($messageSearchUser != '') {
                $this->view->inboxList = $messageMapper->getInboxSearchedList($sessionUserId, $messageSearchUser, $startLimit, $perPage);
            } else {
                $this->view->inboxList = $messageMapper->getInboxList($sessionUserId, $startLimit, $perPage);
            }
            
            // pagination
            $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Null($totalRows));
            $paginator->setCurrentPageNumber($pageNo);
            $paginator->setItemCountPerPage($perPage);
            $this->view->pagination = $paginator;
        }

    }
    
    /**
     * Create My_Form_ReplyMessage form
     *
     * @param string $action Form action name
     * @return My_Form_ReplyMessage
     */
    public function getReplyMessageForm($action = '')
    {
        $form = new My_Form_ReplyMessage($action);
        return $form;
    }
    
    /**
     * Inbox for a particular project
     *
     * @return void
     */
    public function projectInboxAction()
    {
        // Get logged in userId
        $this->view->sessionUserId = $sessionUserId = $this->_loginNamespace->session_user_id;
        
        // Redirect to login page if user is not logged in
        if (empty($sessionUserId)) {
            $this->_redirector->gotoSimple('login', 'index');
        }
        
        // Receive projectId and senderUserId parameter
        $projectId = $this->getRequest()->getParam('projectId');
        $senderUserId = $this->getRequest()->getParam('senderUserId');
        $this->view->projectId = $projectId;
        $this->view->senderUserId = $senderUserId;
        
        // Reply message form action
        $action = $this->view->baseUrl('account/project-inbox') . '/' . $projectId . '/' . $senderUserId;
        
        // Get reply message form, My_Form_ReplyMessage
        $form = $this->getReplyMessageForm($action);
        $this->view->form = $form;
        
        if ($this->getRequest()->isPost()) {
            if (!$form->isValid($_POST)) {
                // Retrieve form error messages and prepare for view part
                $errors = $form->getMessages();
                $this->view->error = '';
                foreach ($errors as $field => $fieldErrors) {
                    $this->view->error .= $field . ': ' . implode(' ', $fieldErrors) . '<br />';
                }
            } else {
                $values = $form->getValues();
                
                // Prepare Message object to store
                $message = new Application_Model_Message(); 
                $message->setMessage($values['reply_message']);
                $message->setProjectId($projectId);
                $message->setReceiverUserId($senderUserId);
                $message->setSenderUserId($sessionUserId);
                
                // Save message
                $messageMapper = new Application_Model_MessageMapper();
                $messageMapper->saveMessage($message);
                
                // Clear form inputs
                $form->reset();
            }
        }
        
        // Get inbox project messages
        $messageMapper = new Application_Model_MessageMapper();
        $this->view->projectMessagesBySender = $messageMapper->getProjectMessagesBySender($projectId, 
                                                                                          $senderUserId, 
                                                                                          $sessionUserId);
        
        // Get project info
        $projectMapper = new Application_Model_ProjectMapper();
        $this->view->projectInfo = $projectMapper->getProject($projectId);
    }
    
    /**
     * Delete inbox message
     *
     * @return void
     */
    public function deleteInboxMessageAction()
    {
        // Stop view rendering
        $this->_helper->viewRenderer->setNoRender(true);
        
        // Get logged in userId
        $this->view->sessionUserId = $sessionUserId = $this->_loginNamespace->session_user_id;
        
        // Redirect to login page if user is not logged in
        if (empty($sessionUserId)) {
            $this->_redirector->gotoSimple('login', 'index');
        }

        // Receive projectId and senderUserId parameter
        $projectId = $this->getRequest()->getParam('projectId');
        $senderUserId = $this->getRequest()->getParam('senderUserId');

        // Delete message
        $messageMapper = new Application_Model_MessageMapper();        
        $messageMapper->deleteInboxMessage($projectId, $senderUserId, $sessionUserId);
        
        // Redirect to inbox page
        $this->_redirector->gotoSimple('inbox', 'account');
    }
    
    /**
     * Account outbox
     *
     * @return void
     */
    public function outboxAction()
    {
        // Get logged in userId and username
        $this->view->sessionUserId = $sessionUserId = $this->_loginNamespace->session_user_id;
        $this->view->sessionUsername = $this->_loginNamespace->session_username;
        
        // Redirect to login page if user is not logged in
        if (empty($sessionUserId)) {
            $this->_redirector->gotoSimple('login', 'index');
        }
        
        if ($sessionUserId != '') {
            $messageMapper = new Application_Model_MessageMapper();
            
            // Get messageSearchUser parameter 
            $this->view->messageSearchUser = $messageSearchUser = $this->getRequest()->getParam('messageSearchUser');

            // Total inbox message count for pagination
            if ($messageSearchUser != '') {
                $totalRows = $messageMapper->getOutboxSearchedCount($sessionUserId, $messageSearchUser);
            } else {
                $totalRows = $messageMapper->getOutboxCount($sessionUserId);
            }

            // Prepare pagination info
            $pageNo = $this->_getParam('page');
            if (empty($pageNo)) $pageNo = 1;
            $perPage = 8;
            $startLimit = $perPage*($pageNo-1);
            
            // Get outbox messages
            if ($messageSearchUser != ''){
                $this->view->outboxList = $messageMapper->getOutboxSearchedList($sessionUserId, $messageSearchUser, $startLimit, $perPage);
            } else {
                $this->view->outboxList = $messageMapper->getOutboxList($sessionUserId, $startLimit, $perPage);
            }
            
            // pagination
            $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Null($totalRows));
            $paginator->setCurrentPageNumber($pageNo);
            $paginator->setItemCountPerPage($perPage);
            $this->view->pagination = $paginator;
        }
    }
    
    /**
     * Delete outbox message
     *
     * @return void
     */
    public function deleteOutboxMessageAction()
    {
        // Stop view rendering
        $this->_helper->viewRenderer->setNoRender(true);
        
        // Get logged in userId
        $this->view->sessionUserId = $sessionUserId = $this->_loginNamespace->session_user_id;
        
        // Redirect to login page if user is not logged in
        if (empty($sessionUserId)) {
            $this->_redirector->gotoSimple('login', 'index');
        }

        // Receive projectId and senderUserId parameter
        $projectId = $this->getRequest()->getParam('projectId');
        $receiverUserId = $this->getRequest()->getParam('receiverUserId');

        // Delete outbox message
        $messageMapper = new Application_Model_MessageMapper();
        $messageMapper->deleteOutboxMessage($projectId, $receiverUserId, $sessionUserId);
        
        // Redirect to outbox page
        $this->_redirector->gotoSimple('inbox', 'account');
    }
    
    /**
     * Outbox for a particular project
     *
     * @return void
     */
    public function projectOutboxAction()
    {
        // Get logged in userId
        $this->view->sessionUserId = $sessionUserId = $this->_loginNamespace->session_user_id;
        
        // Redirect to login page if user is not logged in
        if (empty($sessionUserId)) {
            $this->_redirector->gotoSimple('login', 'index');
        }
        
        // Receive projectId and receiverUserId parameter
        $projectId = $this->getRequest()->getParam('projectId');
        $receiverUserId = $this->getRequest()->getParam('receiverUserId');
        $this->view->projectId = $projectId;
        $this->view->receiverUserId = $receiverUserId;
        
        // Get outbox project messages
        $messageMapper = new Application_Model_MessageMapper();
        $this->view->projectMessagesByReceiver = $messageMapper->getProjectMessagesBySender($projectId, 
                                                                                          $receiverUserId, 
                                                                                          $sessionUserId);
        
        // Get project owner and project info
        $projectMapper = new Application_Model_ProjectMapper();
        $this->view->projectInfo = $projectMapper->getProject($projectId);
    }
    
    /**
     * Create My_Form_AddPortfolio form
     *
     * @return My_Form_AddPortfolio
     */
    public function getAddPortfolioForm()
    {
        $form = new My_Form_AddPortfolio();
        return $form;
    }

    /**
     * Add portfolio
     *
     * @return void
     */
    public function addPortfolioAction()
    {
        // Get logged in userId and username
        $sessionUserId = $this->_loginNamespace->session_user_id;
        $this->view->sessionUsername = $sessionUsername = $this->_loginNamespace->session_username;
        
        // Redirect to login page if user is not logged in
        if (empty($sessionUserId)) {
            $this->_redirector->gotoSimple('login', 'index');
        }
        
        // Get add portfolio form, My_Form_AddPortfolio
        $form = $this->getAddPortfolioForm();
        
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                // Prepare portfolio object to store
                $portfolio = new Application_Model_Portfolio();
                $portfolio->setUserId($sessionUserId);
                $portfolio->setClientName($this->getRequest()->getPost('client_name'));
                $portfolio->setPortfolioTitle($this->getRequest()->getPost('portfolio_title'));
                $portfolio->setProjectUrl($this->getRequest()->getPost('project_url'));
                $portfolio->setProjectDescription($this->getRequest()->getPost('project_description'));
                $portfolio->setPortfolioImage($this->getRequest()->getPost('portfolio_image'));
                $portfolio->setStatus(1);
                $portfolio->setCreatedOn(date('Y-m-d, H:i:s', time()));

                // Add portfolio
                $portfolioMapper = new Application_Model_PortfolioMapper();
                $portfolioId = $portfolioMapper->addPortfolio($portfolio);

                if ($portfolioId > 0) {
                    // On success add success message to session namespace and redirect to portfolio page
                    $this->_messageNamespace->message = 'Portfolio added successfully.';
                    $this->_redirector->gotoRoute(array('username' => $sessionUsername), 'portfolio');
                } else {
                    // On failure add error message to session namespace and redirect to portfolio page
                    $this->_messageNamespace->error = 'Portfolio addition failed.';
                    $this->_redirector->gotoRoute(array('username' => $sessionUsername), 'portfolio');
                }
            } else {
                // Get form validation error messages and prepare for view part
                $errors = $form->getMessages();
                $this->view->error = '';
                foreach ($errors as $field => $fieldErrors) {
                    $this->view->error .= $field . ': ' . implode(' ', $fieldErrors) . '<br />';
                }
            }
            
        }        
        
        $this->view->form = $form;
    }
    
    /**
     * Create My_Form_EditPortfolio form
     *
     * @return My_Form_EditPortfolio
     */
    public function getEditPortfolioForm()
    {
        $form = new My_Form_EditPortfolio();
        return $form;
    }
    
    /**
     * Edit portfolio
     *
     * @return void
     */
    public function editPortfolioAction()
    {
        // Get logged in userId and username
        $sessionUserId = $this->_loginNamespace->session_user_id;
        $this->view->sessionUsername = $sessionUsername = $this->_loginNamespace->session_username;
        
        // Redirect to login page if user is not logged in
        if (empty($sessionUserId)) {
            $this->_redirector->gotoSimple('login', 'index');
        }
        
        // Get edit portfolio form, My_Form_EditPortfolio
        $form = $this->getEditPortfolioForm();
        
        $portfolioMapper = new Application_Model_PortfolioMapper();
        
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                // Prepare portfolio object to store
                $portfolio = new Application_Model_Portfolio();
                $portfolio->setUserId($sessionUserId);
                $portfolio->setPortfolioId($this->getRequest()->getPost('portfolio_id'));
                $portfolio->setClientName($this->getRequest()->getPost('client_name'));
                $portfolio->setPortfolioTitle($this->getRequest()->getPost('portfolio_title'));
                $portfolio->setProjectUrl($this->getRequest()->getPost('project_url'));
                $portfolio->setProjectDescription($this->getRequest()->getPost('project_description'));
                $portfolio->setPortfolioImage($this->getRequest()->getPost('portfolio_image'));
                $portfolio->setUpdatedOn(date('Y-m-d, H:i:s', time()));
            
                // Update portfolio
                $result = $portfolioMapper->updatePortfolio($portfolio);

                if ($result) {
                    // On success add success message to session namespace and redirect to portfolio page
                    $this->_messageNamespace->message = 'Portfolio updated successfully.';
                    $this->_redirector->gotoRoute(array('username' => $sessionUsername), 'portfolio');
                } else {
                    // On failure add error message to session namespace and redirect to portfolio page
                    $this->_messageNamespace->error = 'Portfolio update failed.';
                    $this->_redirector->gotoRoute(array('username' => $sessionUsername), 'portfolio');
                }
            } else {
                // Get form validation error messages and prepare for view part
                $errors = $form->getMessages();
                $this->view->error = '';
                foreach ($errors as $field => $fieldErrors) {
                    $this->view->error .= $field . ': ' . implode(' ', $fieldErrors) . '<br />';
                }
            }
            
        } 
        
        // Receive portfolioId parameter
        $portfolioId = $this->getRequest()->getParam('portfolioId');
        
        // Get portfolio details to prepopulate
        $portfolioDetails = $portfolioMapper->getPortfolioDetails($portfolioId);
        $data = array(
            'portfolio_id' => $portfolioDetails->getPortfolioId(),
            'portfolio_title' => $portfolioDetails->getPortfolioTitle(),
            'client_name' => $portfolioDetails->getClientName(),
            'project_url' => $portfolioDetails->getProjectUrl(),
            'project_description' => $portfolioDetails->getProjectDescription(),
            'portfolio_image' => $portfolioDetails->getPortfolioImage()
        );
        
        // Prepopulate edit portfolio form
        $form->populate($data);
        
        $this->view->portfolioImage = $portfolioDetails->getPortfolioImage();
        
        $this->view->form = $form;
    }
    
    /**
     * Create My_Form_EditProfile form
     *
     * @param  array $params Array contains profileImage and imagePath
     * @return My_Form_EditProfile
     */
    public function getEditProfileForm(array $params = array())
    {
        $form = new My_Form_EditProfile($params);
        return $form;
    }
    
    /**
     * Edit account profile
     *
     * @return void
     */
    public function editProfileAction()
    {
        // Get logged in userId and username
        $sessionUserId = $this->_loginNamespace->session_user_id;
        $sessionUsername = $this->_loginNamespace->session_username;
        
        // Redirect to login page if user is not logged in
        if (empty($sessionUserId)) {
            $this->_redirector->gotoSimple('login', 'index');
        }
        
        // Get logged in user info
        $userMapper = new Application_Model_UserMapper();
        $this->view->userInfo = $userInfo = $userMapper->getUserInfo($sessionUserId);
        
        // Get edit profile form, My_Form_EditProfile
        $params['profileImage'] = $userInfo->getProfileImage();
        $params['imagePath'] = $this->view->baseUrl('images');
        $form = $this->getEditProfileForm($params);
        
        if ($this->getRequest()->isPost()) {
            if (!$form->isValid($_POST)) {
                // Get form validation error messages and prepare for view part
                $errors = $form->getMessages();
                $this->view->error = '';
                foreach ($errors as $field => $fieldErrors) {
                    $this->view->error .= $field . ': ' . implode(' ', $fieldErrors) . '<br />';
                }
            } else {
                // Change layout to simple one
                $this->_helper->layout->setLayout('layout-simple');
        
                // Prepare user object to update
                $user = new Application_Model_User();
                $user->setUserId($sessionUserId);
                $user->setFullName($this->getRequest()->getPost('full_name'));
                $user->setProfileImage($this->getRequest()->getPost('profile_image'));
                $user->setEmail($this->getRequest()->getPost('email'));
                $user->setContactNo($this->getRequest()->getPost('contact_no'));
                $user->setCompany($this->getRequest()->getPost('company'));
                $user->setNricRocNumber($this->getRequest()->getPost('NRIC_ROC_number'));
                
                // Update user
                $userMapper = new Application_Model_UserMapper();
                $updated = $userMapper->updateUser($user);
                
                if ($updated) {
                    // On success add success message to session namespace and redirect to profile page
                    $this->_messageNamespace->message = 'Account updated successfully.';
                    $this->_redirector->gotoRoute(array('username' => $sessionUsername), 'profile');
                } else {
                    // On failure add error message to session namespace and redirect to profile page
                    $this->_messageNamespace->error = 'Accunt update failed.';
                    $this->_redirector->gotoRoute(array('username' => $sessionUsername), 'profile');

                } 
                
            }
        }
        
        // Prepare user info to prepopulate
        $data = array(
            'full_name' => $userInfo->getFullName(),
            'email' => $userInfo->getEmail(),
            'contact_no' => $userInfo->getContactNo(),
            'company' => $userInfo->getCompany(),
            'NRIC_ROC_number' => $userInfo->getNricRocNumber()
        );
        
        // Prepopulate edit profile form
        $form->populate($data);
        $this->view->form = $form;
    }

    
}