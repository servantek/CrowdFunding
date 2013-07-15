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

class CrowdFundingModelBacking extends JModel {
    
    protected $item;
    
    /**
	 * Model context string.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $context = 'com_crowdfunding.backing';
    
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   type    The table type to instantiate
     * @param   string  A prefix for the table class name. Optional.
     * @param   array   Configuration array for model. Optional.
     * @return  JTable  A database object
     * @since   1.6
     */
    public function getTable($type = 'Project', $prefix = 'CrowdFundingTable', $config = array()) {
        return JTable::getInstance($type, $prefix, $config);
    }
    
    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since	1.6
     * @todo replace $_context with $context
     */
    protected function populateState() {
        
        $app     = JFactory::getApplication();
        $params  = $app->getParams();
        
        // Project ID
        $itemId   = $app->input->getUint('id');
        $this->setState($this->context.'.id', $itemId);
        
        $projectContext = $this->context.".project".$itemId;
        
        // Reward ID
        $value   = $app->getUserStateFromRequest($projectContext.".rid", 'rid');
        $this->setState($this->context.'.rid', $value);
        
        // Load the parameters.
        $this->setState('params', $params);
    }
    
    /**
     * Return the context of the model
     */
    public function getContext() {
        return $this->context;        
    }
    
    /**
     * Method to get an ojbect.
     *
     * @param	integer	The id of the object to get.
     *
     * @return	mixed	Object on success, false on failure.
     */
    public function getItem($id = null) {
        
        if (empty($id)) {
            $id = $this->getState($this->context.'.id');
        }
        
        if (is_null($this->item)) {
            
            $db     = $this->getDbo();
            $query  = $db->getQuery(true);
            
            $query
                ->select(
                	"a.id, a.title, a.short_desc, a.image, " . 
                	"a.funded, a.goal, a.pitch_video, a.pitch_image, " . 
                	"a.funding_start, a.funding_end, a.funding_days, " .  
                	"a.funding_type, a.user_id, " . 
                	"b.name AS user_name, " .
                	$query->concatenate(array("a.id", "a.alias"), "-") . ' AS slug, ' .
                	$query->concatenate(array("c.id", "c.alias"), "-") . ' AS catslug' 
                )
                ->from("#__crowdf_projects AS a")
                ->innerJoin('#__users AS b ON a.user_id = b.id')
                ->innerJoin('#__categories AS c ON a.catid = c.id')
                ->where("a.id = " .(int)$id)
                ->where("a.published = 1")
                ->where("a.approved  = 1");

            $db->setQuery($query, 0, 1);
            $result = $db->loadObject();
            
            // Attempt to load the row.
            if (!empty($result)) {
                $result->funded_percents = CrowdFundingHelper::calculatePercent($result->funded, $result->goal);
                $result->days_left       = CrowdFundingHelper::calcualteDaysLeft($result->funding_days, $result->funding_start, $result->funding_end);
                if(!empty($result->funding_days)) {
                    $result->funding_end     = CrowdFundingHelper::calcualteEndDate($result->funding_days, $result->funding_start);
                }
                $this->item              = $result;
            } 
        }
        
        return $this->item;
    }

    /**
     * 
     * Load all rewards of a project
     * @param integer $id Project ID
     */
    public function getRewards($id = null) {
        
        if (empty($id)) {
            $id = $this->getState($this->context.'.id');
        }
        
        $results = array();
        
        if (!empty($id)) {
            
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            
            $query
                ->select("a.id, a.title, a.description, a.amount")
                ->from($db->quoteName("#__crowdf_rewards") ." AS a")
                ->where("a.project_id = " .(int)$id);

            $db->setQuery($query);
            $results = $db->loadObjectList();
            
        }
        
        return $results;
    }
    
    /**
     * 
     * Get reward
     * @param integer $id
     */
    public function getReward($rewardId = null) {
        
        if (empty($rewardId)) {
            $rewardId = $this->getState($this->context.'.rid');
        }
        
        // Get project id
        $projectId = $this->getState($this->context . '.id');
        
        $row = null;
        
        if (!empty($rewardId) AND !empty($projectId) ) {
            
            $keys = array(
                "id"         => $rewardId,
                "project_id" => $projectId
            );
            $row = $this->getTable("Reward");
            $row->load($keys);
            if(!$row->id) {
                $row = null;
            }
        }
        
        return $row;
    }
}