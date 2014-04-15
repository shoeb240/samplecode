<?php
/**
 * Application_Model_ProjectMapper class
 * 
 * @category   Application
 * @package    Application_Model
 * @author     Shoeb Abdullah <shoeb240@gmail.com>
 * @copyright  Copyright (c) 2013, Shoeb Abdullah
 * @version    1.0
 */
class Application_Model_ProjectMapper
{
    /**
     * @var Application_Model_DbTable_Project
     */
    private $_dbTable = null;
    
    /**
     * Create Zend_Db_Adapter_Abstract object
     *
     * @return Application_Model_DbTable_Project
     */
    public function getTable()
    {
        if (null == $this->_dbTable) {
            $this->_dbTable = new Application_Model_DbTable_Project();
        }
        
        return $this->_dbTable;
    }
    
    /**
     * Get premium projects
     *
     * @return array $info Array of Application_Model_Project
     */
    public function getProjectsPremium()
    {
        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('p' => 'job_project'), array('p.project_id', 'p.project_title', 
                                                         'p.bid_ending_date', 'p.cost'))
               ->joinLeft(array('u' => 'job_user'), 
                          'u.user_id = p.user_id', 
                          array())
               ->joinLeft(array('pb'=>'job_project_bid'), 
                          'pb.project_id = p.project_id', 
                          array('total_bid' => 'COUNT(pb.project_bid_id)'))
               ->where('u.is_premium = 1
                        AND p.status = 1
                        AND p.project_status = "opened"')
               ->group('p.project_id')
               ->order('RAND()')
               ->limit(4);
        //echo $select . '<br />';
        $rowSets = $this->getTable()->fetchAll($select);
        
        $info = array();
        foreach($rowSets as $k => $row) {
            $project = new Application_Model_Project();
            $project->setProjectId($row->project_id);
            $project->setProjectTitle($row->project_title);
            $project->setBidEndingDate($row->bid_ending_date);
            $project->setCost($row->cost);
            $project->setTotalBid($row->total_bid);
            $info[] = $project;
        }
        
        return $info;
    }
    
    /**
     * Get default projects
     *
     * @param  int   $searchType
     * @param  int   $startLimit
     * @param  int   $limit     
     * @return array $info       Array of Application_Model_Project
     */
    public function getProjectsDefault($searchType = 'latest', $startLimit = 0, $limit = 8)
    {
        if($searchType == 'latest') {    
            $searchType = "p.project_id DESC";
        } else if ($searchType == 'budget') {
            $searchType = "p.cost DESC";
        } else if ($searchType == 'closing-date') {
            $searchType = "p.bid_ending_date DESC";
        } else if ($searchType == 'alphabetical') {
            $searchType = "p.project_title ASC";
        } else {    
            $searchType = "p.project_id DESC";
        }
        
        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('p' => 'job_project'), array('p.project_id', 'p.project_title', 
                                                         'p.bid_ending_date', 'p.cost'))
               ->joinLeft(array('u' => 'job_user'), 
                          'u.user_id = p.user_id', 
                          array())
               ->joinLeft(array('pb'=>'job_project_bid'), 
                          'pb.project_id = p.project_id', 
                          array('total_bid' => 'COUNT(pb.project_bid_id)'))
               ->where('u.is_premium != 1
                        AND p.status = 1
                        AND p.project_status = "opened"')
               ->group('p.project_id')
               ->order($searchType)
               ->limit($limit, $startLimit);
        $rowSets = $this->getTable()->fetchAll($select);
        
        $info = array();
        foreach($rowSets as $k => $row) {
            $project = new Application_Model_Project();
            $project->setProjectId($row->project_id);
            $project->setProjectTitle($row->project_title);
            $project->setBidEndingDate($row->bid_ending_date);
            $project->setCost($row->cost);
            $project->setTotalBid($row->total_bid);
            $info[] = $project;
        }
        
        return $info;
    }
    
