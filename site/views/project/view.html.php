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

jimport('joomla.application.categories');
jimport('joomla.application.component.view');

class CrowdFundingViewProject extends JViewLegacy {
    
    protected $form       = null;
    protected $state      = null;
    protected $item       = null;
    
    protected $option     = null;
    
    public function __construct($config){
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->getCmd("option");
    }
    
    /**
     * Display the view
     *
     */
    public function display($tpl = null){
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        if(!JFactory::getUser()->id) {
            $this->setLayout("intro");
        }
        
        $this->layout = $this->getLayout();
        
        switch($this->layout) {
            
            case "rewards":
                $this->prepareRewards();
                break;
                
            case "story":
                $this->prepareStory();
                break;
                
            case "funding":
                $this->prepareFunding();
                break;

            case "intro":
                $this->prepareIntro();
                break;
                
            default: // Basic data for project
                $this->prepareBasic();
                break;
        }
        
        $this->version    = new CrowdfundingVersion();
        
        $this->prepareDebugMode();
        $this->prepareDocument();
        
        parent::display($tpl);
    }
    
    /**
     * Check the system for debug mode
     */
    protected function prepareDebugMode() {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/

        $this->disabledButton = "";
        
        // Check for maintenance (debug) state
        $params = $this->state->get("params");
        $this->debugMode = $params->get("debug_project_adding_disabled", 0);
        if($this->debugMode) {
            $msg = JString::trim($params->get("debug_disabled_functionality_msg"));
            if(!$msg) {
                $msg = JText::_("COM_CROWDFUNDING_DEBUG_MODE_DEFAULT_MSG");
            }
            $app->enqueueMessage($msg, "notice");
            
            $this->disabledButton = 'disabled="disabled"';
        }
        
    }
    
    /**
     * Display default page 
     */
    protected function prepareIntro() {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        $model             = JModelLegacy::getInstance("Intro", "CrowdFundingModel", $config = array('ignore_request' => false));
        $this->state       = $model->getState();
        $this->params      = $this->state->get("params");
        
        $articleId         = $this->params->get("project_intro_article", 0);
        $this->article     = $model->getItem($articleId);
        
        $this->pathwayName = JText::_("COM_CROWDFUNDING_START_PROJECT_BREADCRUMB");
        
    }
    
    protected function prepareBasic() {
        
        $model             = JModelLegacy::getInstance("Project", "CrowdFundingModel", $config = array('ignore_request' => false));
        
        $this->state       = $model->getState();
        $this->params      = $this->state->get("params");
        
        // Get item
        $itemId            = $this->state->get('project.id');
	    $userId            = JFactory::getUser()->id;
        $this->item        = $model->getItem($itemId, $userId);
        
        $this->form        = $model->getForm();
            
        $this->imageFolder = $this->params->get("images_directory", "images/projects");
        $this->imageSmall  = $this->item->get("image_small");
        
        $this->pathwayName = JText::_("COM_CROWDFUNDING_STEP_BASIC");
        
    } 
    
    protected function prepareFunding() {
        
        $model             = JModelLegacy::getInstance("Funding", "CrowdFundingModel", $config = array('ignore_request' => false));
        
        // Initialise variables
        $this->state       = $model->getState();
        
        // Get item
        $itemId            = $this->state->get('funding.id');
	    $userId            = JFactory::getUser()->id;
        $this->item        = $model->getItem($itemId, $userId);
        
        $this->form        = $model->getForm();
        $this->params      = $this->state->get("params");
            
        // Get currency
        jimport("crowdfunding.currency");
        $currencyId        = $this->params->get("project_currency");
        $currency          = CrowdFundingCurrency::getInstance($currencyId);
        
        // Set minimum values - days, amount,...
        $this->minAmount   = $this->params->get("project_amount_minimum", 500);
        $this->minAmount   = $currency->getAmountString($this->minAmount);
        
        $this->minDays     = $this->params->get("project_days_minimum", 30);
        
		// If the date is invalid then set checkedDate to empty string.
        $this->checkedDays = (!$this->item->funding_days) ? "" : 'checked="checked"';
		
        // Validate funding date
        // Set the radio button to checked if there is a funding date
        if(!CrowdFundingHelper::isValidDate($this->item->funding_end) ){
            $this->checkedDate  = '';
        } else {
            $this->checkedDate  = 'checked="checked"';
        }
        
        $this->pathwayName = JText::_("COM_CROWDFUNDING_STEP_FUNDING");
        
    } 
    
