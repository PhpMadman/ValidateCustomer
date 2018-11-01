<?php

if(!defined('_PS_VERSION_'))
	exit;

class ValidateCustomer extends Module
{
	public function  __construct()
	{
		$this->name = 'validatecustomer';
		$this->tab = 'administration';
		$this->version = '1.3';
		$this->author = 'Madman';
		$this->need_instance = 0;

		$this->validate_customer = array();
		$this->send_mail = true;

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
		$this->context->customer->mylogout();
		$customer = new Customer($this->context->customer->id);
		$customer->active = 0;
		$customer->update();

		Db::getInstance()->insert('customer_validate', array('id_customer' => $customer->id));

		Tools::redirect(__PS_BASE_URI__.'module/validatecustomer/validate');
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
				$this->validate_customer[$customer->id] = true; // save state in array
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

				if ($this->send_mail)
					// Send mail
					Mail::Send(
						$customer->id_lang,
						'account_activated',
						Mail::l('Your account has been activated.', $customer->id_lang),
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
