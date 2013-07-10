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

jimport('joomla.application.component.modelform');

class CrowdFundingModelFunding extends CrowdFundingModelProject {
    
    /**
     * Method to get the profile form.
     *
     * The base form is loaded from XML and then an event is fired
     * for users plugins to extend the form with extra fields.
     *
     * @param	array	$data		An optional array of data for the form to interogate.
     * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
     * @return	JForm	A JForm object on success, false on failure
     * @since	1.6
     */
    public function getForm($data = array(), $loadData = true) {
        // Get the form.
        $form = $this->loadForm($this->option.'.funding', 'funding', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }
        
        return $form;
    }
    
    /**
     * Method to get the data that should be injected in the form.
     *
     * @return	mixed	The data for the form.
     * @since	1.6
     */
    protected function loadFormData() {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
		$data	    = $app->getUserState($this->option.'.edit.funding.data', array());
		if(!$data) {
		    
		    $itemId = $this->getState($this->getName().'.id');
		    $userId = JFactory::getUser()->id;
		    
		    $data   = $this->getItem($itemId, $userId);
		    
		}

		return $data;
    }
    
    /**
     * Method to save the form data.
     *
     * @param	array		The form data.
     * @return	mixed		The record id on success, null on failure.
     * @since	1.6
     */
    public function save($data) {
        
        $id             = JArrayHelper::getValue($data, "id");
        $goal           = JArrayHelper::getValue($data, "goal");
        $fundingType    = JArrayHelper::getValue($data, "funding_type");
        $fundingStart	= JArrayHelper::getValue($data, "funding_start");
        $fundingEnd     = JArrayHelper::getValue($data, "funding_end");
        $fundingDays    = JArrayHelper::getValue($data, "funding_days");
        $durationType   = JArrayHelper::getValue($data, "funding_duration_type");
        
        // Load a record from the database
        $row = $this->getTable();
        $row->load($id);
        
        $row->set("goal",          $goal);
        $row->set("funding_type",  $fundingType);
        $row->set("funding_start", $fundingStart);
        $row->set("funding_end",   $fundingEnd);
        $row->set("funding_days",  $fundingDays);
        
        $this->prepareTable($row, $durationType);
        
        $row->store();
        
        return $row->id;
        
    }
    
	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since	1.6
	 */
	protected function prepareTable(&$table, $durationType) {
	    
	    $userId = JFactory::getUser()->id;
	    
		if (empty($table->id) OR ($userId != $table->user_id)) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_INVALID_PROJECT"), ITPrismErrors::CODE_ERROR);
		}
		
		$fundingEndDate = "0000-00-00";
		
		switch($durationType) {
		    
		    case "date":
		        
		        $table->set("funding_days", 0);
		        
		        if(CrowdFundingHelper::isValidDate($table->funding_end)) {
		            jimport('joomla.utilities.date');
		            $date = new JDate($table->get("funding_end"));
		            $fundingEndDate = $date->toSql();
		        } 
		        
                $table->set("funding_end", $fundingEndDate);
		        break;
	        
		    case "days":
		        $table->set("funding_end", $fundingEndDate);
		        break;
		        
		    default:
		        $table->set("funding_days", 0);
		        $table->set("funding_end",  $fundingEndDate);
		        break;
		}
		
	}
	
	/**
	 * Valudate funding data
	 * @param array $data
	 */
	public function validateFundingData($data) {
	    
	    $params        = JComponentHelper::getParams($this->option);
	    
	    $goal          = JArrayHelper::getValue($data, "goal", 0, "float");
        $minimumAmount = $params->get("project_amount_minimum", 500);
        $minimumDays   = (int)$params->get("project_days_minimum", 15);
        $fundingType   = JArrayHelper::getValue($data, "funding_duration_type");
         
        // Verify goal
        if($goal < $minimumAmount) {
            throw new Exception( JText::_('COM_CROWDFUNDING_ERROR_INVALID_GOAL'), ITPrismErrors::CODE_WARNING );
        }
        
	    // Verify funding type
	    if(strcmp("days", $fundingType) == 0) {
	        
	        $days = JArrayHelper::getValue($data, "funding_days", 0, "integer");
	        if($days < $minimumDays) {
	            throw new Exception( JText::_('COM_CROWDFUNDING_ERROR_INVALID_DAYS'), ITPrismErrors::CODE_WARNING );
	        }
	        
	    } else {
	        
            $fundingDate    = JArrayHelper::getValue($data, "funding_end");
            if(!CrowdFundingHelper::isValidDate($fundingDate)) {
                throw new Exception( JText::_('COM_CROWDFUNDING_ERROR_INVALID_DATE'), ITPrismErrors::CODE_WARNING );
            }
           
	    }
	    
	}
	
}