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

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class CrowdFundingViewImport extends JViewLegacy {
    
    protected $state;
    protected $form;
    
    protected $option;
    
    public function __construct($config) {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->get("option");
    }
    
    /**
     * Display the view
     */
    public function display($tpl = null){
        
        $app = JFactory::getApplication();
        /** @var $app JAdministrator **/
        
        $this->state = $this->get('State');
        $this->form  = $this->get('Form');
        
        $importType  = $this->state->get("import.context");
        
        switch($importType) {
            case "locations":
                $this->legend = JText::_("COM_CROWDFUNDING_IMPORT_LOCATIONS_DATA");
                $this->uploadTask = "import.locations";
                break;
                
            default: // Currencies
                $this->legend = JText::_("COM_CROWDFUNDING_IMPORT_CURRENCY_DATA");
                $this->uploadTask = "import.currencies";
                break;
             
        }
        
        // Add submenu
        CrowdFundingHelper::addSubmenu($importType);
        
        // Prepare actions
        $this->addToolbar();
        $this->setDocument();
        
        parent::display($tpl);
        
    }
    
	/**
     * Add the page title and toolbar.
     *
     * @since   1.6
     */
    protected function addToolbar(){
        
        // Set toolbar items for the page
        JToolBarHelper::title(JText::_('COM_CROWDFUNDING_IMPORT_MANAGER'));
        
        // Upload
		JToolBarHelper::custom($this->uploadTask, "upload", "", JText::_("COM_CROWDFUNDING_UPLOAD"), false);
		
        JToolBarHelper::divider();
        JToolBarHelper::cancel('import.cancel', 'JTOOLBAR_CANCEL');
        
    }
    
	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() {
		
	    $this->document->setTitle(JText::_('COM_CROWDFUNDING_IMPORT_MANAGER'));
		
		// Scripts
        JHtml::_('behavior.tooltip');
        JHtml::_('behavior.formvalidation');
        
		$this->document->addScript('../media/'.$this->option.'/js/admin/'.JString::strtolower($this->getName()).'.js');
	}
	
}
