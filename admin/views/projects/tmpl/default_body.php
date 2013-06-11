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

?>
<?php foreach ($this->items as $i => $item) {
	    $ordering  = ($this->listOrder == 'a.ordering');
	     
	    $disableClassName = '';
	    $disabledLabel	  = '';
	    if (!$this->saveOrder) {
	        $disabledLabel    = JText::_('JORDERINGDISABLED');
	        $disableClassName = 'inactive tip-top';
	    }
	?>
	<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid?>">
	    <td class="order nowrap center hidden-phone">
    		<span class="sortable-handler hasTooltip <?php echo $disableClassName?>" title="<?php echo $disabledLabel?>">
    			<i class="icon-menu"></i>
    		</span>
    		<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering;?>" class="width-20 text-area-order " />
    	</td>
    	<td class="center hidden-phone">
            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
        </td>
        <td class="center">
            <?php echo JHtml::_('jgrid.published', $item->published, $i, "projects."); ?>
        </td>
        
		<td><a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=project&layout=edit&id=".$item->id);?>" ><?php echo $item->title; ?></a></td>
		<td class="center hidden-phone"><?php echo $item->category;?></td>
		<td class="center hidden-phone"><?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC3'));?></td>
		<td class="center hidden-phone"><?php echo $this->currency->getAmountString($item->goal); ?></td>
		<td class="center hidden-phone"><?php echo $this->currency->getAmountString($item->funded);?></td>
		<td class="center hidden-phone"><?php echo $item->funded_percents;?>%</td>
		<td class="center hidden-phone"><?php echo (!(int)$item->funding_start) ? "" : JHtml::_('date', $item->funding_start, JText::_('DATE_FORMAT_LC3'));?></td>
		<td class="center hidden-phone"><?php echo (!(int)$item->funding_end)   ? "" : JHtml::_('date', $item->funding_end, JText::_('DATE_FORMAT_LC3')); ?></td>
		<td class="center hidden-phone"><?php echo (!$item->funding_days)       ? "" : (int)$item->funding_days; ?></td>
        <td class="center">
            <?php echo JHtml::_('crowdfunding.approvedBackend', $i, $item->approved, "projects."); ?>
        </td>
        <td class="center hidden-phone"><?php echo $item->id;?></td>
	</tr>
<?php }?>
	  