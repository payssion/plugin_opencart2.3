<?php

require_once(realpath(dirname(__FILE__)) . "/payssion.php");
class ControllerExtensionPaymentPayssionEbankingkr extends ControllerExtensionPaymentPayssion {
    protected $pm_id = 'ebanking_kr';
}