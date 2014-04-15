<?php
/**
 * Application_Model_ProjectBidMapper class
 * 
 * @category   Application
 * @package    Application_Model
 * @author     Shoeb Abdullah <shoeb240@gmail.com>
 * @copyright  Copyright (c) 2013, Shoeb Abdullah
 * @version    1.0
 */
class Application_Model_ProjectBidMapper
{
    /**
     * @var Application_Model_DbTable_ProjectBid
     */
    private $_dbTable = null;
    
    /**
     * Create Zend_Db_Adapter_Abstract object
     *
     * @return Application_Model_DbTable_ProjectBid
     */
    public function getTable()
    {
        if (null == $this->_dbTable) {
            $this->_dbTable = new Application_Model_DbTable_ProjectBid();
        }
        
        return $this->_dbTable;
    }
    
    /**
     * Get assigned bidder userId and username in an array indexed by project id
     *
     * @param  array $projectIds
     * @return array $info                  
     */
    public function getAssignedProjectsUsers($projectIds = array())
    {
        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('pb' => 'job_project_bid'), array('pb.project_id', 'pb.BIDDER_user_id'))
               ->join(array('u' => 'job_user'), 
                          'pb.BIDDER_user_id = u.user_id', 
                          array('u.username'))
               ->where('pb.project_id IN ('.implode(",", $projectIds).')')
               ->where('pb.is_assigned = ?', 1);
        $rowSets = $this->getTable()->fetchAll($select);
        
        $info = array();
        foreach($rowSets as $k => $row) {
            $info[$row->project_id]['user_id'] = $row->BIDDER_user_id;
            $info[$row->project_id]['username'] = $row->username;
        }
        
