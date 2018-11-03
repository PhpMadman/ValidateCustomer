<?php
class validatecustomervalidateModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();
        if ((version_compare(_PS_VERSION_, '1.6', '>=') >= 1) && (version_compare(_PS_VERSION_, '1.7', '<') >= 1) )
        {
            $this->setTemplate('validate.tpl');
        }
        else
        {
            $this->setTemplate('module:validatecustomer/views/templates/front/validate.tpl');
        }
    }
}
