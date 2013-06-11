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

jimport('joomla.application.component.modellist');

class CrowdFundingModelFunders extends JModelList {
    
    protected $items   = null;
    protected $numbers = null;
    protected $params  = null;
    
    /**
     * Constructor.
     *
     * @param   array   An optional associative array of configuration settings.
     * @see     JController
     * @since   1.6
     */
    public function __construct($config = array()){
        
        if(empty($config['filter_fields'])){
            $config['filter_fields'] = array(
                'id', 'a.id',
                'name', 'a.name'
            );
        }
        
        parent::__construct($config);
    }
    
	/**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  void
     * @since   1.6
     */
    protected function populateState($ordering = 'ordering', $direction = 'ASC'){
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        // Load parameters
        $params     =  $app->getParams();
        $this->setState('params', $params);
        
        // Get project id
        $value      = $app->input->get("id", 0, "uint");
        $this->setState($this->context.'.project_id', $value);
        
        parent::populateState("a.txn_date", "DESC");
        
    }
    
	/**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string      $id A prefix for the store id.
     * @return  string      A store id.
     * @since   1.6
     */
    protected function getStoreId($id = '') {
        
        // Compile the store id.
        $id.= ':' . $this->getState($this->context.'.project_id');
        $id.= ':' . $this->getState('list.ordering');
        $id.= ':' . $this->getState('list.direction');

        return parent::getStoreId($id);
    }
    
   /**
     * Build an SQL query to load the list data.
     *
     * @return  JDatabaseQuery
     * @since   1.6
     */
    protected function getListQuery() {
        
        $db     = $this->getDbo();
        /** @var $db JDatabaseMySQLi **/
        
        // Create a new query object.
        $query  = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.txn_date, '. 
                'b.id, b.name'
            )
        );
        $query->from($db->quoteName('#__crowdf_transactions').' AS a');
        $query->innerJoin($db->quoteName('#__users').' AS b ON a.investor_id = b.id');

        // Filter by project id
        $projectId = $this->getState($this->context.".project_id");
        $query->where("a.project_id =" .(int)$projectId);
        
        // Add the list ordering clause.
        $orderString = $this->getOrderString();
        $query->order($db->escape($orderString));

        return $query;
    }
    
    protected function getOrderString() {
        
        $orderCol   = $this->getState('list.ordering');
        $orderDirn  = $this->getState('list.direction');
        
        return $orderCol.' '.$orderDirn;
    }
    
}