<?php
/**
 * Application_Model_UserMapper class
 * 
 * @category   Application
 * @package    Application_Model
 * @author     Shoeb Abdullah <shoeb240@gmail.com>
 * @copyright  Copyright (c) 2013, Shoeb Abdullah
 * @version    1.0
 */
class Application_Model_UserMapper
{
    /**
     * @var Application_Model_DbTable_User
     */
    private $_dbTable = null;
    
    /**
     * Create Zend_Db_Adapter_Abstract object
     *
     * @return Application_Model_DbTable_User
     */
    public function getTable()
    {
        if (null == $this->_dbTable) {
            $this->_dbTable = new Application_Model_DbTable_User();
        }
        
        return $this->_dbTable;
    }
    
    /**
     * Get premium members
     *
     * @param  int   $limit
     * @return array $info  Array of Application_Model_User
     */
    public function getMembersPremium($limit = 6)
    {
        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('u' => 'job_user'), array('u.user_id', 'username', 
                                                      'created_on', 'country', 
                                                      'is_premium', 'profile_image', 
                                                      'balance' => '(SELECT SUM(cb.balance) 
                                                                     FROM job_credit_balance cb 
                                                                     WHERE cb.user_id = u.user_id)'))
               ->joinLeft(array('pc' => 'job_primary_category'), 
                          'u.primary_category_id = pc.primary_category_id', 
                          array('pc.category_title'))
               ->joinLeft(array('p'=>'job_project'), 
                          'u.user_id = p.assigned_user_id AND p.project_status = "closed"', 
                          array('closed_projects' => 'COUNT(p.project_status)'))
               ->where('u.is_premium = ?', 1)
               ->group('u.user_id')
               ->order('RAND()')
               ->limit($limit);
        $rowSets = $this->getTable()->fetchAll($select);
        $info = array();
        foreach($rowSets as $k => $row) {
            $user = new Application_Model_User();
            $user->setUserId($row->user_id);
            $user->setUsername($row->username);
            $user->setCreatedOn($row->created_on);
            $user->setCountry($row->country);
            $user->setIsPremium($row->is_premium);
            $user->setProfileImage($row->profile_image);
            $user->setUserWorked($row->closed_projects);
            $user->setBalance($row->balance);
            $user->setRating($this->getUserRating($row->balance));
            $primaryCategory = new Application_Model_PrimaryCategory();
            $primaryCategory->setCategoryTitle($row->category_title);
            $user->setPrimaryCategory($primaryCategory);
            $info[] = $user;
        }
        
        return $info;
    }
    
    /**
     * Get featured members
     *
     * @param  int   $searchType
     * @param  int   $startLimit
     * @param  int   $limit
     * @return array $info        Array of Application_Model_User
     */
    public function getMembersFeatured($searchType = 'newest', $startLimit = 0, $limit = 4)
    {
        if ($searchType == 'newest') {
          $searchType = "u.user_id DESC";
        } else if ($searchType == 'rating') {
          $searchType = "balance DESC";
        } else if ($searchType == 'alphabetical') {
          $searchType = "u.username ASC";
        } else {
          $searchType = "u.user_id DESC";
        }

        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('u' => 'job_user'), array('u.user_id', 'username', 
                                                      'created_on', 'country', 
                                                      'is_premium', 'profile_image', 
                                                      'balance' => '(SELECT SUM(cb.balance) 
                                                                     FROM job_credit_balance cb 
                                                                     WHERE cb.user_id = u.user_id)'))
               ->joinLeft(array('pc' => 'job_primary_category'), 
                          'u.primary_category_id = pc.primary_category_id', 
                          array('pc.category_title'))
               ->joinLeft(array('p'=>'job_project'), 
                          'u.user_id = p.assigned_user_id AND p.project_status = "closed"', 
                          array('closed_projects' => 'COUNT(p.project_status)'))
               ->where('u.is_premium = ?', 1)
               ->group('u.user_id')
               ->order($searchType)
               ->limit($limit, $startLimit);
        $rowSets = $this->getTable()->fetchAll($select);
        
        $info = array();
        foreach($rowSets as $k => $row) {
            $user = new Application_Model_User();
            $user->setUserId($row->user_id);
            $user->setUsername($row->username);
            $user->setCreatedOn($row->created_on);
            $user->setCountry($row->country);
            $user->setIsPremium($row->is_premium);
            $user->setProfileImage($row->profile_image);
            $user->setUserWorked($row->closed_projects);
            $user->setBalance($row->balance);
            $user->setRating($this->getUserRating($row->balance));
            $primaryCategory = new Application_Model_PrimaryCategory();
            $primaryCategory->setCategoryTitle($row->category_title);
            $user->setPrimaryCategory($primaryCategory);
            $info[] = $user;
        }
        
        return $info;
    }
    
