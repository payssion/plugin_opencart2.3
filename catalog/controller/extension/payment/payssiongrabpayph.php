<?php

require_once(realpath(dirname(__FILE__)) . "/payssion.php");
class ControllerExtensionPaymentPayssionGrabpayph extends ControllerExtensionPaymentPayssion {
    protected $pm_id = 'grabpay_ph';
}