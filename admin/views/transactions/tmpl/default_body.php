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
	?>
	<tr class="row<?php echo $i % 2; ?>">
	    <td class="center hidden-phone">
            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
        </td>
		<td><a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=transaction&layout=edit&id=".$item->id);?>"><?php echo $item->beneficiary; ?></a></td>
		<td class="center hidden-phone"><?php echo $item->sender; ?></td>
		<td class="center hidden-phone">
			<a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=transactions&filter_search=pid:".$item->project_id);?>">
		    <?php echo JHtmlString::truncate(strip_tags($item->project), 53); ?>
		    </a>
	    </td>
		<td class="center"><?php 
		$currency = JArrayHelper::getValue($this->currencies, $item->txn_currency);
		if(!empty($currency)) {
		    echo JHtml::_("crowdfunding.amount", $item->txn_amount, $currency);
		} else {
		    echo $item->txn_amount;
		}
		?></td>
		<td class="center hidden-phone"><?php echo $item->txn_date; ?></td>
		<td class="center hidden-phone"><?php echo $item->service_provider; ?></td>
		<td class="center hidden-phone"><?php echo $item->txn_id; ?>
		<td class="center hidden-phone"><?php echo $item->txn_status; ?></td>
		<td class="center hidden-phone">
		<?php if(!$item->reward_id) { ?>
		<img src="../media/com_crowdfunding/images/noreward_16.png" alt="<?php echo JText::_('COM_CROWDFUNDING_REWARD_NOT_SELECTED'); ?>" width="16" height="16"/>
		<?php } else {?>
		<img src="../media/com_crowdfunding/images/reward_16.png" alt="<?php echo JText::_('COM_CROWDFUNDING_REWARD_SELECTED'); ?>" title="<?php echo JText::sprintf('COM_CROWDFUNDING_REWARD_TOOLTIP', $item->reward); ?>" width="16" height="16" class="hasTip" />
		<?php }?>
		</td>
        <td class="center hidden-phone"><?php echo $item->id;?></td>
	</tr>
<?php }?>
	  