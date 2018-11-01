<?php

if(!defined('_PS_VERSION_'))
	exit;

class ValidateCustomer extends Module
{
	public function  __construct()
	{
		$this->name = 'validatecustomer';
		$this->tab = 'administration';
		$this->version = '1.5';
		$this->author = 'Madman';
		$this->need_instance = 0;
		$this->bootstrap = true;

		$this->validate_customer = array();

		parent::__construct();

		$this->displayName = $this->l('Validate Customer');
		$this->description = $this->l('Validate customer before they can login');

	}

	public function install()
	{
		if(Shop::isFeatureActive())
			Shop::setContext(Shop::CONTEXT_ALL);

		return ( parent::install() &&
			$this->registerHook('actionCustomerAccountAdd') &&
			$this->registerHook('displayCustomerAccountForm') &&
			$this->registerHook('actionObjectCustomerUpdateBefore') &&
			$this->registerHook('actionObjectCustomerUpdateAfter') &&
			$this->installDB()
		);
	}
	
	public function getContent()
	{
		if (Tools::isSubmit('submitUpdateConfig'))
			$this->_updateConfig();
		
		return $this->renderSettingsForm();
	}
	
	private function _updateConfig()
	{
		Configuration::updateValue('PS_MOD_VALCUS_SENDMAIL',Tools::getValue('PS_MOD_VALCUS_SENDMAIL'));
		Configuration::updateValue('PS_MOD_VALCUS_SEND_REGMAIL',Tools::getValue('PS_MOD_VALCUS_SEND_REGMAIL'));
		Configuration::updateValue('PS_MOD_VALCUS_EMAILS',Tools::getValue('PS_MOD_VALCUS_EMAILS'));	
	}
	
