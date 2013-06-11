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

jimport('joomla.application.component.controller');

/**
 * CrowdFunding backing controller
 *
 * @package     ITPrism Components
 * @subpackage  CrowdFunding
  */
class CrowdFundingControllerBacking extends JControllerLegacy {
    
	/**
     * Method to get a model object, loading it if required.
     *
     * @param	string	$name	The model name. Optional.
     * @param	string	$prefix	The class prefix. Optional.
     * @param	array	$config	Configuration array for model. Optional.
     *
     * @return	object	The model.
     * @since	1.5
     */
    public function getModel($name = 'Backing', $prefix = 'CrowdFundingModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
    
    public function step1() {
        
        // Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
        $userId = JFactory::getUser()->id;
        if(!$userId) {
            $this->setMessage(JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'), "notice");
            $this->setRedirect(JRoute::_("index.php?option=com_users&view=login", false));
            return;
        }
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
		// Get the data from the form
		$itemId              = $app->input->post->getInt('id', 0);
		$rewardId            = $app->input->post->getInt('rid', 0);
		
        $model               = $this->getModel();
        /** @var $model CrowdFundingModelBacking **/
        
        // Get the item
        $item   = $model->getItem($itemId);
        
        // Check for valid project
        if(empty($item->id))  {
            $this->setMessage(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'), "notice");
            $this->setRedirect(JRoute::_("index.php?option=com_crowdfunding&view=discover", false));
            return;
        }
        
        // Get params
        $params        = JComponentHelper::getParams("com_crowdfunding");
        
        // Check for maintenance (debug) state
        if($this->isDebugMode($params, $item, $rewardId)) {
            return;
        }
        
        // Get context string
		$modelContext        = $model->getContext();
		$projectContext      = $modelContext.".project".$itemId;
		
		// Check for agreed conditions from the user
        if($params->get("backing_terms", 0)) {
            $terms = $app->input->post->get("terms", 0, "int");
            if(!$terms) {
                $app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_TERMS_NOT_ACCEPTED"), "notice");
                $link = CrowdFundingHelperRoute::getBackingRoute($item->slug, $item->catslug);
                $app->redirect(JRoute::_($link, false));
                return; 
            }
        }
        
        // Check for valid amount
        $amount       = $app->input->post->get("amount", 0, "float");
        if(!$amount) {
            $app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_INVALID_AMOUNT"), "notice");
            $link = CrowdFundingHelperRoute::getBackingRoute($item->slug, $item->catslug);
            $app->redirect(JRoute::_($link, false));
            return; 
        }
        
        // Initialize step one
        $app->setUserState($projectContext.".step1", false);
        
        // Set project amount to the session
        $app->setUserState($projectContext.".amount", $amount);
        
        // Set the flag of step 1 to true
        $app->setUserState($projectContext.".step1", true);
        
        // Redirect to next page
        $link = CrowdFundingHelperRoute::getBackingRoute($item->slug, $item->catslug, "payment");
		$this->setRedirect(JRoute::_($link, false));
    }
    
    protected function isDebugMode($params, $item, $rewardId) {
        
        $this->debugMode = $params->get("debug_payment_disabled", 0);
        if(!$this->debugMode) {
		    return false;
        }
        
        $msg = JString::trim($params->get("debug_disabled_functionality_msg"));
        if(!$msg) {
            $msg = JText::_("COM_CROWDFUNDING_DEBUG_MODE_DEFAULT_MSG");
        }
		    
        // Set message
        JFactory::getApplication()->enqueueMessage($msg, "notice");
        
        // Redirect
        $link = CrowdFundingHelperRoute::getBackingRoute($item->slug, $item->catslug);
        $this->setRedirect(JRoute::_($link, false));

        return true;
    } 

    
}