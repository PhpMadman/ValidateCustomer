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

if (!defined('_PS_VERSION_')) {
    exit;
}

class ValidateCustomer extends Module
{
    public function __construct()
    {
        $this->name = 'validatecustomer';
        $this->tab = 'administration';
        $this->version = '1.6.1';
        $this->author = 'Madman';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->validate_customer = array();
        parent::__construct();
        $this->displayName = $this->l('Validate Customer');
        $this->description = $this->l('Validate customer before they can login');
        $this->module_key = 'e7920a6194d2ac3fb2ded8736be03a06';
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        return ( parent::install() &&
            $this->registerHook('actionCustomerAccountAdd') &&
            $this->registerHook('displayCustomerAccountForm') &&
            $this->registerHook('actionObjectCustomerUpdateBefore') &&
            $this->registerHook('actionObjectCustomerUpdateAfter') &&
            $this->installDB() &&
            Configuration::updateValue('PS_MOD_VALCUS_GROUPS', 3)
        );
    }

    public function getContent()
    {
        if (Tools::isSubmit('submitUpdateConfig')) {
            $this->updateConfig();
        }
        return $this->renderSettingsForm();
    }

    private function updateConfig()
    {
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $groups = Group::getGroups($lang->id);
        // Remove Visitor and Guest groups
        unset($groups[0]);
        unset($groups[1]);

        $id_group = array();
        foreach ($groups as $group) {
            if (Tools::getValue('PS_MOD_VALCUS_GROUPS_'.$group['id_group'])) {
                $id_group[] = $group['id_group'];
            }
        }

        Configuration::updateValue('PS_MOD_VALCUS_GROUPS', implode(',', $id_group));
        Configuration::updateValue('PS_MOD_VALCUS_SENDMAIL', Tools::getValue('PS_MOD_VALCUS_SENDMAIL'));
        Configuration::updateValue('PS_MOD_VALCUS_SEND_REGMAIL', Tools::getValue('PS_MOD_VALCUS_SEND_REGMAIL'));
        Configuration::updateValue('PS_MOD_VALCUS_EMAILS', Tools::getValue('PS_MOD_VALCUS_EMAILS'));
    }

    public function renderSettingsForm()
    {

        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $groups = Group::getGroups($lang->id);
        // Remove Visitor and Guest groups
        unset($groups[0]);
        unset($groups[1]);

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                ),
                'input' => array(
                    array(
                        'type' => $this->getSwtichType(),
                        'class' => 't',
                        'label' => $this->l('Send new registration mail'),
                        'name' => 'PS_MOD_VALCUS_SEND_REGMAIL',
                        'is_bool' => true,
                        'hint' => $this->l('Enable sending an e-mail when a customer reg them self'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'PS_MOD_VALCUS_EMAILS',
                        'label' => $this->l('Send email to the following addresses'),
                        'hint' => $this->l('Comma separated list'),
                        'size' => 100,
                    ),
                    array(
                        'type' => $this->getSwtichType(),
                        'class' => 't',
                        'label' => $this->l('Auto send mail when account activated'),
                        'name' => 'PS_MOD_VALCUS_SENDMAIL',
                        'is_bool' => true,
                        'hint' => $this->l('Inform the customer their account has been activated'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'checkbox',
                        'name' => 'PS_MOD_VALCUS_GROUPS',
                        'label' => $this->l('Customer groups that requires validation'),
                        'hint' => $this->l('Selected groups cannot login until manually activated'),
                        'values' => array(
                            'query' => $groups,
                            'id' => 'id_group',
                            'name' => 'name'
                        ),
                    ),
            ), // input
            'submit' => array(
                'title' => $this->l('Save'),
                ),
            ),
        );

        if (!$this->is16()) {
            foreach ($fields_form as &$form) {
                foreach ($form as $key => &$table) {
                    if ($key == 'input') {
                        foreach ($table as &$cfg) {
                            $cfg['desc'] = $cfg['hint'];
                            unset($cfg['hint']);
                        }
                    }
                }
            }
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = (Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?
            Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0);
        $this->fields_form = array();
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitUpdateConfig';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.
            '&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        $cfg_fields = array(
            'PS_MOD_VALCUS_SENDMAIL' => Configuration::get('PS_MOD_VALCUS_SENDMAIL'),
            'PS_MOD_VALCUS_SEND_REGMAIL' => Configuration::get('PS_MOD_VALCUS_SEND_REGMAIL'),
            'PS_MOD_VALCUS_EMAILS' => Configuration::get('PS_MOD_VALCUS_EMAILS'),
        );

        // get groups from config
        $id_group_config = array();
        $cfgs = array();
        if ($cfg = Configuration::get('PS_MOD_VALCUS_GROUPS')) {
            $cfgs = explode(',', $cfg);
        }

        foreach ($cfgs as $cfg) {
            $id_group_config['PS_MOD_VALCUS_GROUPS_'.$cfg] = true;
        }

        $cfg_fields = array_merge($cfg_fields, $id_group_config);

