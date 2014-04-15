<?php
/**
 * Application_Model_Project class
 * 
 * @category   Application
 * @package    Application_Model
 * @author     Shoeb Abdullah <shoeb240@gmail.com>
 * @copyright  Copyright (c) 2013, Shoeb Abdullah
 * @version    1.0
 */
class Application_Model_Project
{
    protected $_projectId;
    protected $_userId;
    protected $_assignedUserId;
    protected $_projectCategoryId;
    protected $_projectTitle;
    protected $_projectDescription;
    protected $_cost;
    protected $_currencyCode;
    protected $_additionalRemarks;
    protected $_meetUpRequired;
    protected $_milestonePayments;
    protected $_requirePortfolio;
    protected $_status;
    protected $_createdOn;
    protected $_bidEndingDate;
    protected $_projectStatus;
    protected $_archiveStatus;
    protected $_projectOwner;
    protected $_assignedUser;
    protected $_primaryCategory;
    protected $_totalBid;
    protected $_ownerFeedbackGiven;
    protected $_bidderFeedbackGiven;
    protected $_custom;
    
    
    public function __construct($options = null)
    {
        if (is_array($options)) $this->setOptions($options);
    }
    
    public function setOptions($options)
    {
        $methods = get_class_methods($this);
        foreach($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }
    
    public function setProjectId($projectId)
    {
        $this->_projectId = $projectId;
    }
    
    public function getProjectId()
    {
        return $this->_projectId;
    }
    
    public function setUserId($userId)
    {
        $this->_userId = $userId;
    }
    
    public function getUserId()
    {
        return $this->_userId;
    }
    
    public function setAssignedUserId($assignedUserId)
    {
        $this->_assignedUserId = $assignedUserId;
    }
    
    public function getAssignedUserId()
    {
        return $this->_assignedUserId;
    }
    
    public function setProjectCategoryId($projectCategoryId)
    {
        $this->_projectCategoryId = $projectCategoryId;
    }
    
    public function getProjectCategoryId()
    {
        return $this->_projectCategoryId;
    }
    
    public function setProjectTitle($projectTitle)
    {
        $this->_projectTitle = $projectTitle;
    }
    
    public function getProjectTitle()
    {
        return $this->_projectTitle;
    }
    
    public function setProjectDescription($projectDescription)
    {
        $this->_projectDescription = $projectDescription;
    }
    
    public function getProjectDescription()
    {
        return $this->_projectDescription;
    }
    
    public function setCost($cost)
    {
        $this->_cost = $cost;
    }
    
    public function getCost()
    {
        return $this->_cost;
    }
    
    public function setCurrencyCode($currencyCode)
    {
        $this->_currencyCode = $currencyCode;
    }
    
    public function getCurrencyCode()
    {
        return $this->_currencyCode;
    }
    
    public function setAdditionalRemarks($additionalRemarks)
    {
        $this->_additionalRemarks = $additionalRemarks;
    }
    
    public function getAdditionalRemarks()
    {
        return $this->_additionalRemarks;
    }
    
    public function setMeetUpRequired($meetUpRequired)
    {
        $this->_meetUpRequired = $meetUpRequired;
    }
    
    public function getMeetUpRequired()
    {
        return $this->_meetUpRequired;
    }
    
    public function setMilestonePayments($milestonePayments)
    {
        $this->_milestonePayments = $milestonePayments;
    }
    
    public function getMilestonePayments()
    {
        return $this->_milestonePayments;
    }
    
    public function setRequirePortfolio($requirePortfolio)
    {
        $this->_requirePortfolio = $requirePortfolio;
    }
    
    public function getRequirePortfolio()
    {
        return $this->_requirePortfolio;
    }
    
    public function setStatus($status)
    {
        $this->_status = $status;
    }
    
    public function getStatus()
    {
        return $this->_status;
    }
    
    public function setCreatedOn($createdOn)
    {
        $this->_createdOn = $createdOn;
    }
    
    public function getCreatedOn()
    {
        return $this->_createdOn;
    }
    
    public function setBidEndingDate($bidEndingDate)
    {
        $this->_bidEndingDate = $bidEndingDate;
    }
    
    public function getBidEndingDate()
    {
        return $this->_bidEndingDate;
    }
    
    public function setProjectStatus($projectStatus)
    {
        $this->_projectStatus = $projectStatus;
    }
    
    public function getProjectStatus()
    {
        return $this->_projectStatus;
    }
    
    public function setArchiveStatus($archiveStatus)
    {
        $this->_archiveStatus = $archiveStatus;
    }
    
    public function getArchiveStatus()
    {
        return $this->_archiveStatus;
    }
    
    public function setProjectOwner(Application_Model_User $projectOwner)
    {
        $this->_projectOwner = $projectOwner;
    }
    
    public function getProjectOwner()
    {
        return $this->_projectOwner;
    }
    
    public function setAssignedUser(Application_Model_User $assignedUser)
    {
        $this->_assignedUser = $assignedUser;
    }
    
    public function getAssignedUser()
    {
        return $this->_assignedUser;
    }

    public function setPrimaryCategory(Application_Model_PrimaryCategory $primaryCategory)
    {
        $this->_primaryCategory = $primaryCategory;
    }
    
    public function getPrimaryCategory()
    {
        return $this->_primaryCategory;
    }
    
    public function setTotalBid($totalBid)
    {
        $this->_totalBid = $totalBid;
    }
    
    public function getTotalBid()
    {
        return $this->_totalBid;
    }
    
    public function setOwnerFeedbackGiven($ownerFeedbackGiven)
    {
        $this->_ownerFeedbackGiven = $ownerFeedbackGiven;
    }
    
    public function getOwnerFeedbackGiven()
    {
        return $this->_ownerFeedbackGiven;
    }
    
    public function setBidderFeedbackGiven($bidderFeedbackGiven)
    {
        $this->_bidderFeedbackGiven = $bidderFeedbackGiven;
    }
    
    public function getBidderFeedbackGiven()
    {
        return $this->_bidderFeedbackGiven;
    }
    
    public function setCustom($custom)
    {
        $this->_custom = $custom;
    }
    
    public function getCustom()
    {
        return $this->_custom;
    }

}