    /**
     * Get default project count
     *
     * @return int
     */
    public function getProjectsDefaultCount()
    {
        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('p' => 'job_project'), array('total_rows' => 'COUNT(p.project_id)'))
               ->join(array('u' => 'job_user'), 
                          'u.user_id = p.user_id', 
                          array())
               ->where('u.is_premium != 1
                        AND p.status = 1
                        AND p.project_status = "opened"');
        $row = $this->getTable()->fetchRow($select);
        
        return $row['total_rows'];
    }
    
    /**
     * Get category projects
     *
     * @param  int   $userPrimaryCategoryId
     * @return array $info                  Array of Application_Model_Project
     */
    public function getProjectsByCategory($userPrimaryCategoryId)
    {
        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('p' => 'job_project'), array('p.project_id', 'p.project_title', 
                                                         'p.project_category_id',
                                                         'p.bid_ending_date',
                                                         'p.cost'))
               ->joinLeft(array('pb'=>'job_project_bid'), 
                          'pb.project_id = p.project_id', 
                          array('total_bid' => 'COUNT(pb.project_bid_id)'))
               ->where('p.project_category_id = ' . $userPrimaryCategoryId . '
                        AND p.status = 1
                        AND p.project_status = "opened"')
               ->group('p.project_id')
               ->order('p.created_on DESC')
               ->limit(5);
        $rowSets = $this->getTable()->fetchAll($select);
        
        $info = array();
        foreach($rowSets as $k => $row) {
            $project = new Application_Model_Project();
            $project->setProjectId($row->project_id);
            $project->setProjectTitle($row->project_title);
            $project->setProjectCategoryId($row->project_category_id);
            $project->setBidEndingDate($row->bid_ending_date);
            $project->setCost($row->cost);
            $project->setTotalBid($row->total_bid);
            $info[] = $project;
        }
        
        return $info;
    }
    
    /**
     * Get project details
     *
     * @param  int   $projectId
     * @return array $info      Array of Application_Model_Project
     */
    public function getProjectDetails($pojectId)
    {
        $select = $this->getTable()->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
        $select->setIntegrityCheck(false)
               ->joinLeft(array('prc'=>'job_primary_category'), 
                          'job_project.project_category_id = prc.primary_category_id', 
                          array('prc.category_title'))
               ->where('job_project.project_id = ?', $pojectId)
               ->limit(1);
        $row = $this->getTable()->fetchRow($select);
        
        $project = new Application_Model_Project();
        $project->setProjectId($row->project_id);
        $project->setUserId($row->user_id);
        $project->setProjectTitle($row->project_title);
        $project->setProjectDescription($row->project_description);
        $project->setAdditionalRemarks($row->additional_remarks);
        $project->setMeetUpRequired($row->meet_up_required);
        $project->setMilestonePayments($row->milestone_payments);
        $project->setRequirePortfolio($row->require_portfolio);
        $project->setProjectCategoryId($row->project_category_id);
        $project->setBidEndingDate($row->bid_ending_date);
        $project->setCost($row->cost);
        $primaryCategory = new Application_Model_PrimaryCategory();
        $primaryCategory->setCategoryTitle($row->category_title);
        $project->setPrimaryCategory($primaryCategory);
        
        return $project;
    }
    
    /**
     * Update project status to frozen
     *
     * @param  int $projectId
     * @return int
     */
    public function updateProjectFrozen($projectId)
    {
        $data = array(
            'project_status' => 'frozen'
        );

        $where = $this->getTable()->getAdapter()->quoteInto('project_id = ?', $projectId, 'INTEGER');

        return $this->getTable()->update($data, $where);
    }
    
    /**
     * Update project status to close
     *
     * @param  int $projectId
     * @param  int $bidderUserId
     * @return int
     */
    public function updateProjectClose($projectId, $bidderUserId)
    {
        $data = array(
            'assigned_user_id' => $bidderUserId,
            'project_status' => 'closed'
        );

        $where = $this->getTable()->getAdapter()->quoteInto('project_id = ?', $projectId, 'INTEGER');

        return $this->getTable()->update($data, $where);
    }
    
