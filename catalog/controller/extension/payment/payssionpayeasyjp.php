<?php

require_once(realpath(dirname(__FILE__)) . "/payssion.php");
class ControllerExtensionPaymentPayssionPayeasyJP extends ControllerExtensionPaymentPayssion {
	protected $pm_id = 'payeasy_jp';
}