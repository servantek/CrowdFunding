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

jimport('joomla.application.component.modeladmin');

class CrowdFundingModelProject extends JModelAdmin {
    
    /**
     * @var     string  The prefix to use with controller messages.
     * @since   1.6
     */
    protected $text_prefix = 'COM_CROWDFUNDING';
    
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   type    The table type to instantiate
     * @param   string  A prefix for the table class name. Optional.
     * @param   array   Configuration array for model. Optional.
     * @return  JTable  A database object
     * @since   1.6
     */
    public function getTable($type = 'Project', $prefix = 'CrowdFundingTable', $config = array()){
        return JTable::getInstance($type, $prefix, $config);
    }
    
    /**
     * Method to get the record form.
     *
     * @param   array   $data       An optional array of data for the form to interogate.
     * @param   boolean $loadData   True if the form is to load its own data (default case), false if not.
     * @return  JForm   A JForm object on success, false on failure
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true){
        
        // Get the form.
        $form = $this->loadForm($this->option.'.project', 'project', array('control' => 'jform', 'load_data' => $loadData));
        if(empty($form)){
            return false;
        }
        
        return $form;
    }
    
    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed   The data for the form.
     * @since   1.6
     */
    protected function loadFormData(){
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState($this->option.'.edit.project.data', array());
        if(empty($data)){
            $data = $this->getItem();
        }
        
        return $data;
    }
    
    /**
     * Save data into the DB
     * 
     * @param $data   The data about item
     * @return     Item ID
     */
    public function save($data){
        
        $id           = JArrayHelper::getValue($data, "id");
        $title        = JArrayHelper::getValue($data, "title");
        $alias        = JArrayHelper::getValue($data, "alias");
        $goal         = JArrayHelper::getValue($data, "goal");
        $funded       = JArrayHelper::getValue($data, "funded");
        $fundingType  = JArrayHelper::getValue($data, "funding_type");
        $pitchVideo   = JArrayHelper::getValue($data, "pitch_video");
        $shortDesc    = JArrayHelper::getValue($data, "short_desc");
        $description  = JArrayHelper::getValue($data, "description");
        $catId        = JArrayHelper::getValue($data, "catid");
        $published    = JArrayHelper::getValue($data, "published");
        $approved     = JArrayHelper::getValue($data, "approved");
        
        // Load a record from the database
        $row = $this->getTable();
        $row->load($id);
        
        $row->set("title",          $title);
        $row->set("alias",          $alias);
        $row->set("goal",           $goal);
        $row->set("funded",         $funded);
        $row->set("funding_type",   $fundingType);
        $row->set("pitch_video",    $pitchVideo);
        $row->set("catid",          $catId);
        $row->set("published",      $published);
        $row->set("approved",       $approved);
        $row->set("short_desc",     $shortDesc);
        $row->set("description",    $description);
        
        $row->store();
        
        return $row->id;
    
    }
    
	/**
	 * Method to change the approved state of one or more records.
	 *
	 * @param   array    &$pks   A list of the primary keys to change.
	 * @param   integer  $value  The value of the approved state.
	 */
	public function approve(&$pks, $value) {
	    
		// Initialise variables.
		$table   = $this->getTable();
		$pks     = (array) $pks;

		$db      = JFactory::getDbo();
		
		$query   = $db->getQuery(true);
		$query
		    ->update($db->quoteName("#__crowdf_projects"))
		    ->set("approved = " . (int)$value)
		    ->where("id IN (".implode(",", $pks).")");

	    $db->setQuery($query);
	    $db->query();
	    
		// Clear the component's cache
		$this->cleanCache();

		return true;
	}
	
	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param	object	A record object.
	 *
	 * @return	array	An array of conditions to add to add to ordering queries.
	 * @since	1.6
	 */
	protected function getReorderConditions($table) {
	    $condition   = array();
	    $condition[] = 'catid = '.(int) $table->catid;
	    return $condition;
	}
}