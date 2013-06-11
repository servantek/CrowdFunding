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
 * CrowdFunding project controller
 *
 * @package     CrowdFunding
 * @subpackage  Components
 */
class CrowdFundingControllerProjects extends JControllerLegacy {
    
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
    public function getModel($name = 'ProjectItem', $prefix = 'CrowdFundingModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);
        
        return $model;
    }
    
    public function saveState() {
        
        // Check for request forgeries.
		JSession::checkToken("get") or jexit(JText::_('JINVALID_TOKEN'));
		
        $userId = JFactory::getUser()->id;
        if(!$userId) {
            
            $this->setMessage(JText::_("COM_CROWDFUNDING_ERROR_NOT_LOG_IN"), "notice");
            $link = "index.php?option=com_users&view=login";
            $this->setRedirect(JRoute::_($link, false));
            return;
        }
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
		// Get the data from the form
		$itemId    = $app->input->get->get('id', 0, 'int');
		$state     = $app->input->get->get('state', 0, 'int');
        $state     = (!$state) ? 0 : 1;
        
        $link      = "index.php?option=com_crowdfunding&view=projects";
        
        $model   = $this->getModel();
        /** @var $model CrowdFundingModelProjectItem **/
        
        $item   = $model->getItem($itemId);
        if($item->user_id != $userId) {
            $this->setMessage(JText::_("COM_CROWDFUNDING_ERROR_INVALID_PROJECT"), "notice");
            $this->setRedirect(JRoute::_($link, false));
            return;
        }
            
        if($item->approved) {
            $this->setMessage(JText::_("COM_CROWDFUNDING_ERROR_APPROVED_UNPUBLISH"), "notice");
            $this->setRedirect(JRoute::_($link, false));
            return;
        }
            
        try {
            
            $model->validate($item);
            
            $model->saveState($itemId, $state);
            
        } catch(Exception $e) {
            
            JLog::add($e->getMessage());
            
            $code = $e->getCode();
            switch($code) {
                
                case ITPrismErrors::CODE_WARNING:
                    $this->setMessage($e->getMessage(), "notice");
                    $this->setRedirect(JRoute::_($link, false));
                    return;
                break;
                
                default:
                    throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'), ITPrismErrors::CODE_ERROR);
                break;
            }
            
        }
        
		// Redirect to next page
		if(!$state) {
		    $this->setMessage(JText::_("COM_CROWDFUNDING_PROJECT_UNPUBLISHED_SUCCESSFULY"));
		} else {
		    $this->setMessage(JText::_("COM_CROWDFUNDING_PROJECT_PUBLISHED_SUCCESSFULY"));
		}
		
		$this->setRedirect(JRoute::_($link, false));
    }
    
}