    protected function prepareStory() {
        
        $model             = JModelLegacy::getInstance("Story", "CrowdFundingModel", $config = array('ignore_request' => false));
        
        // Initialise variables
        $this->state       = $model->getState();
        
        // Get item
        $itemId            = $this->state->get('story.id');
	    $userId            = JFactory::getUser()->id;
        $this->item        = $model->getItem($itemId, $userId);
        
        $this->form        = $model->getForm();
        $this->params      = $this->state->get("params");
            
        $this->imageFolder = $this->params->get("images_directory", "images/projects");
        $this->pitchImage  = $this->item->get("pitch_image");
        
        $this->pWidth      = $this->params->get("pitch_image_width", 600);
        $this->pHeight     = $this->params->get("pitch_image_height", 400);
        
        $this->pathwayName = JText::_("COM_CROWDFUNDING_STEP_STORY");
        
    } 
    
    protected function prepareRewards() {
        
        $model             = JModelLegacy::getInstance("Rewards", "CrowdFundingModel", $config = array('ignore_request' => false));
        
        // Initialise variables
        $this->state       = $model->getState();
        $this->projectId   = $this->state->get("rewards.id");
        $this->params      = $this->state->get("params");
        
        $this->items       = $model->getItems($this->projectId);
        $this->item        = CrowdFundingHelper::getProject($this->projectId);
        
        jimport("crowdfunding.currency");
        $currencyId        = $this->params->get("project_currency");
		$this->currency    = CrowdFundingCurrency::getInstance($currencyId);
		
        $this->pathwayName = JText::_("COM_CROWDFUNDING_STEP_REWARDS");
        
    } 
    
    /**
     * Prepares the document
     */
    protected function prepareDocument(){

        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
         // Prepare page suffix
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));
        
        $menus = $app->getMenu();
        
        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu  = $menus->getActive();
        
        if($menu) {
            $this->params->def('page_heading', $menu->title);
        } else {
            $this->params->def('page_heading', JText::_('COM_CROWDFUNDING_RAISE_DEFAULT_PAGE_TITLE'));
        }
        
        // Prepare page title
        $title = $menu->title;
        if(!$title){
            $title = $app->getCfg('sitename');
        }elseif($app->getCfg('sitename_pagetitles', 0)){ // Set site name if it is necessary ( the option 'sitename' = 1 )
            $title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
        }
            
        $this->document->setTitle($title);
        
        // Meta Description
        $this->document->setDescription($this->params->get('menu-meta_description'));
        
        // Meta keywords
        $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        
        // Add current layout into breadcrumbs 
        $pathway    = $app->getPathway();
        $pathway->addItem($this->pathwayName);
        
        // Head styles
        $this->document->addStyleSheet('media/'.$this->option.'/css/site/style.css');
        
        // Add scripts
        JHtml::_('bootstrap.framework');
        JHtml::_('formbehavior.chosen', 'select');
        
        JHtml::_('behavior.keepalive');
        JHtml::_('behavior.tooltip');
        JHtml::_('behavior.formvalidation');
        
        switch($this->layout) {
            
            case "rewards":
                
                // Styles
                $this->document->addStyleSheet('media/'.$this->option.'/css/jquery.pnotify.default.css');
                
                // Scripts
		        $this->document->addScript('media/'.$this->option.'/js/jquery.pnotify.min.js');
		        $this->document->addScript('media/'.$this->option.'/js/helper.js');
		        $this->document->addScript('media/'.$this->option.'/js/site/project_rewards.js');
                break;
                
            case "funding":
		        $this->document->addScript('media/'.$this->option.'/js/site/project_funding.js');
                break;

            case "story":
                
                // Styles
                $this->document->addStyleSheet('media/'.$this->option.'/css/bootstrap-fileupload.min.css');
                
                // Scripts
                $this->document->addScript('media/'.$this->option.'/js/bootstrap-fileupload.min.js');
                $this->document->addScript('media/'.$this->option.'/js/site/project_story.js');
                
                break;
                    
            default: // Basic
                
                // Styles
                $this->document->addStyleSheet('media/'.$this->option.'/css/bootstrap-fileupload.min.css');
                
                // Scripts
                $this->document->addScript('media/'.$this->option.'/js/bootstrap-fileupload.min.js');
                $this->document->addScript('media/'.$this->option.'/js/bootstrap-maxlength.min.js');
		        $this->document->addScript('media/'.$this->option.'/js/site/project_basic.js');
                break;
        }
		
    }

}