        return $cfg_fields;
    }

    private function getSwtichType()
    {
        if ($this->is16()) {
            return 'switch';
        } else {
            return 'radio';
        }
    }

    private function is16($not17 = false)
    {
        // Version is higher or equal to 1.6
        if (version_compare(_PS_VERSION_, '1.6', '>=') >= 1) {
            if ($not17) {
                // Version is higher or equal to 1.7 and there for is not 1.6
                if (version_compare(_PS_VERSION_, '1.7', '>=') >= 1) {
                    return false;
                }
                return true; // version was less then 1.7
            }
            return true; // version was higher then 1.6
        }
        return false;
    }

    private function installDB()
    {
        $installedDb = Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'customer_validate` (
            `id_customer` int(10) NOT NULL,
            `validate` int(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id_customer`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
        if (!$installedDb) {
            return false;
        } else {
            return true;
        }
    }

    public function hookActionCustomerAccountAdd()
    {
        // This is the hook that actually disables the customer
        $customer = new Customer($this->context->customer->id);

        $groups = Configuration::get('PS_MOD_VALCUS_GROUPS');
        $groups = explode(',', $groups);
        $needValidation = false;
        foreach ($groups as $id) {
            if ($customer->id_default_group == $id) {
                $needValidation = true;
                break;
            }
        }

        if ($needValidation) {
            $this->context->customer->mylogout();
            $customer->active = 0;
            $customer->update();
            if (Validate::isUnsignedInt($customer->id)) {
                Db::getInstance()->insert('customer_validate', array('id_customer' => (int)$customer->id));
                if (Configuration::get('PS_MOD_VALCUS_SEND_REGMAIL')) {
                    $emails = explode(',', Configuration::get('PS_MOD_VALCUS_EMAILS'));
                    foreach ($emails as $reg_email) {
                        // Send mail
                        Mail::Send(
                            Configuration::get('PS_LANG_DEFAULT'),
                            'new_reg',
                            Mail::l('A new customers has registered', Configuration::get('PS_LANG_DEFAULT')),
                            array('{email}' => $customer->email,
                            '{shopname}' => $this->context->shop->name),
                            $reg_email,
                            $this->context->shop->name,
                            null,
                            $this->context->shop->name,
                            null,
                            null,
                            dirname(__FILE__).'/mails/'
                        );
                    }
                }
                Tools::redirect(Context::getContext()->link->getModuleLink('validatecustomer', 'validate'));
            } else {
                 $this->errors[] = $this->trans('Could not update customer', array(), 'Module.ValidateCustomer.Shop');
            }
        }
    }

    public function hookDisplayCustomerAccountForm()
    {
        return $this->display(__FILE__, 'needactivation.tpl');
    }

    public function hookActionObjectCustomerUpdateBefore($params)
    {
        $customer = $params['object']; // Get customer object
        // check if customer has been validated
        if (Validate::isUnsignedInt($customer->id)) {
            $validate = Db::getInstance()->getValue('SELECT validate
            FROM `'._DB_PREFIX_.'customer_validate`
            WHERE `id_customer` = '.(int)$customer->id);
            if ($validate != 1) { // if not validated
                $customer_db = new Customer($customer->id);
                // customer has to be loaded again, beacuse object already has changed the active status.
                if ($customer_db->active != 1) { // and if account is disabled before update
                    $this->validate_customer[$customer->id] = true; // save state in array
                }
            }
        } else {
            // This call is only multiline to avoid PSR-2 120 char limit
            $this->errors[] = $this->trans(
                'Could not read customer validation',
                array(),
                'Module.ValidateCustomer.Shop'
            );
        }
    }

    public function hookActionObjectCustomerUpdateAfter($params)
    {
        $customer = $params['object'];
        if (Validate::isUnsignedInt($customer->id)) {
            if (isset($this->validate_customer[$customer->id])) { // if customer is in array
                $customer_db = new Customer($customer->id); // do not check object customer, check in database
                if ($customer_db->active == 1) { // and customer is active after update
                    // check if customer is in DB
                    $id_customer = Db::getInstance()->getValue(
                        'SELECT id_customer
                        FROM `'._DB_PREFIX_.'customer_validate`
                        WHERE `id_customer` = '.(int)$customer->id
                    );
                    if ($id_customer > 0) {// if id higher then 0, then update
                        Db::getInstance()->update(
                            'customer_validate',
                            array('validate' => 1),
                            '`id_customer` = '.(int)$customer->id
                        );
                    } else {// customer is not in table, insert customer
                        Db::getInstance()->insert(
                            'customer_validate',
                            array('id_customer' => (int)$customer->id,
                            'validate' => 1)
                        );
                    }

                    if (Configuration::get('PS_MOD_VALCUS_SENDMAIL')) {
                        // Send mail
                        Mail::Send(
                            $customer->id_lang,
                            'account_activated',
                            Mail::l('Your account has been activated', $customer->id_lang),
                            array('{email}' => $customer->email, '{firstname}' => $customer->firstname,
                                '{lastname}' => $customer->lastname, '{shopname}' => $this->context->shop->name),
                            $customer->email,
                            $customer->lastname,
                            null,
                            $this->context->shop->name,
                            null,
                            null,
                            dirname(__FILE__).'/mails/'
                        );
                    }
                    unset($this->validate_customer[$customer->id]);
                }
            }
        } else {
            $this->errors[] = $this->trans('Could not read customer', array(), 'Module.ValidateCustomer.Shop');
        }
    }
}