    /**
     * Update project status to open
     *
     * @param  int $projectId
     * @return int
     */
    public function updateProjectOpen($projectId)
    {
        $data = array(
            'project_status' => 'opened'
        );

        $where = $this->getTable()->getAdapter()->quoteInto('project_id = ?', $projectId, 'INTEGER');

        return $this->getTable()->update($data, $where);
    }
    
    /**
     * Update project archive status to archived
     *
     * @param  int $projectId
     * @return int
     */
    public function updateProjectArchive($projectId)
    {
        $data = array(
            'archive_status' => 1
        );

        $where = $this->getTable()->getAdapter()->quoteInto('project_id = ?', $projectId, 'INTEGER');

        return $this->getTable()->update($data, $where);
    }
    
    /**
     * Update project status to cancel
     *
     * @param  int $projectId
     * @return int
     */
    public function updateProjectCancel($projectId)
    {
        $data = array(
            'project_status' => 'cancel'
        );

        $where = $this->getTable()->getAdapter()->quoteInto('project_id = ?', $projectId, 'INTEGER');

        return $this->getTable()->update($data, $where);
    }
    
    /**
     * Get project and project owner info
     *
     * @param  int   $projectId
     * @return array $info      Array of Application_Model_Project
     */
    public function getProject($projectId)
    {
        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('p' => 'job_project'), array('p.project_title', 'p.cost'))
               ->join(array('u' => 'job_user'), 'p.user_id = u.user_id', array('u.user_id', 'u.username', 'u.email'))
               ->where('p.project_id = ?', $projectId);
        $row = $this->getTable()->fetchRow($select);
        
        $project = new Application_Model_Project();
        $project->setProjectTitle($row->project_title);
        $project->setCost($row->cost);
        $user = new Application_Model_User();
        $user->setUserId($row->user_id);
        $user->setUsername($row->username);
        $user->setEmail($row->email);
        $project->setProjectOwner($user);
        
