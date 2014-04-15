<?php
/**
 * Application_Model_ProjectBid class
 * 
 * @category   Application
 * @package    Application_Model
 * @author     Shoeb Abdullah <shoeb240@gmail.com>
 * @copyright  Copyright (c) 2013, Shoeb Abdullah
 * @version    1.0
 */
class Application_Model_ProjectBid
{
    protected $_projectBidId;
    protected $_projectId;
    protected $_bidderUserId;
    protected $_bidAmount;
    protected $_currencyCode;
    protected $_timePeriod;
    protected $_status;
    protected $_createdOn;
    protected $_isAssigned;
    protected $_acceptDecline;
    protected $_bidderUser;
    protected $_project;
    
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
    
    public function setProjectBidId($projectBidId)
    {
        $this->_projectBidId = $projectBidId;
    }
    
    public function getProjectBidId()
    {
        return $this->_projectBidId;
    }
    
    public function setProjectId($projectId)
    {
        $this->_projectId = $projectId;
    }
    
    public function getProjectId()
    {
        return $this->_projectId;
    }
    
    public function setBidderUserId($bidderUserId)
    {
        $this->_bidderUserId = $bidderUserId;
    }
    
    public function getBidderUserId()
    {
        return $this->_bidderUserId;
    }
    
    public function setBidAmount($bidAmount)
    {
        $this->_bidAmount = $bidAmount;
    }
    
    public function getBidAmount()
    {
        return $this->_bidAmount;
    }
    
    public function setCurrencyCode($currencyCode)
    {
        $this->_currencyCode = $currencyCode;
    }
    
    public function getCurrencyCode()
    {
        return $this->_currencyCode;
    }
    
    public function setTimePeriod($timePeriod)
    {
        $this->_timePeriod = $timePeriod;
    }
    
    public function getTimePeriod()
    {
        return $this->_timePeriod;
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

    public function setIsAssigned($isAssigned)
    {
        $this->_isAssigned = $isAssigned;
    }
    
    public function getIsAssigned()
    {
        return $this->_isAssigned;
    }
    
    public function setAcceptDecline($acceptDecline)
    {
        $this->_acceptDecline = $acceptDecline;
    }
    
    public function getAcceptDecline()
    {
        return $this->_acceptDecline;
    }
    
    public function setBidderUser(Application_Model_User $bidderUser)
    {
        $this->_bidderUser = $bidderUser;
    }
    
    public function getBidderUser()
    {
        return $this->_bidderUser;
    }
    
    public function setProject(Application_Model_Project $project)
    {
        $this->_project = $project;
    }
    
    public function getProject()
    {
        return $this->_project;
    }
    
}