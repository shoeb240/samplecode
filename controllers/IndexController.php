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
class IndexController extends Zend_Controller_Action
{
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
        
        $this->_loginNamespace = new Zend_Session_Namespace('login');
        $this->_messageNamespace = new Zend_Session_Namespace('message');
    }
    
    /**
     * Home page default action
     *
     * @return void
     */    
    public function indexAction()
    {
        // Get success or error messages from session for view part and then unset
        if ($this->_messageNamespace->message) {
            $this->view->message = $this->_messageNamespace->message;
            unset($this->_messageNamespace->message);
        }
        if ($this->_messageNamespace->error) {
            $this->view->error = $this->_messageNamespace->error;
            unset($this->_messageNamespace->error);
        }
        
        // Get premium members
        $userMapper = new Application_Model_UserMapper();
        $this->view->membersPremium = $userMapper->getMembersPremium();
    }
    
    /**
     * Create My_Form_Login form
     *
     * @return My_Form_Login
     */    
    public function getLoginForm()
    {
        $form = new My_Form_Login();
        return $form;
    }
    
    /**
     * User login
     *
     * @return void
     */    
    public function loginAction()
    {
        $form = $this->getLoginForm();
        $this->view->form = $form;
        
        // Get success or error messages from session for view part and then unset
        if ($this->_messageNamespace->error) {
            $this->view->error = $this->_messageNamespace->error;
            unset($this->_messageNamespace->error);
        }

        if ($this->getRequest()->isPost()) {
            if (!$form->isValid($_POST)) {
                // Retrieve form error messages and prepare for view part
                $errors = $form->getMessages();
                $this->view->error = '';
                foreach ($errors as $field => $fieldErrors) {
                    $this->view->error .= $field . ': ' . implode(' ', $fieldErrors) . '<br />';
                }
            } else {
                // Receive post values
                $username = $this->getRequest()->getPost('username');
                $password = $this->getRequest()->getPost('password');
                $keepLoggedin = $this->getRequest()->getPost('keep_loggedin');
                
        
                // Using DbTable adapter
                $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'development');
                $dbAdapter = Zend_Db::factory($config->resources->db->adapter, $config->resources->db->params);

                // Preparing zend auth adapter
                $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
                $authAdapter->setTableName('job_user')
                            ->setIdentityColumn('username')
                            ->setCredentialColumn('password')
                            ->setCredentialTreatment('MD5(?) AND status = 1');

                // Set login username and password as auth adapter identity and credential
                $authAdapter->setIdentity($username)
                            ->setCredential($password);

                // Create zend auth instance and authenticate
                $auth = Zend_Auth::getInstance();
                $result = $auth->authenticate($authAdapter);
                
                if ($result->isValid()) {
                    // On authentication success get user info
                    $loggedinUser = $authAdapter->getResultRowObject();
                    
                    // Store login info to session namespace
                    $this->_loginNamespace->user_logged_in = 1;
                    $this->_loginNamespace->session_user_id = $loggedinUser->user_id;
                    $this->_loginNamespace->session_username = $loggedinUser->username;
                    $this->_loginNamespace->session_email_address = $loggedinUser->email;

                    // Update last login time
                    $userMapper = new Application_Model_UserMapper();
                    $userMapper->updateLastLogin($loggedinUser->user_id);
                    
                    if ($keepLoggedin == 'yes') {
                        $loggedInTime = 14*24*3600;
                        $zendAuth = new Zend_Session_Namespace('Zend_Auth');
                        $this->_loginNamespace->setExpirationSeconds($loggedInTime);
                        $zendAuth->setExpirationSeconds($loggedInTime);
                    }
                    
                    // On success add success message to session namespace and redirect to user account
                    $this->_messageNamespace->message = 'You are successfully logged in.';
                    $this->_redirector->gotoSimple('index', 'account');
                } else {
                    // Retrieve form error messages and prepare for view part
                    $errors = $form->getMessages();
                    $errorSt = '';
                    foreach ($errors as $field => $fieldErrors) {
                        $errorSt .= $field . ': ' . implode(' ', $fieldErrors) . '<br />';
                    }
                    $this->_messageNamespace->error = $errorSt;
                    
                    // On authentication failure, redirect to login page
                    $this->_redirector->gotoSimple('login', 'index');
                }
            }
        }
        
    }
    
    /**
     * User logout
     *
     * @return void
     */
    public function logoutAction()
    {
        // Clear zend auth identity
        Zend_Auth::getInstance()->clearIdentity();
        
        // Remove 'login' session namespace
        Zend_Session::namespaceUnset('login');
        
        // Redirect to login page
        $this->_redirector->gotoSimple('login', 'index');
    }
    
    /**
     * Create My_Form_Signup form
     *
     * @param  array $params Array contains primaryCategories and membershipList
     * @return My_Form_Signup
     */
    public function getSignupForm(array $params = array())
    {
        $form = new My_Form_Signup($params);
        return $form;
    }
    
    /**
     * User signup
     *
     * @return void
     * @todo: email work
     */
    public function signupAction()
    {
        // Get project primary categories
        $primaryCategoryMapper = new Application_Model_PrimaryCategoryMapper();
        $primaryCategories = $primaryCategoryMapper->getPrimaryCategories();
        
        // Get membership types
        $membershipMapper = new Application_Model_MembershipMapper();
        $membershipList = $membershipMapper->getPremiumMembershipList();

        // Create signup form, My_Form_Signup
        $params['primaryCategories'] = $primaryCategories;
        $params['membershipList'] = $membershipList;
        $form = $this->getSignupForm($params);
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
                // Change layout to simple one
                $this->_helper->layout->setLayout('layout-simple');
        
                // Prepare user object to store in db
                $user = new Application_Model_User();
                $user->setUsername($this->getRequest()->getPost('username'));
                $user->setFullName($this->getRequest()->getPost('full_name'));
                $user->setProfileImage($this->getRequest()->getPost('profile_image'));
                $user->setEmail($this->getRequest()->getPost('email'));
                $user->setCountry($this->getRequest()->getPost('country'));
                $user->setState($this->getRequest()->getPost('state'));
                $user->setCity($this->getRequest()->getPost('city'));
                $user->setPassword($this->getRequest()->getPost('password'));
                $user->setContactNo($this->getRequest()->getPost('contact_no'));
                $user->setCompany($this->getRequest()->getPost('company'));
                $user->setNricRocNumber($this->getRequest()->getPost('NRIC_ROC_number'));
                $user->setPrimaryCategoryId($this->getRequest()->getPost('primary_category_id'));
                $user->setMembershipId($this->getRequest()->getPost('membership_id'));
                
                // Save new user
                $userMapper = new Application_Model_UserMapper();
                $userId = $userMapper->saveUser($user);
                
                if (!$userId) {
                     // On failure redirect to home page
                     $this->_messageNamespace->error = 'User sign up failed.';
                     $this->_redirector->gotoSimple('index', 'index');
                }
                    
                if ($this->getRequest()->getPost('membership_id') == 0) {
                    // todo: email work
                    // Freemembership success
                    // On success add success message to session and redirect to home page
                    $this->_messageNamespace->message = 'You have signed up successfully.';
                    $this->_redirector->gotoSimple('index', 'index');

                } else {
                    // Paid membership
                    // Redirect to paypalPayment action for payment processing
                    $this->_redirector->gotoRoute(array('userId' => $userId), 'paypalPayment');
                }
                
            }
        }
        
    }
    
    /**
     * Create My_Form_PaypalPayment form
     *
     * @return My_Form_PaypalPayment
     */
    public function getPaypalPaymentForm()
    {
        $form = new My_Form_PaypalPayment();
        return $form;
    }
    
    /**
     * Paypal payment form
     * 
     * Generate paypal payment form and auto submit to paypal for payment processing
     *
     * @return void
     */
    public function paypalPaymentAction()
    {
        // Change layout to simple one
        $this->_helper->layout->setLayout('layout-simple');
        
        $userId = $this->getRequest()->getParam('userId');
        
        // Get user membership
        $userMapper = new Application_Model_UserMapper();
        $userMembership = $userMapper->getUserMembership($userId);
        
        // Get host name
        $host = $this->getRequest()->getHttpHost();

        // Prepare data for paypal payment form to post
        $data['notify_url']        = $this->view->baseUrl($host . '/ipn');
        $data['currency_code']     = Zend_Registry::get('ADMIN_CURRENCY');
        $data['business']          = Zend_Registry::get('BUSINESS_EMAIL');
        $data['payer_id']          = $userMembership->getUserId();
        $data['item_name']         = $userMembership->getUserId() . '__' . $userMembership->getMembership()->getMembership();
        $data['item_id']           = $userMembership->getUserId();
        $data['test_ipn']          = 1;
        $data['return']            = $this->view->baseUrl($host . '/ipn/postpaid/successful.php');
        $data['cancel_return']     = $this->view->baseUrl($host . '/ipn/postpaid/un-successful.php');
        $data['a3']                = Zend_Registry::get('PAYPAL_a3'); 
        $data['payer_email']       = $userMembership->getEmail();
        
        // Create paypal payment form, My_Form_PaypalPayment
        $form = $this->getPaypalPaymentForm();
        $form->populate($data);
        $this->view->form = $form;
    }
    
    /**
     * Payment success page action
     * 
     * After successful payment processing user redirected to this action
     *
     * @return void
     */
    public function premiumSuccessfulAction()
    {
        $this->_messageNamespace->message = 'You have successfully registered as a premium member.';
        $this->_redirector->gotoSimple('index', 'index');
    }
    
    /**
     * Payment failure page action
     * 
     * Failure of paypal payment processing redirects user to this action
     *
     * @return void
     */
    public function premiumUnsuccessfulAction()
    {
        $this->_messageNamespace->error = 'Your registration for premium membership is not fully completed. Please try again!';
        $this->_redirector->gotoSimple('index', 'index');
    }
    
}