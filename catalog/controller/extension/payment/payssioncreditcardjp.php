<?php

require_once(realpath(dirname(__FILE__)) . "/payssion.php");
class ControllerExtensionPaymentPayssionCreditCardJP extends ControllerExtensionPaymentPayssion {
	protected $pm_id = 'creditcard_jp';
}