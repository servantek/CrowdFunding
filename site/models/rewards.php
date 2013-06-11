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

jimport('joomla.application.component.model');

class CrowdFundingModelRewards extends JModelLegacy {
    
	/**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   type    The table type to instantiate
     * @param   string  A prefix for the table class name. Optional.
     * @param   array   Configuration array for model. Optional.
     * @return  JTable  A database object
     * @since   1.6
     */
    public function getTable($type = 'Reward', $prefix = 'CrowdFundingTable', $config = array()){
        return JTable::getInstance($type, $prefix, $config);
    }
    
	/**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since	1.6
     */
    protected function populateState() {
        
        parent::populateState();
        
        $app = JFactory::getApplication("Site");
        /** @var $app JSite **/
        
		// Get the pk of the record from the request.
		$value     = $app->input->getInt("id");
		$this->setState($this->getName().'.id', $value);

		// Load the parameters.
		$value = JComponentHelper::getParams($this->option);
		$this->setState('params', $value);
		
    }
    
    public function getItems($projectId) {
        
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        $query
            ->select("id, amount, title, description, number, distributed, delivery")
            ->from("#__crowdf_rewards")
            ->where("project_id = ". (int)$projectId);
        
        $db->setQuery($query);
        return $db->loadAssocList();
        
    }
    
    public function validate($data, $projectId) {
        
        if(empty($data)) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_INVALID_REWARDS"), ITPrismErrors::CODE_WARNING);
        }
        
        $filter = JFilterInput::getInstance();
        
        foreach($data as $key => $item) {
            
            // Filter data
            if(!is_numeric($item["amount"])) {
                $item["amount"] = 0;
            }

            $item["title"]       = $filter->clean($item["title"], "string");
            $item["title"]       = JString::trim($item["title"]);
            $item["title"]       = JString::substr($item["title"], 0, 128);

            $item["description"] = $filter->clean($item["description"], "string");
            $item["description"] = JString::trim($item["description"]);
            $item["description"] = JString::substr($item["description"], 0, 500);
            
            $item["number"]      = (int)$item["number"];
            
            $item["delivery"]    = JString::trim($item["delivery"]);
            $item["delivery"]    = $filter->clean($item["delivery"], "string");
            
            if(!empty($item["delivery"])) {
                $date     = new JDate($item["delivery"]);
                $unixTime = $date->toUnix();
                if($unixTime < 0) {
                    $item["delivery"] = "";
                }
            }
            
            if(!$item["title"]) {
                throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_INVALID_TITLE"), ITPrismErrors::CODE_WARNING);
            }
            
            if(!$item["description"]) {
                throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_INVALID_DESCRIPTION"), ITPrismErrors::CODE_WARNING);
            }
            
            if(!$item["amount"]) {
                throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_INVALID_AMOUNT"), ITPrismErrors::CODE_WARNING);
            }
            
            $data[$key] = $item;
        }
        
        return $data;
    }
    
    /**
     * Method to save the form data.
     *
     * @return	mixed		The record id on success, null on failure.
     * @since	1.6
     */
    public function save($data, $projectId) {
        
        foreach($data as $item) {
            
            // Load a record from the database
            $row    = $this->getTable();
            $itemId = JArrayHelper::getValue($item, "id");
            if($itemId) {
                $row->load($itemId);
            }
            
            $amount         = JArrayHelper::getValue($item, "amount");
            $title          = JArrayHelper::getValue($item, "title");
            $description    = JArrayHelper::getValue($item, "description");
            $number         = JArrayHelper::getValue($item, "number");
            $delivery       = JArrayHelper::getValue($item, "delivery");
        
            $row->set("amount",      $amount);
            $row->set("title",       $title);
            $row->set("description", $description);
            $row->set("number",       $number);
            $row->set("delivery",    $delivery);
            $row->set("project_id",  $projectId);
            
            $row->store();
            
        }
        
    }
    
    public function remove($pks, $userId) {
        
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        $query
            ->delete()
            ->from("#__crowdf_rewards USING #__crowdf_rewards")
            ->innerJoin("#__crowdf_projects ON #__crowdf_rewards.project_id = #__crowdf_projects.id")
            ->where("#__crowdf_rewards.id IN (". implode(",", $pks) .")")
            ->where("(#__crowdf_projects.user_id = ". (int)$userId .")");
        
        $db->setQuery($query);
        $db->query();
        
    }
}