<?php
/**
 * Application_Model_User class
 * 
 * @category   Application
 * @package    Application_Model
 * @author     Shoeb Abdullah <shoeb240@gmail.com>
 * @copyright  Copyright (c) 2013, Shoeb Abdullah
 * @version    1.0
 */
class Application_Model_User
{
    protected $_username;
    protected $_fullName;
    protected $_profileImage;
    protected $_coverImage;
    protected $_email;
    protected $_country;
    protected $_state;
    protected $_city;
    protected $_contactNo;
    protected $_primaryCategoryId;
    protected $_company;
    protected $_nricRocNumber;
    protected $_membershipId;
    protected $_password;
    protected $_lastLogin;
    protected $_createdOn;
    protected $_status;
    protected $_activationCode;
    protected $_forgetPassword;
    protected $_type;
    protected $_isFeatured;
    protected $_isPremium;
    protected $_subscriptionStartDate;
    protected $_subscrId;
    protected $_userFbId;
    protected $_twitterUid;
    protected $_twitterUsername;
    protected $_twitterScreenName;
    protected $_primaryCategory;
    protected $_userWorked;
    protected $_userHired;
    protected $_balance;
    protected $_earned;
    protected $_spent;
    protected $_custom;
    protected $_membership;
    protected $_rating;
    
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
    
    public function setUserId($userId)
    {
        $this->_userId = $userId;
    }
    
    public function getUserId()
    {
        return $this->_userId;
    }
    
    public function setUsername($username)
    {
        $this->_username = $username;
    }
    
    public function getUsername()
    {
        return $this->_username;
    }
    
    public function setFullName($fullName)
    {
        $this->_fullName = $fullName;
    }
    
    public function getFullName()
    {
        return $this->_fullName;
    }
    
    public function setProfileImage($profileImage)
    {
        $this->_profileImage = $profileImage;
    }
    
    public function getProfileImage()
    {
        return $this->_profileImage;
    }
    
    public function setCoverImage($coverImage)
    {
        $this->_coverImage = $coverImage;
    }
    
    public function getCoverImage()
    {
        return $this->_coverImage;
    }
    
    public function setEmail($email)
    {
        $this->_email = $email;
    }
    
    public function getEmail()
    {
        return $this->_email;
    }
    
    public function setCountry($country)
    {
        $this->_country = $country;
    }
    
    public function getCountry()
    {
        return $this->_country;
    }
    
    public function setState($state)
    {
        $this->_state = $state;
    }
    
    public function getState()
    {
        return $this->_state;
    }
    
    public function setCity($city)
    {
        $this->_city = $city;
    }
    
    public function getCity()
    {
        return $this->_city;
    }
    
    public function setContactNo($contactNo)
    {
        $this->_contactNo = $contactNo;
    }
    
    public function getContactNo()
    {
        return $this->_contactNo;
    }
    
    public function setPrimaryCategoryId($primaryCategoryId)
    {
        $this->_primaryCategoryId = $primaryCategoryId;
    }
    
    public function getPrimaryCategoryId()
    {
        return $this->_primaryCategoryId;
    }
    
    public function setCompany($company)
    {
        $this->_company = $company;
    }
    
    public function getCompany()
    {
        return $this->_company;
    }
    
    public function setNricRocNumber($nricRocNumber)
    {
        $this->_nricRocNumber = $nricRocNumber;
    }
    
    public function getNricRocNumber()
    {
        return $this->_nricRocNumber;
    }
    
    public function setMembershipId($membershipId)
    {
        $this->_membershipId = $membershipId;
    }
    
    public function getMembershipId()
    {
        return $this->_membershipId;
    }
    
    public function setPassword($password)
    {
        $this->_password = $password;
    }
    
    public function getPassword()
    {
        return $this->_password;
    }
    
    public function setLastLogin($lastLogin)
    {
        $this->_lastLogin = $lastLogin;
    }
    
    public function getLastLogin()
    {
        return $this->_lastLogin;
    }
    
    public function setCreatedOn($createdOn)
    {
        $this->_createdOn = $createdOn;
    }
    
