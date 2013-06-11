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

jimport('joomla.application.component.controller');

/**
 * Default controller
 *
 * @package		ITPrism Components
 * @subpackage	CrowdFunding
  */
class CrowdFundingController extends JControllerLegacy {
    
	public function display( ) {

	    $app = JFactory::getApplication();
        /** @var $app JAdministrator **/
        
        $option   = $app->input->getCmd("option");
        
        $document = JFactory::getDocument();
		/** @var $document JDocumentHtml **/
        
        $viewName      = $app->input->getCmd('view', 'dashboard');
        $app->input->set("view", $viewName);

        parent::display();
        return $this;
	}

}