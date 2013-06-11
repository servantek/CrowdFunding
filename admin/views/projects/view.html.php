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

jimport('joomla.application.component.view');

class CrowdFundingViewProjects extends JViewLegacy {
    
    protected $state;
    protected $items;
    protected $pagination;
    
    protected $option;
    
    public function __construct($config){
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->get("option");
    }
    
    public function display($tpl = null){
        
        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        
        // Prepare filters
        $this->listOrder  = $this->escape($this->state->get('list.ordering'));
        $this->listDirn   = $this->escape($this->state->get('list.direction'));
        $this->saveOrder  = (strcmp($this->listOrder, 'a.ordering') != 0 ) ? false : true;
        
        jimport("crowdfunding.currency");
        $currencyId       = $this->state->params->get("project_currency");
        $this->currency   = CrowdFundingCurrency::getInstance($currencyId);
        
        // Add submenu
        CrowdFundingHelper::addSubmenu($this->getName());
        
        // Prepare sorting data
        $this->prepareSorting();
        
        // Prepare actions
        $this->addToolbar();
        $this->addSidebar();
        $this->setDocument();
        
        // Include HTML helper
        JHtml::addIncludePath(JPATH_COMPONENT_SITE.'/helpers/html');
        
        parent::display($tpl);
    }
    
    /**
     * Prepare sortable fields, sort values and filters.
     */
    protected function prepareSorting() {
    
        // Prepare filters
        $this->listOrder  = $this->escape($this->state->get('list.ordering'));
        $this->listDirn   = $this->escape($this->state->get('list.direction'));
        $this->saveOrder  = (strcmp($this->listOrder, 'a.ordering') != 0 ) ? false : true;
    
        if ($this->saveOrder) {
            $this->saveOrderingUrl = 'index.php?option='.$this->option.'&task='.$this->getName().'.saveOrderAjax&format=raw';
            JHtml::_('sortablelist.sortable', $this->getName().'List', 'adminForm', strtolower($this->listDirn), $this->saveOrderingUrl);
        }
    
        $this->sortFields = array(
            'a.ordering'      => JText::_('JGRID_HEADING_ORDERING'),
            'a.published'     => JText::_('JSTATUS'),
            'a.title'         => JText::_('COM_CROWDFUNDING_TITLE'),
            'b.title'         => JText::_('COM_CROWDFUNDING_CATEGORY'),
            'a.created'       => JText::_('COM_CROWDFUNDING_CREATED'),
            'a.goal'          => JText::_('COM_CROWDFUNDING_GOAL'),
            'a.funded'        => JText::_('COM_CROWDFUNDING_FUNDED'),
            'funded_percents' => JText::_('COM_CROWDFUNDING_FUNDED_PERCENTS'),
            'a.funding_start' => JText::_('COM_CROWDFUNDING_START_DATE'),
            'a.funding_end'   => JText::_('COM_CROWDFUNDING_END_DATE'),
            'a.approved'      => JText::_('COM_CROWDFUNDING_APPROVED'),
            'a.id'            => JText::_('JGRID_HEADING_ID')
            
        );
    
    }
    
    /**
     * Add a menu on the sidebar of page
     */
    protected function addSidebar() {
    
        JHtmlSidebar::setAction('index.php?option='.$this->option.'&view='.$this->getName());
    
        JHtmlSidebar::addFilter(
            JText::_('JOPTION_SELECT_PUBLISHED'),
            'filter_state',
            JHtml::_('select.options', JHtml::_('jgrid.publishedOptions', array("archived" => false, "trash"=>false)), 'value', 'text', $this->state->get('filter.state'), true)
        );
    
        JHtmlSidebar::addFilter(
            JText::_('JOPTION_SELECT_CATEGORY'),
            'filter_category_id',
            JHtml::_('select.options', JHtml::_('category.options', 'com_crowdfunding'), 'value', 'text', $this->state->get('filter.category_id'))
        );
    
        $this->sidebar = JHtmlSidebar::render();
    
    }
    
    /**
     * Add the page title and toolbar.
     *
     * @since   1.6
     */
    protected function addToolbar(){
        
        // Set toolbar items for the page
        JToolBarHelper::title(JText::_('COM_CROWDFUNDING_PROJECTS_MANAGER'));
        JToolBarHelper::custom('projects.approve', "ok", "", JText::_("COM_CROWDFUNDING_APPROVE"), false);
        JToolBarHelper::custom('projects.disapprove', "ban-circle", "", JText::_("COM_CROWDFUNDING_DISAPPROVE"), false);
        JToolBarHelper::divider();
        JToolBarHelper::publishList("projects.publish");
        JToolBarHelper::unpublishList("projects.unpublish");
        
        JToolBarHelper::divider();
        JToolBarHelper::trash("projects.trash");
        JToolBarHelper::divider();
        JToolBarHelper::custom('projects.backToDashboard', "dashboard", "", JText::_("COM_CROWDFUNDING_DASHBOARD"), false);
        
    }
    
	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() {
	    
		$this->document->setTitle(JText::_('COM_CROWDFUNDING_PROJECTS_MANAGER'));
		
		// Scripts
		JHtml::_('behavior.multiselect');
		JHtml::_('bootstrap.tooltip');
		
		JHtml::_('formbehavior.chosen', 'select');
		
		$this->document->addScript('../media/'.$this->option.'/js/admin/list.js');
		
	}
    
}