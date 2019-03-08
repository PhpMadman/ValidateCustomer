<?php
/**
* 2019 Madman
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
*  @author    Madman
*  @copyright 2019 Madman
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

function upgrade_module_1_6_0()
{
    Configuration::updateValue('PS_MOD_VALCUS_GROUPS', 3);
    return true; // Return true if success.
}
