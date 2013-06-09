<?php
/**
 * Sea Lab LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mygento
 * @package    Mygento_Stats
 * @copyright  Copyright © 2011 Sea Lab LLC.
 */
class Mygento_Stats_Helper_Data extends Mage_Core_Helper_Abstract
{
	
	
	public function AddLog($text)
	{
		if(Mage::getStoreConfig('mystats/general/debug'))
		{
			Mage::log($text);
		}
	}
	
}