        return $info;
    }
    
    /**
     * Get assign bid of the project
     *
     * @param  int                          $projectId
     * @return Application_Model_ProjectBid $projectBid
     */
    public function getAssignedBid($projectId)
    {
        $select = $this->getTable()->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
        $select->setIntegrityCheck(false)
               ->join(array('u' => 'job_user'), 
                          'job_project_bid.BIDDER_user_id = u.user_id', 
                          array('u.user_id', 'u.username'))
               ->where('job_project_bid.project_id = ?', $projectId)
               ->where('job_project_bid.is_assigned = ?', 1);
        $row = $this->getTable()->fetchRow($select);
        
        $projectBid = new Application_Model_ProjectBid();
        if (!$row) return $projectBid;
        $projectBid->setProjectId($row->project_id);
        $projectBid->setBidderUserId($row->BIDDER_user_id);
        $projectBid->setAcceptDecline($row->accept_decline);
        $user = new Application_Model_User();
        $user->setUsername($row->username);
        $projectBid->setBidderUser($user);
        
        return $projectBid;
    }
    
    /**
     * Get project bids with bidder user info and bidder message
     *
     * @param  int   $projectId
     * @return array $info       Array of Application_Model_ProjectBid
     */
    public function getProjectBids($projectId)
    {
        $select = $this->getTable()->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
        $select->setIntegrityCheck(false)
               ->joinLeft(array('u' => 'job_user'), 
                      'job_project_bid.BIDDER_user_id = u.user_id', 
                      array('u.user_id', 'u.username'))
               ->join(array('p' => 'job_project'), 
                      'p.project_id = job_project_bid.project_id', 
                      array('project_owner_id' => 'p.user_id'))
               ->join(array('m' => 'job_message'), 
                      'm.project_id = job_project_bid.project_id 
                          AND m.SENDER_user_id = job_project_bid.BIDDER_user_id', 
                      array('m.message'))
               ->where('job_project_bid.project_id = ?', $projectId)
               ->where('job_project_bid.status != ?', 2)
               ->where('m.status = ?', 2);
        $rowSets = $this->getTable()->fetchAll($select);
        
        $info = array();
        foreach($rowSets as $k => $row) {
            $projectBid = new Application_Model_ProjectBid();
            $projectBid->setProjectId($row->project_id);
            $projectBid->setBidderUserId($row->BIDDER_user_id);
            $projectBid->setBidAmount($row->bid_amount);
            $projectBid->setTimePeriod($row->time_period);
            $projectBid->setCurrencyCode($row->CurrencyCode);
            $projectBid->setCreatedOn($row->created_on);
            $projectBid->setBidAmount($row->bid_amount);
            $user = new Application_Model_User();
            $user->setUsername($row->username);
            $projectBid->setBidderUser($user);
            $project = new Application_Model_Project();
            $project->setUserId($row->project_owner_id);
            $projectBid->setProject($project);
            // need to add message
            $info[] = $projectBid;
        }
        
        return $info;
    }
    
    /**
     * Get bid count of the user in last one month
     *
     * @param  int $userId
     * @return int
     */
    public function getBidNumberCount($userId)
    {
        $current_date = date('Y-m-d H:i:s', time());
        $month_date = date('Y-m-d H:i:s', time()-30*24*60*60); 
      
        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('pb' => 'job_project_bid'), array('bid_number' => 'COUNT( pb.project_bid_id )'))
               ->join(array('u' => 'job_user'), 
                      'pb.BIDDER_user_id = u.user_id', 
                      array())
               ->where('pb.BIDDER_user_id = ?', $userId)
               ->where("pb.created_on BETWEEN '" . $month_date . "' AND '" . $current_date . "'")
               ->where('u.is_premium != ?', 1)
               ->limit(1);
        $row = $this->getTable()->fetchRow($select);
        
        return $row->bid_number;
    }
    
    /**
     * Assign the project to the bidder
     *
     * @param  int $projectId
     * @param  int $bidderUserId
     * @return int
     */
    public function updateBidAssigned($projectId, $bidderUserId)
    {
        $data = array(
            'is_assigned' => 1
        );

        $where[] = $this->getTable()->getAdapter()->quoteInto('project_id = ?', $projectId, 'INTEGER');
        $where[] = $this->getTable()->getAdapter()->quoteInto('BIDDER_user_id = ?', $bidderUserId, 'INTEGER');

        return $this->getTable()->update($data, $where);
    }
    
    /**
     * Accept the project as bidder
     *
     * @param  int $projectId
     * @param  int $bidderUserId
     * @return int
     */
    public function setBidAcceptDecline($projectId, $bidderUserId)
    {
        $data = array(
            'accept_decline' => 1
        );

        $where[] = $this->getTable()->getAdapter()->quoteInto('project_id = ?', $projectId, 'INTEGER');
        $where[] = $this->getTable()->getAdapter()->quoteInto('BIDDER_user_id = ?', $bidderUserId, 'INTEGER');

        return $this->getTable()->update($data, $where);
    }
    
    /**
     * Decline the project as bidder
     *
     * @param  int $projectId
     * @param  int $bidderUserId
     * @return int
     */
    public function unsetBidAcceptDecline($projectId, $bidderUserId)
    {
        $data = array(
            'is_assigned' => 0,
            'accept_decline' => 0
        );

        $where[] = $this->getTable()->getAdapter()->quoteInto('project_id = ?', $projectId, 'INTEGER');
        $where[] = $this->getTable()->getAdapter()->quoteInto('BIDDER_user_id = ?', $bidderUserId, 'INTEGER');

        $this->getTable()->update($data, $where);
    }
    
    /**
     * Set bid status as deleted
     *
     * @param  int $projectId
     * @param  int $bidderUserId
     * @return int
     */
    public function setBidDeleted($projectId, $bidderUserId)
    {
        $data = array(
            'status' => 2
        );

        $where[] = $this->getTable()->getAdapter()->quoteInto('project_id = ?', $projectId, 'INTEGER');
        $where[] = $this->getTable()->getAdapter()->quoteInto('BIDDER_user_id = ?', $bidderUserId, 'INTEGER');

        $this->getTable()->update($data, $where);
    }
    
    /**
     * Get bidder info with project info and bid amount
     *
     * @param  int $projectId
     * @param  int $userId
     * @return Application_Model_ProjectBid
     */
    public function getProjectUserBid($projectId, $userId)
    {
        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('pb' => 'job_project_bid'), array('pb.bid_amount'))
               ->join(array('u' => 'job_user'), 
                      'pb.BIDDER_user_id = u.user_id', 
                      array('u.username', 'u.email'))
                ->join(array('p' => 'job_project'), 
                      'pb.project_id = p.project_id', 
                      array('p.project_title'))
               ->where('pb.BIDDER_user_id = ?', $userId)
               ->where('pb.project_id = ?', $projectId);
        $row = $this->getTable()->fetchRow($select);
        
        $projectBid = new Application_Model_ProjectBid();
        if (!$row) return $projectBid;
        $projectBid->setBidAmount($row->bid_amount);
        $user = new Application_Model_User();
        $user->setUsername($row->username);
        $user->setEmail($row->email);
        $projectBid->setBidderUser($user);
        $project = new Application_Model_Project();
        $project->setProjectTitle($row->project_title);
        $projectBid->setProject($project);
        
        return $projectBid;
    }
    
    /**
     * Get bid amount of a bid
     *
     * @param  int $projectId
     * @param  int $userId
     * @return int
     */
    public function getProjectUserBidAmount($projectId, $userId)
    {
        $select = $this->getTable()->select();
        $select->where('job_project_bid.BIDDER_user_id = ?', $userId)
               ->where('job_project_bid.project_id = ?', $projectId);
        
        $row = $this->getTable()->fetchRow($select);
        
        if (!$row) return 0;
        return $row->bid_amount;
    }
    
    /**
     * Get current hired members info as a project owner
     *
     * @param  int   $userId
     * @param  int   $searchType
     * @param  int   $startLimit
     * @param  int   $limit
     * @return array $info          Array of Application_Model_ProjectBid
     */
    public function getCurrentHiredMembers($userId, $searchType = 'newest', $startLimit = 0, $limit = 10)
    {
        if ($searchType == 'newest') {
            $searchType = "pb.BIDDER_user_id DESC";
        } else if($searchType == 'rating') {
            $searchType = "balance DESC";
        } else if($searchType == 'alphabetical') {
          $searchType = "u.username ASC";
        } else {
            $searchType = "pb.BIDDER_user_id DESC";
        }

        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('pb' => 'job_project_bid'), array('pb.BIDDER_user_id', 
                                                              'pb.is_assigned',
                                                              'balance' => '(SELECT SUM(cb.balance) 
                                                                             FROM job_credit_balance cb 
                                                                             WHERE cb.user_id = pb.BIDDER_user_id)'))
               ->join(array('p' => 'job_project'), 
                      'p.project_id = pb.project_id',
                      array('project_owner_id' => 'u.user_id', 
                             'p.project_id'))
               ->join(array('u'=>'job_user'), 
                      'u.user_id = pb.BIDDER_user_id', 
                      array('u.username', 'member_since' => 'u.created_on',
                            'user_hired' => '(SELECT COUNT(p2.project_status) 
                                                           FROM job_project p2 
                                                           WHERE u.user_id = p2.user_id 
                                                                 AND p2.project_status = "closed")'))
               ->joinLeft(array('pc' => 'job_primary_category'), 
                          'u.primary_category_id = pc.primary_category_id', 
                          array('pc.category_title'))
               ->where('p.user_id = ?', $userId)
               ->where('pb.is_assigned = ?', 1)
               ->group('pb.BIDDER_user_id')
               ->order($searchType)
               ->limit($limit, $startLimit);
        $rowSets = $this->getTable()->fetchAll($select);
        
        $info = array();
        foreach($rowSets as $k => $row) {
            $projectBid = new Application_Model_ProjectBid();
            $projectBid->setProjectId($row->project_id);
            $projectBid->setBidderUserId($row->is_assigned);
            $projectBid->setIsAssigned($row->BIDDER_user_id);
            $userBidder = new Application_Model_User();
            $userBidder->setUsername($row->username);
            $userBidder->setBalance($row->balance);
            $userBidder->setUserHired($row->user_hired);
            $userBidder->setCreatedOn($row->member_since);
            $userBidder->setRating($this->getUserRating($row->balance));
            $projectBid->setBidderUser($userBidder);
            $project = new Application_Model_Project();
            $project->setProjectId($row->project_id);
            $project->setUserId($row->project_owner_id);
            $primaryCategory = new Application_Model_PrimaryCategory();
            $primaryCategory->setCategoryTitle($row->category_title);
            $project->setPrimaryCategory($primaryCategory);
            $projectBid->setProject($project);
            
            // need to add message
            $info[] = $projectBid;
        }
        
        return $info;
    }
    
    /**
     * Get count of current hired members as a project owner
     *
     * @param  int $userId
     * @return int
     */
    public function getCurrentHiredMembersCount($userId)
    {
        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('pb' => 'job_project_bid'), array('total_rows' => 'COUNT(pb.BIDDER_user_id)'))
               ->join(array('p' => 'job_project'), 'p.project_id = pb.project_id', array())
               ->where('p.user_id = ?', $userId)
               ->where('pb.is_assigned = ?', 1)
               ->group('pb.BIDDER_user_id');
        $rowSets = $this->getTable()->fetchAll($select);
        
        return count($rowSets);
    }
    
    /**
     * Save project bid
     *
     * @param  Application_Model_ProjectBid $projectId
     * @return int
     */
    public function saveProjectBid(Application_Model_ProjectBid $projectBid)
    {
        $data = array(
            'project_id' => $projectBid->getProjectId(),
            'BIDDER_user_id' => $projectBid->getBidderUserId(),
            'bid_amount' => $projectBid->getBidAmount(),
            'time_period' => $projectBid->getTimePeriod(),
            'status' => $projectBid->getStatus(),
            'created_on' => $projectBid->getCreatedOn()
        );
        
        return $this->getTable()->insert($data);
    }
    
    /**
     * Get user rating depending on his balance
     *
     * @param  int $balance
     * @return int $rating
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