<?php

require_once(realpath(dirname(__FILE__)) . "/payssion.php");
class ControllerExtensionPaymentPayssionDokuid extends ControllerExtensionPaymentPayssion {
    protected $pm_id = 'doku_id';
}