{*
* 2018 Madman
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
*  @copyright 2018 Madman
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*}
{if !$customer.is_logged}
    <p>
    <strong class="exclusive">
    {l s='Your account might need to be approved by an admin before you can login after registering' mod='validatecustomer'}
    </strong><br>
    </p>
{/if}
