<?php
/**
 * @package      CrowdFunding
 * @subpackage   ItpDonate
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2010 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * ItpDonate is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

class CrowdFundingModelNotifier extends JModelLegacy {
    
    /**
     * Send mail to administrator and notify him about new transaction.
     * @param array  $data		Transaction data
     * @param object $project	This is an obeject of the project
     * @param mixed  $reward	This is an obeject of the reward
     */
    public function sendMailToAdministrator($data, $project, $reward = null) {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
         // Send email to the administrator
        $subject = JText::_("COM_CROWDFUNDING_NEW_INVESTMENT_ADMIN_SUBJECT");
        $body    = JText::sprintf("COM_CROWDFUNDING_NEW_INVESTMENT_ADMIN_BODY", $project->title);
        $return  = JFactory::getMailer()->sendMail($app->getCfg("mailfrom"), $app->getCfg("fromname"), $app->getCfg("mailfrom"), $subject, $body);
		
		// Check for an error.
		if ($return !== true) {
		    $error = JText::sprintf("COM_CROWDFUNDING_ERROR_MAIL_SENDING_ADMIN");
			JLog::add($error);
		}
        
    }
    
	/**
     * Send mail to user
     * 
     * @param float  $amount
     * @param string $currency
     */
    public function sendMailToUser($data, $project, $reward = null) {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        $amount   = $data->txn_amount.$data->txn_currency;
        
         // Send email to the administrator
        $subject = JText::sprintf("COM_CROWDFUNDING_NEW_INVESTMENT_USER_SUBJECT", $project->title);
        $body    = JText::sprintf("COM_CROWDFUNDING_NEW_INVESTMENT_USER_BODY", $amount, $project->title );
        $return  = JFactory::getMailer()->sendMail($app->getCfg("mailfrom"), $app->getCfg("fromname"), $app->getCfg("mailfrom"), $subject, $body);
		
		// Check for an error.
		if ($return !== true) {
		    $error = JText::_("COM_CROWDFUNDING_ERROR_MAIL_SENDING_USER");
			JLog::add($error);
		}
        
    }
    
}