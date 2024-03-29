<?php
class ControllerExtensionPaymentPayssion extends Controller {
	protected $pm_id = '';
	protected $template = 'payssion';
	
	public function index() {
		$data['button_confirm'] = $this->language->get('button_confirm');

		if (!$this->config->get('payssion_test')) {
			$data['action'] = 'https://www.payssion.com/payment/create.html';
		} else {
			$data['action'] = 'http://sandbox.payssion.com/payment/create.html';
		}

		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$data = array_merge($data, $this->fillFormData($order_info));
		
		return $this->load->view('extension/payment/' . $this->template . '.tpl', $data);
	}
	
	protected function fillFormData($order_info) {
	    $data['source'] = 'opencart';
	    $data['pm_id'] = $this->pm_id;
	    $data['api_key'] = $this->config->get('payssion_apikey');
	    $data['track_id'] = $order_info['order_id'];
	    $data['amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
	    $data['currency'] = $order_info['currency_code'];
	    $data['description'] = $this->config->get('config_name') . ' - #' . $order_info['order_id'];
	    $data['payer_name'] = $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];
	    
	    // 		if (!$order_info['payment_address_2']) {
	    // 			$data['address'] = $order_info['payment_address_1'] . ', ' . $order_info['payment_city'] . ', ' . $order_info['payment_zone'];
	    // 		} else {
	    // 			$data['address'] = $order_info['payment_address_1'] . ', ' . $order_info['payment_address_2'] . ', ' . $order_info['payment_city'] . ', ' . $order_info['payment_zone'];
	    // 		}
	    
	    //$data['postcode'] = $order_info['payment_postcode'];
	    $data['country'] = $order_info['payment_iso_code_2'];
	    $data['payer_phone'] = $order_info['telephone'];
	    $data['payer_email'] = $order_info['email'];
	    
	    $data['notify_url'] = $this->url->link('extension/payment/payssion/notify', '', true);
	    $data['success_url'] = $this->url->link('checkout/success', '', true);
	    $data['return_url'] = $this->url->link('checkout/checkout', '', true);
	    
	    $data['api_sig'] = $this->generateSignature($data, $this->config->get('payssion_secretkey'));
	    
	    return $data;
	}
	
	private function generateSignature(&$req, $secretKey) {
		$arr = array($req['api_key'], $req['pm_id'], $req['amount'], $req['currency'],
				$req['track_id'], '', $secretKey);
		$msg = implode('|', $arr);
		return md5($msg);
	}

	public function notify() {
		$track_id = $this->request->post['track_id'];
		$this->load->model('checkout/order');
		if ($this->isValidNotify()) {
			if (!$this->request->server['HTTPS']) {
				$data['base'] = $this->config->get('config_url');
			} else {
				$data['base'] = $this->config->get('config_ssl');
			}
			
			$state = $this->request->post['state'];
			$message = '';
				
			if (isset($this->request->post['track_id'])) {
				$message .= 'track_id: ' . $this->request->post['track_id'] . "\n";
			}
				
			if (isset($this->request->post['pm_id'])) {
				$message .= 'pm_id: ' . $this->request->post['pm_id'] . "\n";
			}
			
			if (isset($this->request->post['state'])) {
				$message .= 'state: ' . $this->request->post['state'] . "\n";
			}
				
			if (isset($this->request->post['amount'])) {
				$message .= 'amount: ' . $this->request->post['amount'] . "\n";
			}
				
			if (isset($this->request->post['paid'])) {
				$message .= 'paid: ' . $this->request->post['paid'] . "\n";
			}
				
			if (isset($this->request->post['currency'])) {
				$message .= 'currency: ' . $this->request->post['currency'] . "\n";
			}
				
			if (isset($this->request->post['notify_sig'])) {
				$message .= 'notify_sig: ' . $this->request->post['notify_sig'] . "\n";
			}
				
			
			$status_list = array(
					'completed' => $this->config->get('payssion_order_status_id'),
					'pending' => $this->config->get('payssion_pending_status_id'),
					'expired' => $this->config->get('payssion_expired_status_id'),
					'cancelled_by_user' => $this->config->get('payssion_canceled_status_id'),
					'cancelled' => $this->config->get('payssion_canceled_status_id'),
					'rejected_by_bank' => $this->config->get('payssion_canceled_status_id'),
					'failed' => $this->config->get('payssion_failed_status_id'),
					'error' => $this->config->get('payssion_failed_status_id')
			);
				
			$this->model_checkout_order->addOrderHistory($track_id, $status_list[$state], $message);
			$this->response->setOutput('success');
			
		} else {
			$this->model_checkout_order->addOrderHistory($track_id, $this->config->get('config_order_status_id'), $this->language->get('text_pw_mismatch'));
			$this->response->setOutput('verify failed');
		}

	}
	
	public function isValidNotify() {
		$apiKey = $this->config->get('payssion_apikey');;
		$secretKey = $this->config->get('payssion_secretkey');
	
		// Assign payment notification values to local variables
		$pm_id = $this->request->post['pm_id'];
		$amount = $this->request->post['amount'];
		$currency = $this->request->post['currency'];
		$track_id = $this->request->post['track_id'];
		$sub_track_id = $this->request->post['sub_track_id'];
		$state = $this->request->post['state'];
	
		$check_array = array(
				$apiKey,
				$pm_id,
				$amount,
				$currency,
				$track_id,
				$sub_track_id,
				$state,
				$secretKey
		);
		$check_msg = implode('|', $check_array);
		$check_sig = md5($check_msg);
		$notify_sig = $this->request->post['notify_sig'];
		return ($notify_sig == $check_sig);
	}
}