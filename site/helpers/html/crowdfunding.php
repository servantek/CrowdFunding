<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2010 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * CrowdFunding is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// no direct access
defined('_JEXEC') or die;

/**
 * CrowdFunding Html Helper
 *
 * @package		ITPrism Components
 * @subpackage	CrowdFunding
 * @since		1.6
 */
abstract class JHtmlCrowdFunding {
    
    /**
     * 
     * Display an icon for approved or not approved project
     * @param integer $value
     */
    public static function approved($value) {
        
        $html = '<i class="{ICON}"></i>';
        
        switch($value) {
            
            case 1: // Published
                $html = str_replace("{ICON}", "icon-ok-sign", $html);
                break;
                
            default: // Unpublished
                $html = str_replace("{ICON}", "icon-remove-sign", $html);
                break;
        }
        
        return $html;
        
    }
    
    /**
     * 
     * Display an input field for amount
     * @param float $value
     * @param object $currency
     * @param array $options
     */
    public static function inputAmount($value, $currency, $options) {
        
        $class = "";
        if(!empty($currency->symbol)){
            $class = "input-prepend ";
        }
        
        $class .= "input-append";
        
        $html = '<div class="'.$class.'">';
        
        if(!empty($currency->symbol)){
            $html .= '<span class="add-on">'. $currency->symbol .'</span>';
        }
            
        $name = JArrayHelper::getValue($options, "name");
        
        $id   = "";
        if(JArrayHelper::getValue($options, "id")) {
            $id = 'id="'.JArrayHelper::getValue($options, "id").'"';
        }
        
        if(!$value OR !is_numeric($value)) {
            $value = 0;
        }
        
        if(JArrayHelper::getValue($options, "class")) {
            $class = 'class="'.JArrayHelper::getValue($options, "class").'"';
        }
        
        $html .= '<input type="text" name="'.$name.'" value="'.$value.'" '.$id.' '.$class.' />';
        
        if(!empty($currency->abbr)) {
            $html .= '<span class="add-on">'.$currency->abbr.'</span>';
        }
            
        $html .= '</div>';
        
        return $html;
        
    }
    
    /**
     * Add symbol or abbreviation to a currency
     * 
     * @param float $value
     * @param array $currency
     * 
     * @deprecated 1.1 Use CrowdFundingCurrency::getAmountString()
     */
    public static function amount($value, $currency) {
        
        if(!empty($currency["symbol"])) { // Prepended
		    $amount = $currency["symbol"].$value;
		} else { // Append
		    $amount = $value.$currency["abbr"];
		}
		
		return $amount;
    } 
    
    /**
     * Display a progress bar
     * 
     * @param int 	  $percent	 	A percent of fund raising
     * @param int     $daysLeft
     * @param string  $fundingType
     */
    public static function progressBar($percent, $daysLeft, $fundingType) {
        
        $html   = array();
        $class  = 'progress-success';
        
        if($daysLeft > 0 ) {
            $html[1] = '<div class="bar" style="width: '.$percent.'%"></div>';
        } else {
            
            // Check for the type of funding
            if($fundingType == "FLEXIBLE") { 
            
                if($percent > 0 ) {
                    $html[1] = '<div class="bar" style="width: 100%">'.JString::strtoupper( JText::_("COM_CROWDFUNDING_SUCCESSFUL") ).'</div>';                
                } else {
                    $class   = 'progress-danger';
                    $html[1] = '<div class="bar" style="width: 100%">'.JString::strtoupper( JText::_("COM_CROWDFUNDING_COMPLETED") ).'</div>';
                }
                
            } else { // Fixed
                
                if($percent >= 100 ) {
                    $html[1] = '<div class="bar" style="width: 100%">'.JString::strtoupper( JText::_("COM_CROWDFUNDING_SUCCESSFUL") ).'</div>';                
                } else {
                    $class   = 'progress-danger';
                    $html[1] = '<div class="bar" style="width: 100%">'.JString::strtoupper( JText::_("COM_CROWDFUNDING_COMPLETED") ).'</div>';
                }
                
            }
            
        }
        
        $html[0] = '<div class="progress '.$class.'">';
        $html[2] = '</div>';
        
        ksort($html);
        
        return implode("\n", $html);
    } 
    
    /**
     * Display a state of result
     * 
     * @param int 	$percent	 	A percent of fund raising
     * @param string  $fundingType
     */
    public static function resultState($percent, $fundingType) {
        
        $html   = array();
        
        // Check for the type of funding
        if($fundingType == "FLEXIBLE") { 
            
            if($percent > 0 ) {
                $otuput = JText::_("COM_CROWDFUNDING_SUCCESSFUL");                
            } else {
                $otuput = JText::_("COM_CROWDFUNDING_COMPLETED");
            }
            
        } else { // Fixed
            
            if($percent >= 100 ) {
                $otuput = JText::_("COM_CROWDFUNDING_SUCCESSFUL");                
            } else {
                $otuput = JText::_("COM_CROWDFUNDING_COMPLETED");
            }
            
        }
        
        return $otuput;
    } 
    
