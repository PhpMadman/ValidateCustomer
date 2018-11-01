<?php
class ValidateCustomerValidateModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();
        $this->setTemplate('module:validatecustomer/views/templates/front/validate.tpl');
    }
}
