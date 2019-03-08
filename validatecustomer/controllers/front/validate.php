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

class ValidateCustomerValidateModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();
        if ((version_compare(_PS_VERSION_, '1.5', '>=') >= 1) && (version_compare(_PS_VERSION_, '1.7', '<') >= 1)) {
            $this->setTemplate('validate-1.6.tpl');
        } else {
            $this->setTemplate('module:validatecustomer/views/templates/front/validate.tpl');
        }
    }
}
