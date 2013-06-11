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
 * CrowdFunding comment controller
 *
 * @package     ITPrism Components
 * @subpackage  CrowdFunding
  */
class CrowdFundingControllerComment extends JControllerLegacy {
    
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
    public function getModel($name = 'CommentItem', $prefix = 'CrowdFundingModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
    
    
    /**
     * Method to load data via AJAX
     */
    public function getData() {
        
        // Get the input
		$app     = JFactory::getApplication();
		$itemId  = $app->input->get('id', 0, 'int');
        $userId  = JFactory::getUser()->id;
    
		// Get the model
		$model = $this->getModel();
		/** @var $model CrowdFundingModelCommentItem **/

        try {
            
            $item = $model->getItem($itemId);
            
            if($item->user_id != $userId) {
                
                $response = array(
                	"success" => false,
            		"title" => JText::_("COM_CROWDFUNDING_FAIL"), 
                    "text"  => JText::_("COM_CROWDFUNDING_COMMENT_CANNOT_EDIT")
                );
                    
                echo json_encode($response);
                
                JFactory::getApplication()->close();
                
            }
            
        } catch ( Exception $e ) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }
        
        $response = array(
        	"success" => true,
            "data"	  => array(
            	"id"      => $item->id, 
            	"comment" => $item->comment
            )
        );
            
        echo json_encode($response);
        
        JFactory::getApplication()->close();
        
    }
    
	/**
	 * Method to remove records via AJAX.
	 * @return  void
	 */
	public function remove() {
	    
		// Get the input
		$app     = JFactory::getApplication();
		$itemId  = $app->input->post->get('id', 0, 'int');
        $userId  = JFactory::getUser()->id;
    
		// Get the model
		$model = $this->getModel();
		/** @var $model CrowdFundingModelCommentItem **/

        try {
            
            $item = $model->getItem($itemId);
            
            if($item->user_id != $userId) {
                
                $response = array(
                	"success" => false,
            		"title" => JText::_("COM_CROWDFUNDING_FAIL"), 
                    "text"  => JText::_("COM_CROWDFUNDING_COMMENT_CANNOT_REMOVED")
                );
                    
                echo json_encode($response);
                
                JFactory::getApplication()->close();
                
            }
            
            $model->remove($itemId, $userId);
            
        } catch ( Exception $e ) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }
        
        $response = array(
        	"success" => true,
    		"title" => JText::_("COM_CROWDFUNDING_SUCCESS"), 
            "text"  => JText::_("COM_CROWDFUNDING_COMMENT_REMOVED_SUCCESSFULY")
        );
            
        echo json_encode($response);
        
        JFactory::getApplication()->close();
		
	}
    
	
}