    public function getCreatedOn()
    {
        return $this->_createdOn;
    }
    
    public function setStatus($status)
    {
        $this->_status = $status;
    }
    
    public function getStatus()
    {
        return $this->_status;
    }
    
    public function setActivationCode($activationCode)
    {
        $this->_activationCode = $activationCode;
    }
    
    public function getActivationCode()
    {
        return $this->_activationCode;
    }
    
    public function setForgetPassword($forgetPassword)
    {
        $this->_forgetPassword = $forgetPassword;
    }
    
    public function getForgetPassword()
    {
        return $this->_forgetPassword;
    }
    
    public function setType($type)
    {
        $this->_type = $type;
    }
    
    public function getType()
    {
        return $this->_type;
    }
    
    public function setIsFeatured($isFeatured)
    {
        $this->_isFeatured = $isFeatured;
    }
    
    public function getIsFeatured()
    {
        return $this->_isFeatured;
    }
    
    public function setIsPremium($isPremium)
    {
        $this->_isPremium = $isPremium;
    }
    
    public function getIsPremium()
    {
        return $this->_isPremium;
    }
    
    public function setSubscriptionStartDate($subscriptionStartDate)
    {
        $this->_subscriptionStartDate = $subscriptionStartDate;
    }
    
    public function getSubscriptionStartDate()
    {
        return $this->_subscriptionStartDate;
    }
    
    public function setSubscrId($subscrId)
    {
        $this->_subscrId = $subscrId;
    }
    
    public function getSubscrId()
    {
        return $this->_subscrId;
    }
    
    public function setUserFbId($userFbId)
    {
        $this->_userFbId = $userFbId;
    }
    
    public function getUserFbId()
    {
        return $this->_userFbId;
    }
    
    public function setTwitterUid($twitterUid)
    {
        $this->_twitterUid = $twitterUid;
    }
    
    public function getTwitterUid()
    {
        return $this->_twitterUid;
    }
    
    public function setTwitterUsername($twitterUsername)
    {
        $this->_twitterUsername = $twitterUsername;
    }
    
    public function getTwitterUsername()
    {
        return $this->_twitterUsername;
    }
    
    public function setTwitterScreenName($twitterScreenName)
    {
        $this->_twitterScreenName = $twitterScreenName;
    }
    
    public function getTwitterScreenName()
    {
        return $this->_twitterScreenName;
    }
    
    public function setPrimaryCategory(Application_Model_PrimaryCategory $primaryCategory)
    {
        $this->_primaryCategory = $primaryCategory;
    }
    
    public function getPrimaryCategory()
    {
        return $this->_primaryCategory;
    }
    
    public function setUserWorked($userWorked)
    {
        $this->_userWorked = $userWorked;
    }
    
    public function getUserWorked()
    {
        return $this->_userWorked;
    }
    
    public function setUserHired($userHired)
    {
        $this->_userHired = $userHired;
    }
    
    public function getUserHired()
    {
        return $this->_userHired;
    }
    
    public function setOpenProjects($openProjects)
    {
        $this->_openProjects = $openProjects;
    }
    
    public function getOpenProjects()
    {
        return $this->_openProjects;
    }
    
    public function setBalance($balance)
    {
        $this->_balance = $balance;
    }
    
    public function getBalance()
    {
        return $this->_balance;
    }
    
    public function setEarned($earned)
    {
        $this->_earned = $earned;
    }
    
    public function getEarned()
    {
        return $this->_earned;
    }
    
    public function setSpent($spent)
    {
        $this->_spent = $spent;
    }
    
    public function getSpent()
    {
        return $this->_spent;
    }
    
    public function setCustom($custom)
    {
        $this->_custom = $custom;
    }
    
    public function getCustom()
    {
        return $this->_custom;
    }
    
    public function setMembership(Application_Model_Membership $membership)
    {
        $this->_membership = $membership;
    }
    
    public function getMembership()
    {
        return $this->_membership;
    }
    
    public function setRating($rating)
    {
        $this->_rating = $rating;
    }
    
    public function getRating()
    {
        return $this->_rating;
    }
    
    
}