    /**
     * Get featured members count
     *
     * @return int
     */
    public function getMembersFeaturedCount()
    {
        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('u' => 'job_user'), array('total_rows' => 'COUNT(u.user_id)'))
               ->where('u.is_premium = ?', 1);
        $row = $this->getTable()->fetchRow($select);
        
        return $row['total_rows'];
    }    
    
    /**
     * Get default members
     *
     * @param  int   $searchType
     * @param  int   $startLimit
     * @param  int   $limit
     * @return array $info       Array of Application_Model_User
     */
    public function getMembersDefault($searchType = 'newest', $startLimit = 0, $limit = 4)
    {
        if ($searchType == 'newest') {
            $searchType = "u.user_id DESC";
        } else if ($searchType == 'rating') {
            $searchType = "balance DESC";  
        } else if ($searchType == 'alphabetical') {
            $searchType = "u.username ASC";
        } else {
            $searchType = "u.user_id DESC";
        }

        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('u' => 'job_user'), array('u.user_id', 'u.username', 'u.created_on', 
                                                      'u.country', 'u.is_premium',
                                                      'u.profile_image', 
                                                      'balance' => '(SELECT SUM(cb.balance) 
                                                                     FROM job_credit_balance cb 
                                                                     WHERE cb.user_id = u.user_id)'))
               ->joinLeft(array('pc' => 'job_primary_category'), 
                          'u.primary_category_id = pc.primary_category_id', 
                          array('pc.category_title'))
               ->joinLeft(array('p'=>'job_project'), 
                          'u.user_id = p.assigned_user_id AND p.project_status = "closed"', 
                          array('closed_projects' => 'COUNT(p.project_status)'))
               ->where('u.is_premium != ?', 1)
               ->group('u.user_id')
               ->order($searchType)
               ->limit($limit, $startLimit);
        $rowSets = $this->getTable()->fetchAll($select);

        $info = array();
        foreach($rowSets as $k => $row) {
            $user = new Application_Model_User();
            $user->setUserId($row->user_id);
            $user->setUsername($row->username);
            $user->setCreatedOn($row->created_on);
            $user->setCountry($row->country);
            $user->setIsPremium($row->is_premium);
            $user->setProfileImage($row->profile_image);
            $user->setUserWorked($row->closed_projects);
            $user->setBalance($row->balance);
            $user->setRating($this->getUserRating($row->balance));
            $primaryCategory = new Application_Model_PrimaryCategory();
            $primaryCategory->setCategoryTitle($row->category_title);
            $user->setPrimaryCategory($primaryCategory);
            $info[] = $user;
        }
        
        return $info;
    }
    
    /**
     * Get default members count
     *
     * @return int
     */
    public function getMembersDefaultCount()
    {
        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('u' => 'job_user'), array('total_rows' => 'COUNT(u.user_id)'))
               ->where('u.is_premium != ?', 1);
        $row = $this->getTable()->fetchRow($select);
        
        return $row['total_rows'];
    }
    
    /**
     * Get members by category
     *
     * @param  int   $categoryId
     * @param  int   $searchType
     * @param  int   $startLimit
     * @param  int   $limit
     * @return array $info       Array of Application_Model_User
     */
    public function getMembersCategoryAll($categoryId, $searchType = 'newest', $startLimit = 0, $limit = 4)
    {
        if ($searchType == 'newest') {
            $searchType = "u.user_id DESC";
        } else if ($searchType == 'rating') {
            $searchType = "balance DESC";  
        } else if ($searchType == 'alphabetical') {
            $searchType = "u.username ASC";
        } else {
          $searchType = "u.user_id DESC";
        }
       
        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('u' => 'job_user'), array('u.user_id', 'u.username', 'u.created_on', 
                                                      'u.country', 'u.is_premium',
                                                      'u.profile_image', 
                                                      'balance' => '(SELECT SUM(cb.balance) 
                                                                     FROM job_credit_balance cb 
                                                                     WHERE cb.user_id = u.user_id)'))
               ->joinLeft(array('pc' => 'job_primary_category'), 
                          'u.primary_category_id = pc.primary_category_id', 
                          array('pc.category_title'))
               ->joinLeft(array('p'=>'job_project'), 
                          'u.user_id = p.assigned_user_id AND p.project_status = "closed"', 
                          array('closed_projects' => 'COUNT(p.project_status)'));
        if ($categoryId != 'all') {
            $select->where('u.primary_category_id = ?', $categoryId);
        }
        $select->group('u.user_id')
               ->order($searchType)
               ->limit($limit, $startLimit);
        $rowSets = $this->getTable()->fetchAll($select);

        $info = array();
        foreach($rowSets as $k => $row) {
            $user = new Application_Model_User();
            $user->setUserId($row->user_id);
            $user->setUsername($row->username);
            $user->setCreatedOn($row->created_on);
            $user->setCountry($row->country);
            $user->setIsPremium($row->is_premium);
            $user->setProfileImage($row->profile_image);
            $user->setUserWorked($row->closed_projects);
            $user->setBalance($row->balance);
            $user->setRating($this->getUserRating($row->balance));
            $primaryCategory = new Application_Model_PrimaryCategory();
            $primaryCategory->setCategoryTitle($row->category_title);
            $user->setPrimaryCategory($primaryCategory);
            $info[] = $user;
        }
        
        return $info;
    }
    
    /**
     * Get members who have completed projects to invite
     *
     * @param  int   $projectId
     * @return array $info      Array of Application_Model_User
     */
    public function getMembersToInvite($projectId)
    {
        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('u' => 'job_user'), array('u.user_id', 'u.username', 'u.created_on', 
                                                      'u.country', 'u.is_premium',
                                                      'u.profile_image', 
                                                      'balance' => '(SELECT SUM(cb.balance) 
                                                                     FROM job_credit_balance cb 
                                                                     WHERE cb.user_id = u.user_id)'))
               ->joinLeft(array('pc' => 'job_primary_category'), 
                          'u.primary_category_id = pc.primary_category_id', 
                          array('pc.category_title'))
               ->joinLeft(array('p'=>'job_project'), 
                          'u.user_id = p.assigned_user_id AND p.project_status = "closed"', 
                          array('closed_projects' => 'COUNT(p.project_status)'))
               ->joinLeft(array('i'=>'job_invited'), 
                          'i.user_id = u.user_id AND i.project_id = ' . $projectId, 
                          array('i.invited_id'))
               ->group('u.user_id')
               ->order('RAND()')
               ->limit(9, 0);
        $rowSets = $this->getTable()->fetchAll($select);

        $info = array();
        foreach($rowSets as $k => $row) {
            $user = new Application_Model_User();
            $user->setUserId($row->user_id);
            $user->setUsername($row->username);
            $user->setCreatedOn($row->created_on);
            $user->setCountry($row->country);
            $user->setIsPremium($row->is_premium);
            $user->setProfileImage($row->profile_image);
            $user->setUserWorked($row->closed_projects);
            $user->setBalance($row->balance);
            $user->setRating($this->getUserRating($row->balance));
            $user->setCustom($row->invited_id);
            $primaryCategory = new Application_Model_PrimaryCategory();
            $primaryCategory->setCategoryTitle($row->category_title);
            $user->setPrimaryCategory($primaryCategory);
            $info[] = $user;
        }
        
        return $info;
    }
    
    /**
     * Get primary category of the user
     *
     * @param  int $userId
     * @return int
     */
    public function getPrimaryCategoryByUser($userId)
    {
        $select = $this->getTable()->select();
        $select->from(array('u' => 'job_user'), array('primary_category_id'))
               ->where('u.user_id = ?', $userId);
        $row = $this->getTable()->fetchRow($select);
        
        return $row['primary_category_id'];
    }
    
    /**
     * Get userId by username
     *
     * @param  int $username
     * @return int 
     */
    public function getUserId($username)
    {
        $select = $this->getTable()->select();
        $select->from('job_user', array('user_id'))
               ->where('username = ?', $username);
        
        $row = $this->getTable()->fetchRow($select);
        
        return $row->user_id;
    }
    
    /**
     * Get username by userId
     *
     * @param  int    $userId
     * @return string
     */
    public function getUsername($userId)
    {
        $select = $this->getTable()->select();
        $select->from('job_user', array('username'))
               ->where('user_id = ?', $userId);
        
        $row = $this->getTable()->fetchRow($select);
        
        return $row->username;
    }
    
    /**
     * Get user info
     *
     * @param  int   $userId
     * @return array $info   Array of Application_Model_User
     */
    public function getUserInfo($userId)
    {
        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('u' => 'job_user'), array('u.user_id', 'username',
                                                      'u.full_name', 'u.email', 
                                                      'u.contact_no', 'u.company', 
                                                      'u.NRIC_ROC_number', 'u.created_on', 
                                                      'u.last_login', 'u.country', 
                                                      'u.is_premium', 'u.profile_image', 
                                                      'u.cover_image',
                                                      'balance' => '(SELECT SUM(cb.balance) 
                                                                     FROM job_credit_balance cb 
                                                                     WHERE cb.user_id = u.user_id)',
                                                      'earned' => '(SELECT SUM(cb.balance) 
                                                                     FROM job_credit_balance cb 
                                                                     WHERE cb.user_id = u.user_id 
                                                                           AND cb.type = "earned")',
                                                      'spent' => '(SELECT SUM(cb.balance) 
                                                                     FROM job_credit_balance cb 
                                                                     WHERE cb.user_id = u.user_id AND cb.type = "spend")',
                                                      'user_worked' => '(SELECT COUNT(jp.user_id) 
                                                                            FROM job_project jp 
                                                                            WHERE jp.assigned_user_id = ' . $userId . ' 
                                                                                  AND jp.archive_status = 1)',
                                                      'user_hired' => '(SELECT COUNT(jp.user_id) 
                                                                            FROM job_project jp 
                                                                            WHERE jp.user_id = ' . $userId. ' 
                                                                                  AND assigned_user_id != 0)'))
               ->joinLeft(array('pc' => 'job_primary_category'), 
                          'u.primary_category_id = pc.primary_category_id', 
                          array('pc.category_title'))
               ->joinLeft(array('p'=>'job_project'), 
                          'u.user_id = p.assigned_user_id AND p.project_status != "closed"', 
                          array())
               ->where('u.user_id = ?', $userId)
               ->group('u.user_id');
        $row = $this->getTable()->fetchRow($select);

        $user = new Application_Model_User();
        $user->setUserId($row->user_id);
        $user->setUsername($row->username);
        $user->setFullName($row->full_name);
        $user->setEmail($row->email);
        $user->setContactNo($row->contact_no);
        $user->setCompany($row->company);
        $user->setNricRocNumber($row->NRIC_ROC_number);
        $user->setCreatedOn($row->created_on);
        $user->setLastLogin($row->last_login);
        $user->setCountry($row->country);
        $user->setIsPremium($row->is_premium);
        $user->setProfileImage($row->profile_image);
        $user->setCoverImage($row->cover_image);
        $user->setUserWorked($row->user_worked);
        $user->setUserHired($row->user_hired);
        $user->setBalance($row->balance);
        $user->setBalance($row->earned);
        $user->setBalance($row->spent);
        $user->setRating($this->getUserRating($row->balance));
        $primaryCategory = new Application_Model_PrimaryCategory();
        $primaryCategory->setCategoryTitle($row->category_title);
        $user->setPrimaryCategory($primaryCategory);

        return $user;
    }
    
    /**
     * Save user
     *
     * @param  Application_Model_User $user
     * @return int
     */
    public function saveUser(Application_Model_User $user)
    {
        $password = $this->getTable()->getAdapter()->quoteInto('MD5(?)', $user->getPassword());
        $data = array(
            'username' => $user->getUsername(),
            'full_name' => $user->getFullName(),
            'profile_image' => $user->getProfileImage(),
            'email' => $user->getEmail(),
            'country' => $user->getCountry(),
            'state' => $user->getState(),
            'city' => $user->getCity(),
            'password' => new Zend_Db_Expr($password),
            'contact_no' => $user->getContactNo(),
            'company' => $user->getCompany(),
            'NRIC_ROC_number' => $user->getNricRocNumber(),
            'primary_category_id' => $user->getPrimaryCategoryId(),
            'membership_id' => $user->getMembershipId()
        );
        
        return $this->getTable()->insert($data);
    }
    
    /**
     * Get bookmarked mebers by the user
     *
     * @param  int   $userId
     * @param  int   $searchType
     * @param  int   $startLimit
     * @param  int   $limit
     * @return array $info       Array of Application_Model_User
     */
    public function getBookmarkedMembers($userId, $searchType = 'newest', $startLimit = 0, $limit = 4)
    {
        if ($searchType == 'newest') {
          $searchType = "u.user_id DESC";
        } else if ($searchType == 'rating') {
          $searchType = "balance DESC";
        } else if ($searchType == 'alphabetical') {
          $searchType = "u.username ASC";
        } else {
          $searchType = "u.user_id DESC";
        }

        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('u' => 'job_user'), array('u.user_id', 'u.username', 'u.profile_image',
                                        'u.created_on', 'u.country', 'u.profile_image',
                                        'balance' => '(SELECT SUM(cb.balance) 
                                                       FROM job_credit_balance cb 
                                                       WHERE cb.user_id = u.user_id)'))
               ->joinLeft(array('pc' => 'job_primary_category'), 
                          'u.primary_category_id = pc.primary_category_id', 
                          array('pc.category_title'))
               ->joinLeft(array('p'=>'job_project'), 
                          'u.user_id = p.user_id AND p.project_status = "closed"', 
                          array('user_hired' => 'COUNT(p.project_status)'))
               ->joinRight(array('b' => 'job_bookmark'), 'u.user_id = b.selected_id', array('count' => 'COUNT(*)'))
               ->where('b.user_id = ?', $userId)
               ->group('u.user_id')
               ->order($searchType)
               ->limit($limit, $startLimit);
        $rowSets = $this->getTable()->fetchAll($select);
        
        $info = array();
        foreach($rowSets as $k => $row) {
            $user = new Application_Model_User();
            $user->setUserId($row->user_id);
            $user->setUsername($row->username);
            $user->setCreatedOn($row->created_on);
            $user->setCountry($row->country);
            $user->setProfileImage($row->profile_image);
            $user->setBalance($row->balance);
            $user->setUserHired($row->user_hired);
            $user->setRating($this->getUserRating($row->balance));
            $primaryCategory = new Application_Model_PrimaryCategory();
            $primaryCategory->setCategoryTitle($row->category_title);
            $user->setPrimaryCategory($primaryCategory);
            $info[] = $user;
        }
        
        return $info;
    }
    
    /**
     * Get searched members
     *
     * @param  int   $username
     * @return array $info      Array of Application_Model_User
     */
    public function getSearchedMembers($username)
    {
        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('u' => 'job_user'), array('u.user_id', 'u.username', 'u.created_on', 
                                                      'u.country', 'u.is_premium',
                                                      'u.profile_image', 
                                                      'balance' => '(SELECT SUM(cb.balance) 
                                                                     FROM job_credit_balance cb 
                                                                     WHERE cb.user_id = u.user_id)'))
               ->join(array('p'=>'job_project'), 
                            "u.user_id = p.assigned_user_id AND (p.project_status = 'closed' OR p.project_status = 'opened')", 
                            array('closed_projects' => "SUM(CASE WHEN project_status LIKE '%closed%' THEN 1 ELSE 0 END)",
                                  'opened_projects' => "SUM(CASE WHEN project_status LIKE '%opened%' THEN 1 ELSE 0 END)"))
               ->joinLeft(array('pc' => 'job_primary_category'), 
                          'u.primary_category_id = pc.primary_category_id', 
                          array('pc.category_title'))
               ->where("u.username LIKE '%{$username}%'")
               ->where('u.status = ?', 1)
               ->group('u.user_id')
               ->order('u.username');
        $rowSets = $this->getTable()->fetchAll($select);

        $info = array();
        foreach($rowSets as $k => $row) {
            $user = new Application_Model_User();
            $user->setUserId($row->user_id);
            $user->setUsername($row->username);
            $user->setCreatedOn($row->created_on);
            $user->setCountry($row->country);
            $user->setIsPremium($row->is_premium);
            $user->setProfileImage($row->profile_image);
            $user->setOpenProjects($row->opened_projects);
            $user->setUserHired($row->closed_projects);
            $user->setBalance($row->balance);
            $user->setRating($this->getUserRating($row->balance));
            $primaryCategory = new Application_Model_PrimaryCategory();
            $primaryCategory->setCategoryTitle($row->category_title);
            $user->setPrimaryCategory($primaryCategory);
            $info[] = $user;
        }
        
        return $info;
    }
    
    /**
     * Get searched creative members who have completed projects
     *
     * @param  int   $username
     * @return array $info      Array of Application_Model_User
     */
    public function getSearchedCreatives($username)
    {
        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('u' => 'job_user'), array('u.user_id', 'u.username', 'u.created_on', 
                                                      'u.country', 'u.profile_image', 
                                                      'balance' => '(SELECT SUM(cb.balance) 
                                                                     FROM job_credit_balance cb 
                                                                     WHERE cb.user_id = u.user_id)'))
               ->join(array('p'=>'job_project'), 
                      "u.user_id = p.assigned_user_id AND p.project_status = 'closed'", 
                      array('closed_projects' => 'COUNT(p.project_status)'))
               ->joinLeft(array('pc' => 'job_primary_category'), 
                          'u.primary_category_id = pc.primary_category_id', 
                          array('pc.category_title'))

               ->where("u.username LIKE '%{$username}%'")
               ->where('u.status = ?', 1)
               ->group('u.user_id')
               ->order('u.username');
        $rowSets = $this->getTable()->fetchAll($select);

        $info = array();
        foreach($rowSets as $k => $row) {
            $user = new Application_Model_User();
            $user->setUserId($row->user_id);
            $user->setUsername($row->username);
            $user->setCreatedOn($row->created_on);
            $user->setCountry($row->country);
            $user->setProfileImage($row->profile_image);
            $user->setUserWorked($row->closed_projects);
            $user->setBalance($row->balance);
            $user->setRating($this->getUserRating($row->balance));
            $primaryCategory = new Application_Model_PrimaryCategory();
            $primaryCategory->setCategoryTitle($row->category_title);
            $user->setPrimaryCategory($primaryCategory);
            $info[] = $user;
        }
        
        return $info;
    }
    
    /**
     * Update last login time
     *
     * @param  int   $userId
     * @return int
     */
    public function updateLastLogin($userId)
    {
        $data = array(
            'last_login' => date('Y-m-d, H:i:s',time())
        );

        $where = $this->getTable()->getAdapter()->quoteInto('user_id = ?', $userId, 'INTEGER');

        return $this->getTable()->update($data, $where);
        
    }
    
    /**
     * Get membership of the user
     *
     * @param  int   $userId
     * @return array $info      Array of Application_Model_User
     */
    public function getUserMembership($userId)
    {
        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('u' => 'job_user'), array('u.user_id', 'u.username', 'u.email', 'u.membership_id'))
               ->join(array('m' => 'job_membership'), 
                            'm.membership_id = u.membership_id',
                            array('m.membership', 'm.membership_cost'))
               ->where('u.user_id = ?', $userId);
        $row = $this->getTable()->fetchRow($select);

        $user = new Application_Model_User();
        $user->setUserId($row->user_id);
        $user->setUsername($row->username);
        $user->setEmail($row->email);
        $user->setCreatedOn($row->membership_id);
        $user->setMembershipId($row->membership);
        $membership = new Application_Model_Membership();
        $membership->setMembership($row->membership);
        $membership->setMembershipCost($row->membership_cost);
        $user->setMembership($membership);
        
        return $user;
    }
    
    /**
     * Cancel user subscription
     *
     * @param  int $subscrId
     * @return int
     */
    public function cancelSubscription($subscrId)
    {
        $data = array(
            'subscr_id' => '',
            'subscription_start_date' => '',
            'is_premium' => 0
        );

        $where = $this->getTable()->getAdapter()->quoteInto('subscr_id = ?', $subscrId, 'INTEGER');

        return $this->getTable()->update($data, $where);
    }
    
    /**
     * Update user subscription
     *
     * @param  int $userId
     * @param  int $subscrId
     * @return int 
     */
    public function updateSubscription($userId, $subscrId)
    {
        $select = $this->getTable()->select();
        $select->where('user_id = ?', $userId, 'INTEGER');
        $row = $this->getTable()->fetchRow($select);
        
        if ($row && $row->is_premium == 0) {
            $row->is_premium = 1;
            $row->subscr_id = $subscrId;
            $row->subscription_start_date = date('Y-m-d H:i:s',time());

            return $row->save();
        }
        
        return 0;
    }
    
    /**
     * Update user info
     *
     * @param  Application_Model_User $user
     * @return int
     */
    public function updateUser(Application_Model_User $user)
    {
        $data = array(
            'full_name' => $user->getFullName(),
            'profile_image' => $user->getProfileImage(),
            'email' => $user->getEmail(),
            'contact_no' => $user->getContactNo(),
            'company' => $user->getCompany(),
            'NRIC_ROC_number' => $user->getNricRocNumber()
        );
        
        $where = $this->getTable()->getAdapter()->quoteInto('user_id = ?', $user->getUserId(), 'INTEGER');
        
        return $this->getTable()->update($data, $where);
    }
    
    /**
     * Get user rating by user balance
     *
     * @param  int $balance
     * @return int
     */
    public function getUserRating($balance = 0)
    {
        if($balance <= 1000){
            $rating = 1;
        } else if($balance <= 2000) {
            $rating = 2;
        } else if($balance <= 4000) {
            $rating = 3;
        } else if($balance <= 6000) {
            $rating = 4;
        } else if($balance <= 8000) {
            $rating = 5;
        } else if($balance <= 10000) {
            $rating = 6;
        } else if($balance <= 14000) {
            $rating = 7;
        } else if($balance <= 18000) {
            $rating = 8;
        } else if($balance <= 22000) {
            $rating = 9;
        } else if($balance <= 26000) {
            $rating = 10;
        } else if($balance <= 30000) {
            $rating = 11;
        } else if($balance <= 34000) {
            $rating = 12;
        } else if($balance <= 40000) {
            $rating = 13;
        } else if($balance <= 46000) {
            $rating = 14;
        } else if($balance <= 52000) {
            $rating = 15;
        } else if($balance <= 58000) {
            $rating = 16;
        } else if($balance <= 64000) {
            $rating = 17;
        } else if($balance <= 70000) {
            $rating = 18;
        } else if($balance <= 78000) {
            $rating = 19;
        } else if($balance <= 86000) {
            $rating = 20;
        } else if($balance <= 94000) {
            $rating = 21;
        } else if($balance <= 102000) {
            $rating = 22;
        } else if($balance <= 110000) {
            $rating = 23;
        } else if($balance <= 118000) {
            $rating = 24;
        } else if($balance <= 134000) {
            $rating = 25;
        } else if($balance <= 144000) {
            $rating = 26;
        } else if($balance <= 154000) {
            $rating = 27;
        } else if($balance <= 164000) {
            $rating = 28;
        } else if($balance <= 174000) {
            $rating = 29;
        } else if($balance > 174000) {
            $rating = 30;
        }
        return $rating;

    }
    
    
}