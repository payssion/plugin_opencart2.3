<?php

require_once(realpath(dirname(__FILE__)) . "/payssion.php");
class ControllerExtensionPaymentPayssionGrabpaymy extends ControllerExtensionPaymentPayssion {
    protected $pm_id = 'grabpay_my';
}