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

jimport('itprism.controller.default');

/**
 * CrowdFunding project controller
 *
 * @package     CrowdFunding
 * @subpackage  Components
 */
class CrowdFundingControllerProjects extends ITPrismControllerDefault {
    
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
            $redirectData = array(
                "force_direction" => "index.php?option=com_users&view=login"
            );
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'), $redirectData);
            return;
        }
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
		// Get the data from the form
		$itemId    = $app->input->get->get('id', 0, 'int');
		$state     = $app->input->get->get('state', 0, 'int');
        $state     = (!$state) ? 0 : 1;
        
        $redirectData = array(
            "view" => "projects"
        );
        
        $model   = $this->getModel();
        /** @var $model CrowdFundingModelProjectItem **/
        
        $item   = $model->getItem($itemId);
        if($item->user_id != $userId) {
            $this->displayWarning(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'), $redirectData);
            return;
        }
            
        if($item->approved) {
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_APPROVED_UNPUBLISH'), $redirectData);
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
                    $this->displayWarning($e->getMessage(), $redirectData);
                    return;
                break;
                
                default:
                    throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'), ITPrismErrors::CODE_ERROR);
                break;
            }
            
        }
        
		// Redirect to next page
		if(!$state) {
		    $this->displayMessage(JText::_("COM_CROWDFUNDING_PROJECT_UNPUBLISHED_SUCCESSFULY"), $redirectData);
		} else {
		    $this->displayMessage(JText::_("COM_CROWDFUNDING_PROJECT_PUBLISHED_SUCCESSFULY"), $redirectData);
		}
    }
    
}