    /**
     * 
     * Display a text that describes the state of result
     * 
     * @param int 	  $percent	 A percent of fund raising
     * @param string  $fundingType
     */
    public static function resultStateText($percent, $fundingType) {
        
        $html   = array();
        
        // Check for the type of funding
        if($fundingType == "FLEXIBLE") { 
            
            if($percent > 0 ) {
                $otuput = JText::_("COM_CROWDFUNDING_FUNDRAISE_FINISHED_SUCCESSFULLY");                
            } else {
                $otuput = JText::_("COM_CROWDFUNDING_FUNDRAISE_HAS_EXPIRED");
            }
            
        } else { // Fixed
            
            if($percent >= 100 ) {
                $otuput = JText::_("COM_CROWDFUNDING_FUNDRAISE_FINISHED_SUCCESSFULLY");                
            } else {
                $otuput = JText::_("COM_CROWDFUNDING_FUNDRAISE_HAS_EXPIRED");
            }
            
        }
        
        return $otuput;
    } 
    
    /**
     * 
     * Display an icon for state of project
     * @param integer $value
     * @param string  $url		An url to the task
     */
    public static function state($value, $url) {
        
        $html = '<a href="'.$url.'"><i class="{ICON}"></i></a>';
        
        switch($value) {
            
            case 1: // Published
                $html = str_replace("{ICON}", "icon-ok-circle", $html);
                break;
                
            default: // Unpublished
                $html = str_replace("{ICON}", "icon-remove-circle", $html);
                break;
        }
        
        return $html;
    }
    

	public static function approvedBackend($i, $value, $prefix, $checkbox = 'cb') {
	    
	    if(!$value) { // Disapproved
		    $task   = $prefix."approve";
		    $title  = "COM_CROWDFUNDING_APPROVE_ITEM";
		    $text   = "COM_CROWDFUNDING_DISAPPROVED";
		    $class  = "ban-circle";
	    } else {
	        $task   = $prefix."disapprove";
	        $title  = "COM_CROWDFUNDING_DISAPPROVE_ITEM";
	        $text   = "COM_CROWDFUNDING_APPROVED";
	        $class  = "ok";
	    }
		
		$html[] = '<a class="btn btn-micro" rel="tooltip" ';
		$html[] = ' href="javascript:void(0);" onclick="return listItemTask(\'' . $checkbox . $i . '\',\'' . $task . '\')"';
		$html[] = ' title="' . addslashes(htmlspecialchars(JText::_($title), ENT_COMPAT, 'UTF-8')) . '">';
		$html[] = '<i class="icon-' . $class . '"></i>';
		$html[] = '</a>';
		
		return implode($html);
	}
	
    
    /**
     * 
     * If value is higher than 100, sets it to 100. 
     * This method validates percent of funding.
     * @param integer $value
     */
    public static function funded($value) {
		if($value > 100) {
		    $value = 100;
		};
		return $value;
    }
    
    /**
     * Calculate funded percents
     * @param float $goal
     * @param float $funded
     */
    public static function percents($goal, $funded) {
		
        $percents = 0;
        if($goal > 0) {
            $percents = round( ($funded/$goal) * 100, 2 );
        }
        
		return $percents;
    }
    
    /**
     * 
     * This method generates a code that display a video
     * @param string $value
     */
    public static function video($value) {
        
        jimport("itprism.video.embed");
        $videoEmbed = new ITPrismVideoEmbed($value);
        $html = $videoEmbed->getHtmlCode();
        
        return $html;
    }
    
    /**
     * Generate a link to an user image of a social platform
     * 
     * @param string $socialPlatform	The name of the social platform.
     * @param JUser $user		        This is a Joomla! user object.
     * 
     * @return string A routed link to profile
     */ 
    public static function socialProfile($socialPlatform, JUser $user) {
    
        $link = "";
    
        switch($socialPlatform) {
    
            case "com_socialcommunity":
    
                jimport("itprism.integrate.profile.socialcommunity");
                $profile = new ITPrismIntegrateProfileSocialCommunity($user);
                $link    = $profile->getLink();
    
                break;
    
            case "com_kunena":
    
                jimport("itprism.integrate.profile.kunena");
                $profile = new ITPrismIntegrateProfileKunena($user);
                $link    = $profile->getLink();
    
                break;
    
            case "gravatar":
    
                jimport("itprism.integrate.profile.gravatar");
                $profile = new ITPrismIntegrateProfileGravatar($user);
                $link    = $profile->getLink();
    
                break;
    
            default:
                $link = "";
                break;
        }
    
        return $link;
    
    }
    
    /**
     * 
     * Generate a link to an user image of a social platform
     * @param string $socialPlatform	The name of the social platform
     * @param JUser  $user		        Joomla! user object
     * @param string $default		    A link to default picture
     * 
     * @todo Add option for setting image size
     */    
    public static function socialAvatar($socialPlatform, JUser $user, $default = null) {
        
        $link = "";

        switch($socialPlatform) {

            case "com_socialcommunity":
                
                jimport("itprism.integrate.profile.socialcommunity");
                $profile = new ITPrismIntegrateProfileSocialCommunity($user);
                $link    = $profile->getAvatar();
                break;
                
            case "com_kunena":
                
                jimport("itprism.integrate.profile.kunena");
                $profile = new ITPrismIntegrateProfileKunena($user);
                $link    = $profile->getAvatar();
                
                break;
                
            case "gravatar":
                
                jimport("itprism.integrate.profile.gravatar");
                $profile = new ITPrismIntegrateProfileGravatar($user);
                $profile->setSize(50);
                $link    = $profile->getAvatar();
                
                break;
            
            default:
                $link = "";
                break;
        }
        
        // Set the linke to default picture
        if(!$link AND !empty($default)) {
            $link = $default;
        }
        
		return $link;
		
    }
    
}