        return $project;
    }
    
    /**
     * Get projects own
     *
     * @param  int   $userId
     * @return array $info   Array of Application_Model_Project
     */
    public function getProfileProjects($userId)
    {
        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('p' => 'job_project'), array('p.project_id', 'p.project_title', 
                                                         'p.bid_ending_date', 'p.cost'))
               ->join(array('u' => 'job_user'), 
                          'u.user_id = p.user_id', 
                          array())
               ->joinLeft(array('pb'=>'job_project_bid'), 
                          'pb.project_id = p.project_id', 
                          array('total_bid' => 'COUNT(pb.project_bid_id)'))
               ->where('p.user_id = ?', $userId)
               ->where('p.status = ?', 1)
               ->group('p.project_id')
               ->order('p.project_id DESC')
               ->limit(4);
        $rowSets = $this->getTable()->fetchAll($select);
        
        $info = array();
        foreach($rowSets as $k => $row) {
            $project = new Application_Model_Project();
            $project->setProjectId($row->project_id);
            $project->setProjectTitle($row->project_title);
            $project->setBidEndingDate($row->bid_ending_date);
            $project->setCost($row->cost);
            $project->setTotalBid($row->total_bid);
            $info[] = $project;
        }
        
        return $info;
    }
    
    /**
     * Get active projects the user owns
     *
     * @param  int   $userId
     * @param  int   $searchType
     * @param  int   $startLimit
     * @param  int   $limit
     * @return array $info       Array of Application_Model_Project
     */
    public function getActiveProjects($userId, $searchType = 'latest', $startLimit = 0, $limit = 10)
    {
        if($searchType == 'latest') {    
            $searchType = "p.project_id DESC";
        } else if ($searchType == 'budget') {
            $searchType = "p.cost DESC";
        } else if ($searchType == 'closing-date') {
            $searchType = "p.bid_ending_date DESC";
        } else if ($searchType == 'alphabetical') {
            $searchType = "p.project_title ASC";
        } else {    
            $searchType = "p.project_id DESC";
        }
        
        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('p' => 'job_project'), array('p.project_id', 'p.project_title',
                                                         'p.assigned_user_id', 'p.project_status',
                                                         'p.bid_ending_date', 'p.cost'))
               ->join(array('u' => 'job_user'), 
                          'u.user_id = p.user_id', 
                          array())
               ->joinLeft(array('pb'=>'job_project_bid'), 
                          'pb.project_id = p.project_id', 
                          array('total_bid' => 'COUNT(pb.project_bid_id)'))
               ->joinLeft(array('f' => 'job_feedback'), 
                          'f.project_id = p.project_id AND f.owner_user_id = u.user_id AND f.owner_feedback_rate != ""', array('owner_feedback_given' => 'COUNT(f.feedback_id)'))
               ->where('p.status = 1 AND u.user_id = ?', $userId)
               ->where('p.project_status = "opened" or p.project_status = "closed"')
               ->where('p.archive_status != 1')
               ->group('p.project_id')
               ->order($searchType)
               ->limit($limit, $startLimit);
        $rowSets = $this->getTable()->fetchAll($select);
        
        $info = array();
        foreach($rowSets as $k => $row) {
            $project = new Application_Model_Project();
            $project->setProjectId($row->project_id);
            $project->setProjectTitle($row->project_title);
            $project->setAssignedUserId($row->assigned_user_id);
            $project->setProjectStatus($row->project_status);
            $project->setBidEndingDate($row->bid_ending_date);
            $project->setCost($row->cost);
            $project->setTotalBid($row->total_bid);
            $project->setOwnerFeedbackGiven($row->owner_feedback_given);
            $info[] = $project;
        }
        
        return $info;
    }
    
    /**
     * Get active project count the user owns
     *
     * @param  int $userId
     * @return int
     */
    public function getActiveProjectsCount($userId)
    {
        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('p' => 'job_project'), array('total_rows' => 'COUNT(p.project_id)'))
               ->join(array('u' => 'job_user'), 
                          'u.user_id = p.user_id', 
                          array())
               ->where('p.status = 1 AND u.user_id = ?', $userId)
               ->where('p.project_status = "opened" or p.project_status = "closed"')
               ->where('p.archive_status != 1');
        $row = $this->getTable()->fetchRow($select);
        
        return $row['total_rows'];
    }
    
    /**
     * Get the projects the user bidded
     *
     * @param  int   $userId
     * @param  int   $searchType
     * @param  int   $startLimit
     * @param  int   $limit
     * @return array $info       Array of Application_Model_Project
     */
    public function getBiddedProjects($userId, $searchType = 'latest', $startLimit = 0, $limit = 10)
    {
        if($searchType == 'latest') {    
            $searchType = "p.project_id DESC";
        } else if ($searchType == 'budget') {
            $searchType = "p.cost DESC";
        } else if ($searchType == 'closing-date') {
            $searchType = "p.bid_ending_date DESC";
        } else if ($searchType == 'alphabetical') {
            $searchType = "p.project_title ASC";
        } else {    
            $searchType = "p.project_id DESC";
        }
        
        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('p' => 'job_project'), array('p.project_id', 'p.project_title',
                                                         'p.user_id', 'p.assigned_user_id', 
                                                         'p.project_status',
                                                         'p.bid_ending_date', 'p.cost'))
               ->join(array('u' => 'job_user'), 
                          'u.user_id = p.user_id', 
                          array('u.username'))
               ->joinLeft(array('pb'=>'job_project_bid'), 
                          'pb.project_id = p.project_id', 
                          array('pb.accept_decline', 'total_bid' => '(SELECT COUNT(pb2.project_bid_id) 
                                                                      FROM job_project_bid pb2 
                                                                      WHERE pb2.project_id = p.project_id)'))
               ->joinLeft(array('f' => 'job_feedback'), 
                          'f.project_id = p.project_id AND f.bidder_user_id = pb.BIDDER_user_id AND f.bidder_feedback_rate !=""', 
                          array('bidder_feedback_given' => 'COUNT(f.feedback_id)'))
               ->where('p.status = ?', 1)
               ->where('pb.BIDDER_user_id = ?', $userId)
               ->where('archive_status != ?', 1)
               ->group('p.project_id')
               ->order($searchType)
               ->limit($limit, $startLimit);
        $rowSets = $this->getTable()->fetchAll($select);
        
        $info = array();
        foreach($rowSets as $k => $row) {
            $project = new Application_Model_Project();
            $project->setProjectId($row->project_id);
            $project->setProjectTitle($row->project_title);
            $project->setUserId($row->user_id);
            $project->setAssignedUserId($row->assigned_user_id);
            $project->setProjectStatus($row->project_status);
            $project->setBidEndingDate($row->bid_ending_date);
            $project->setCost($row->cost);
            $project->setTotalBid($row->total_bid);
            $project->setBidderFeedbackGiven($row->bidder_feedback_given);
            $project->setCustom($row->accept_decline);
            $info[] = $project;
        }
        
        return $info;
    }
    
    /**
     * Get count of the projects the user bidded
     *
     * @param  int $userId
     * @return int
     */
    public function getBiddedProjectsCount($userId)
    {
        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('p' => 'job_project'), array('p.project_id'))
               ->joinLeft(array('pb'=>'job_project_bid'), 
                          'pb.project_id = p.project_id', 
                          array('pb.project_id'))
               ->where('p.status = ?', 1)
               ->where('pb.BIDDER_user_id = ?', $userId)
               ->where('archive_status != ?', 1)
               ->group('p.project_id');
        $rowSets = $this->getTable()->fetchAll($select);
        
        return count($rowSets);
    }
    
    /**
     * Get archived projcts of the user
     *
     * @param  int   $userId
     * @param  int   $searchType
     * @param  int   $startLimit
     * @param  int   $limit
     * @return array $info       Array of Application_Model_Project
     */
    public function getArchiveProjects($userId, $searchType = 'latest', $startLimit = 0, $limit = 10)
    {
        if($searchType == 'latest') {    
            $searchType = "p.project_id DESC";
        } else if ($searchType == 'budget') {
            $searchType = "p.cost DESC";
        } else if ($searchType == 'closing-date') {
            $searchType = "p.bid_ending_date DESC";
        } else if ($searchType == 'alphabetical') {
            $searchType = "p.project_title ASC";
        } else {    
            $searchType = "p.project_id DESC";
        }
        
        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('p' => 'job_project'), array('p.project_id', 'p.project_title',
                                                         'p.bid_ending_date', 'p.cost'))
               ->joinLeft(array('pb'=>'job_project_bid'), 
                          'pb.project_id = p.project_id', 
                          array('total_bid' => '(SELECT COUNT(pb2.project_bid_id) 
                                                 FROM job_project_bid pb2 
                                                 WHERE pb2.project_id = p.project_id)'))
               ->where('p.user_id = ?', $userId)
               ->where('p.status = ?', 1)
               ->where('p.project_status = ?', 'closed')
               ->where('archive_status = ?', 1)
               ->group('p.project_id')
               ->order($searchType)
               ->limit($limit, $startLimit);
        $rowSets = $this->getTable()->fetchAll($select);
        
        $info = array();
        foreach($rowSets as $k => $row) {
            $project = new Application_Model_Project();
            $project->setProjectId($row->project_id);
            $project->setProjectTitle($row->project_title);
            $project->setBidEndingDate($row->bid_ending_date);
            $project->setCost($row->cost);
            $project->setTotalBid($row->total_bid);
            $info[] = $project;
        }
        
        return $info;
    }
    
    /**
     * Get count of archived projects of the user
     *
     * @param  int $userId
     * @return int
     */
    public function getArchiveProjectsCount($userId)
    {
        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('p' => 'job_project'), array('total_rows' => 'COUNT(p.project_id)'))
               ->where('p.user_id = ?', $userId)
               ->where('p.status = ?', 1)
               ->where('p.project_status = ?', 'closed')
               ->where('archive_status = ?', 1)
               ->group('p.project_id');
        $row = $this->getTable()->fetchRow($select);
        
        return $row['total_rows'];
    }
    
    /**
     * Get archived projects bidded by the user
     *
     * @param  int   $userId
     * @param  int   $searchType
     * @param  int   $startLimit
     * @param  int   $limit
     * @return array $info       Array of Application_Model_Project
     */
    public function getArchiveBiddedProjects($userId, $searchType = 'latest', $startLimit = 0, $limit = 10)
    {
        if($searchType == 'latest') {    
            $searchType = "p.project_id DESC";
        } else if ($searchType == 'budget') {
            $searchType = "p.cost DESC";
        } else if ($searchType == 'closing-date') {
            $searchType = "p.bid_ending_date DESC";
        } else if ($searchType == 'alphabetical') {
            $searchType = "p.project_title ASC";
        } else {    
            $searchType = "p.project_id DESC";
        }
        
        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('p' => 'job_project'), array('p.project_id', 'p.project_title',
                                                         'p.bid_ending_date', 'p.cost'))
               ->joinLeft(array('pb'=>'job_project_bid'), 
                          'pb.project_id = p.project_id', 
                          array('total_bid' => '(SELECT COUNT(pb2.project_bid_id) 
                                                 FROM job_project_bid pb2 
                                                 WHERE pb2.project_id = p.project_id)'))
               ->where('p.assigned_user_id = ?', $userId)
               ->where('p.status = ?', 1)
               ->where('p.project_status = ?', 'closed')
               ->where('archive_status = ?', 1)
               ->group('p.project_id')
               ->order($searchType)
               ->limit($limit, $startLimit);
        $rowSets = $this->getTable()->fetchAll($select);
        
        $info = array();
        foreach($rowSets as $k => $row) {
            $project = new Application_Model_Project();
            $project->setProjectId($row->project_id);
            $project->setProjectTitle($row->project_title);
            $project->setBidEndingDate($row->bid_ending_date);
            $project->setCost($row->cost);
            $project->setTotalBid($row->total_bid);
            $info[] = $project;
        }
        
        return $info;
    }
    
    /**
     * Get count of archived projects bidded by the user
     *
     * @param  int   $userId
     * @return array $info   Array of Application_Model_Message
     */
    public function getArchiveBiddedProjectsCount($userId)
    {
        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('p' => 'job_project'), array('p.project_id', 'p.project_title',
                                                         'p.bid_ending_date', 'p.cost'))
               ->where('p.assigned_user_id = ?', $userId)
               ->where('p.status = ?', 1)
               ->where('p.project_status = ?', 'closed')
               ->where('archive_status = ?', 1);
        $row = $this->getTable()->fetchRow($select);
        
        return $row['total_rows'];
    }
    
    /**
     * Get assigned user of the project
     *
     * @param  int                       $projectId
     * @return Application_Model_Project
     */
    public function getProjectAssignedUser($projectId)
    {
        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('p' => 'job_project'), array('p.project_title', 'p.cost'))
               ->join(array('u' => 'job_user'), 'p.assigned_user_id = u.user_id', array('u.user_id', 'u.username', 'u.email'))
               ->where('p.project_id = ?', $projectId);
        $row = $this->getTable()->fetchRow($select);
        
        $project = new Application_Model_Project();
        $project->setProjectTitle($row->project_title);
        $project->setCost($row->cost);
        $user = new Application_Model_User();
        $user->setUserId($row->user_id);
        $user->setUsername($row->username);
        $user->setEmail($row->email);
        $project->setAssignedUser($user);
        
        return $project;
    }
    
    /**
     * Save project
     *
     * @param  Application_Model_Project $project
     * @return int
     */
    public function saveProject(Application_Model_Project $project)
    {
        $data = array(
            'user_id' => $project->getUserId(),
            'project_category_id' => $project->getProjectCategoryId(),
            'project_title' => $project->getProjectTitle(),
            'project_description' => $project->getProjectDescription(),
            'cost' => $project->getCost(),
            'CurrencyCode' => $project->getCurrencyCode(),
            'additional_remarks' => $project->getAdditionalRemarks(),
            'meet_up_required' => $project->getMeetUpRequired(),
            'milestone_payments' => $project->getMilestonePayments(),
            'require_portfolio' => $project->getRequirePortfolio(),
            'status' => $project->getStatus(),
            'created_on' => $project->getCreatedOn(),
            'bid_ending_date' => $project->getBidEndingDate()
        );
        
        return $this->getTable()->insert($data);
    }
    


}