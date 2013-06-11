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

jimport( 'joomla.application.component.controller' );

/**
 * CrowdFunding rewards controller
 *
 * @package     CrowdFunding
 * @subpackage  Components
 */
class CrowdFundingControllerRewards extends JControllerLegacy {
    
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
    public function getModel($name = 'Rewards', $prefix = 'CrowdFundingModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);
        
        return $model;
    }
    
    /**
	 * Method to remove records via AJAX.
	 * @return  void
	 */
    public function remove() {
	    
	    // Get the input
		$app     = JFactory::getApplication();
		$pks     = $app->input->post->get('rid', array(), 'array');
        $userId  = JFactory::getUser()->id;
        
        // Sanitize the input
		JArrayHelper::toInteger($pks);
		
		// Validate user
        if(!$userId) {
            $response = array(
        		"success" => false,
            	"title"=> JText::_( 'COM_CROWDFUNDING_FAIL' ),
            	"text" => JText::_( 'COM_CROWDFUNDING_ERROR_NOT_LOG_IN' )
            );
            
            echo json_encode($response);
            JFactory::getApplication()->close();
        }
        
        // Validate primary keys
        if(!$pks) {
            $response = array(
        		"success" => false,
            	"title"=> JText::_( 'COM_CROWDFUNDING_FAIL' ),
            	"text" => JText::_( 'COM_CROWDFUNDING_ERROR_INVALID_REWARDS_SELECTED' )
            );
            
            echo json_encode($response);
            JFactory::getApplication()->close();
        }
        
		// Get the model
		$model = $this->getModel();

        try {
            $model->remove($pks, $userId);
        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }
        
        $response = array(
        	"success" => true,
            "title"=> JText::_( 'COM_CROWDFUNDING_SUCCESS' ),
        	"text" => JText::_( 'COM_CROWDFUNDING_REWARD_SUCCESSFULY_REMOVED' )
        );
            
        echo json_encode($response);
        JFactory::getApplication()->close();
        
	}
    
}