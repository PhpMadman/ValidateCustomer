<?php

if(!defined('_PS_VERSION_'))
	exit;

class ValidateCustomer extends Module
{
// 	private $customer_status_before = 0;
	public function  __construct()
	{
			$this->name = 'validatecustomer';
			$this->tab = 'administration';
			$this->version = '1.2';
			$this->author = 'Madman';
			$this->need_instance = 0;
			/* Code also from
				$this->name = 'customerupdatenotification';
				$this->tab = 'administration';
				$this->version = '1.2';
				$this->author = 'Anila Niaz';
			*/

// 		$this->ps_version_compliancy = array('min' => '1.5');

		parent::__construct();

			$this->displayName = $this->l('Validate Customer');
			$this->description = $this->l('Validate customer before they can login');


// 		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

// 		if(!Configuration::get('VALIDATECUSTOMER_NAME'))
// 			$this->warning = $this->l('No name provided');
	}

	public function install()
	{
		if(Shop::isFeatureActive())
			Shop::setContext(Shop::CONTEXT_ALL);
		return (parent::install()) &&
// 			$this->registerHook('actionObjectCustomerUpdateAfter') &&
// 			$this->registerHook('actionObjectCustomerUpdateBefore') &&
			$this->registerHook('actionCustomerAccountAdd') &&
			$this->registerHook('displayCustomerAccountForm');
	}

// 	public function uninstall()
// 	{
// 		return parent::uninstall();
// 	}
//
// 	public function hookActionObjectCustomerUpdateBefore($params)
// 	{
// 			$data = parse_str($_SERVER['HTTP_REFERER']);
// 	 		$customer_id = $id_customer;
// 	 		$customer = new Customer($customer_id);
// 			$this->customer_status_before = $customer->active;
// 	}
//
// 	public function hookActionObjectCustomerUpdateAfter($params)
// 	{
// 		$data = parse_str($_SERVER['HTTP_REFERER']);
//  		$customer_id = $id_customer;
//  		$customer = new Customer($customer_id);
// 		$customer_status_after = strval(Tools::getValue('active'));
//
// 		if($this->customer_status_before != $customer_status_after && $customer_status_after === '1')
// 		{
// 			/*echo '<script type="text/javascript">';
// 			echo 'console.log(Name:'.dirname(__FILE__).'/mails/)';
// 			echo '</script>';*/
// 			Mail::Send(
// 					$this->context->language->id,
// 					'account_activated',
// 					Mail::l('Your account has been activated.', $this->context->language->id),
// 					array('{email}' => $customer->email, '{firstname}' => $customer->firstname, '{lastname}' => $customer->lastname, '{shopname}' => $this->context->shop->name,),
// 					$customer->email,
// 					$customer->lastname,
// 					NULL,
// 					$this->context->shop->name,
// 					NULL,
// 					NULL,
// 					dirname(__FILE__).'/mails/'
//
// 			);
// 		}
// 	}

	public function hookActionCustomerAccountAdd() {
		$this->context->customer->mylogout();
		$id_customer =$this->context->customer->id;
		// set status to disabled
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		UPDATE `'._DB_PREFIX_.'customer`
		SET active = 0
		WHERE `id_customer` = \''.(int)$id_customer.'\'');
// 		Tools::redirect(__PS_BASE_URI__.'index.php?fc=module&module=' . $this->name . '&controller=validate');
// 	Tools::redirect(__PS_BASE_URI__.'module/validatecustomer/validate');
	Tools::redirect('http://www.nldistribution.se/module/validatecustomer/validate');

	}

	public function hookDisplayCustomerAccountForm() {
		return $this->display(__FILE__,'needactivation.tpl');
	}
}
?>