	public function renderSettingsForm()
	{
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Settings'),
				),
				'input' => array(
					array(
						'type' => $this->_getSwtichType(),
						'class' => 't',
						'label' => $this->l('Send new registration mail'),
						'name' => 'PS_MOD_VALCUS_SEND_REGMAIL',
						'is_bool' => true,
						'hint' => $this->l('Enable sending an e-mail when a customer reg them self'),
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Enabled')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('Disabled')
							),
						),
					),
					array(
						'type' => 'text',
						'name' => 'PS_MOD_VALCUS_EMAILS',
						'label' => $this->l('Send email to the following addresses'),
						'hint' => $this->l('Coma seperated list'),
						'size' => 100,
					),
					array(
						'type' => $this->_getSwtichType(),
						'class' => 't',
						'label' => $this->l('Auto send mail when account activated'),
						'name' => 'PS_MOD_VALCUS_SENDMAIL',
						'is_bool' => true,
						'hint' => $this->l('Inform the customer there account has been activated'),
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Enabled')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('Disabled')
							),
						),
					),
				),
				'submit' => array(
					'title' => $this->l('Save'),
				),
			)
		);

		if (!$this->_is16())
			foreach ($fields_form as &$form)
				foreach ($form as $key => &$table)
					if ($key == 'input')
						foreach ($table as &$cfg)
						{
							$cfg['desc'] = $cfg['hint'];
							unset($cfg['hint']);
						}

		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitUpdateConfig';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
			.'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
	}

	public function getConfigFieldsValues()
	{
		return array(
			'PS_MOD_VALCUS_SENDMAIL' => Configuration::get('PS_MOD_VALCUS_SENDMAIL'),
			'PS_MOD_VALCUS_SEND_REGMAIL' => Configuration::get('PS_MOD_VALCUS_SEND_REGMAIL'),
			'PS_MOD_VALCUS_EMAILS' => Configuration::get('PS_MOD_VALCUS_EMAILS'),
		);
	}

	private function _getSwtichType()
	{
		if ($this->_is16())
			return 'switch';
		else
			return 'radio';
	}

	private function _is16()
	{
		if (version_compare(_PS_VERSION_, '1.6', '>=') >= 1)
			return true;

		return false;
	}

	private function installDB()
	{
		return Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'customer_validate` (
		`id_customer` int(10) NOT NULL,
		`validate` int(1) NOT NULL DEFAULT 0,
		PRIMARY KEY (`id_customer`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
	}

	public function hookActionCustomerAccountAdd()
	{
        // This is the hook that actually disables customer
            $customer = new Customer($this->context->customer->id);
            if ($customer->is_guest)
            {
                // Do nothing
            }
            else
            {
                $this->context->customer->mylogout();
                $customer->active = 0;
                $customer->update();

                Db::getInstance()->insert('customer_validate', array('id_customer' => $customer->id));

                if (Configuration::get('PS_MOD_VALCUS_SEND_REGMAIL'))
                {
                PrestaShopLogger::addLog('VALCUS - Reg Mail On');
                $emails = explode(',', Configuration::get('PS_MOD_VALCUS_EMAILS'));
                PrestaShopLogger::addLog('E-mails: '.Configuration::get('PS_MOD_VALCUS_EMAILS');
                foreach ($emails as $reg_email)
                {
                    PrestaShopLogger::addLog('VALCUS - forreach mail');
                    // Send mail
                    Mail::Send(
                        Configuration::get('PS_LANG_DEFAULT'),
                        'new_reg',
                        Mail::l('A new customers has registered', Configuration::get('PS_LANG_DEFAULT')),
                        array('{email}' => $customer->email,
                            '{shopname}' => $this->context->shop->name),
                        $reg_email,
                        $this->context->shop->name,
                        NULL,
                        $this->context->shop->name,
                        NULL,
                        NULL,
                        dirname(__FILE__).'/mails/'
                        );
                        PrestaShopLogger::addLog('VALCUS - Mail sent');
                    }
                }
            Tools::redirect('?fc=module&module=validatecustomer&controller=validate');
            }
	}

	public function hookDisplayCustomerAccountForm()
	{
		return $this->display(__FILE__,'needactivation.tpl');
	}

	public function hookActionObjectCustomerUpdateBefore($params)
	{
		$customer = $params['object']; // Get customer object
		 // check if customer has been validated
		$validate = Db::getInstance()->getValue('SELECT validate
			FROM `'._DB_PREFIX_.'customer_validate`
			WHERE `id_customer` = '.$customer->id);
		if ($validate != 1) // if not validated
		{
			$customer_db = new Customer($customer->id); // customer has to be loaded again, beacuse object already has changed the active status.
			if ($customer_db->active != 1) // and if account is disabled before update
			{
				$this->validate_customer[$customer->id] = true; // save state in array
                        }
		}
	}

    public function hookActionObjectCustomerUpdateAfter($params)
    {
        $customer = $params['object'];
        if (isset($this->validate_customer[$customer->id])) // if customer is in array
        {
            $customer_db = new Customer($customer->id); // do not check object customer, check in database
            if ($customer_db->active == 1) // and customer is active after update
            {
                // check if customer is in DB
                $id_customer = Db::getInstance()->getValue('SELECT id_customer
                        FROM `'._DB_PREFIX_.'customer_validate`
                        WHERE `id_customer` = '.$customer->id);
                if ($id_customer > 0) // if id higher then 0, then update
                        Db::getInstance()->update('customer_validate', array('validate' => 1), '`id_customer` = '.$customer->id);
                else // customer is not in table, insert customer
                        Db::getInstance()->insert('customer_validate', array('id_customer' => $customer->id, 'validate' => 1));

                if (Configuration::get('PS_MOD_VALCUS_SENDMAIL'))
                        // Send mail
                        Mail::Send(
                                $customer->id_lang,
                                'account_activated',
                                Mail::l('Your account has been activated', $customer->id_lang),
                                array('{email}' => $customer->email, '{firstname}' => $customer->firstname, '{lastname}' => $customer->lastname, '{shopname}' => $this->context->shop->name),
                                $customer->email,
                                $customer->lastname,
                                NULL,
                                $this->context->shop->name,
                                NULL,
                                NULL,
                                dirname(__FILE__).'/mails/'
                        );
                unset($this->validate_customer[$customer->id]);
            }
        }
    }
}
?>
