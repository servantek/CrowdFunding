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
defined('_JEXEC') or die;?>
<div class="cfunding<?php echo $this->pageclass_sfx;?>">
    <?php if ($this->params->get('show_page_heading', 1)) { ?>
    <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php } ?>
    
    <table class="table table-striped table-bordered cf-projects-list">
    <thead>
    	<tr>
    		<th><?php echo JText::_("COM_CROWDFUNDING_TITLE");?></th>
    		<th><?php echo JText::_("COM_CROWDFUNDING_GOAL");?></th>
    		<th><?php echo JText::_("COM_CROWDFUNDING_FUNDED");?></th>
    		<th><?php echo JText::_("COM_CROWDFUNDING_STARTING_DATE");?></th>
    		<th><?php echo JText::_("COM_CROWDFUNDING_DURATION");?></th>
    		<th><?php echo JText::_("COM_CROWDFUNDING_PUBLISHED");?></th>
    		<th><?php echo JText::_("COM_CROWDFUNDING_APPROVED");?></th>
    		<th>&nbsp;</th>
    	</tr>
    </thead>
    <tfoot></tfoot>
    
    <tbody>
    	<?php foreach($this->items as $item) {
    	    
    		$goal           = JHtml::_("crowdfunding.amount", $item->goal, $this->currency);
    		$funded         = JHtml::_("crowdfunding.amount", $item->funded, $this->currency);
    		$fundedPercent  = JHtml::_("crowdfunding.percents", $item->goal, $item->funded);
    		
    	    // Prepare duration
    	    if(!empty($item->funding_days)) {
    	        $duration = JText::sprintf("COM_CROWDFUNDING_DURATION_DAYS", (int)$item->funding_days);
    	    } else {
    	        $duration = JHtml::_('date', $item->funding_end, JText::_('DATE_FORMAT_LC3'));
    	        $duration = JText::sprintf("COM_CROWDFUNDING_DURATION_END_DATE", $duration);
    	    }
    	    
    	    // Starting Date
    	    $startingDate = "";
    	    $date         = new JDate($item->funding_start);
    	    if(0 < $date->toUnix()) {
    	        $startingDate = JHtml::_('date', $item->funding_start, JText::_('DATE_FORMAT_LC3'));
    	    }
    	    // Reverse state.
    	    $state = ($item->published) ? 0 : 1;
    	?>
    	<tr>
    		<td>
    		<?php if(!$item->approved) {?>
    		<?php echo $this->escape($item->title);?>
    		<?php } else { ?>
    		<a href="<?php echo JRoute::_(CrowdFundingHelperRoute::getDetailsRoute($item->slug, $item->catslug));?>">
    		<?php echo $this->escape($item->title);?>
    		</a>
    		<?php }?>
    		</td>
    		<td class="pull-center"><?php echo $goal; ?></td>
    		<td class="pull-center"><span class="hasTooltip cursor-help" title="<?php echo JText::sprintf("COM_CROWDFUNDING_PERCENTS_FUNDED", $fundedPercent);?>"><?php echo $funded; ?></span></td>
    		<td class="pull-center"><?php echo $startingDate; ?></td>
    		<td class="pull-center"><?php echo $duration; ?></td>
    		<td class="pull-center">
    		<?php echo JHtml::_("crowdfunding.state", $item->published, JRoute::_("index.php?option=com_crowdfunding&task=projects.savestate&id=".$item->id."&state=".$state."&".JSession::getFormToken()."=1"))?>
    		</td>
    		<td class="pull-center">
    		<?php echo JHtml::_("crowdfunding.approved", $item->approved); ?>
    		</td>
    		<td>
    			<a href="<?php echo JRoute::_(CrowdFundingHelperRoute::getFormRoute($item->id)) ;?>" class="button"><i class="icon-pencil icon-white"></i> <?php echo JText::_("COM_CROWDFUNDING_EDIT");?></a>
    		</td>
    	</tr>
    	<?php }?>
    </tbody>
    
    </table>
</div>
<?php echo $this->version->backlink;?>