<?php

class ControllerExtensionTotalFocIncreaseTotal extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/total/foc_increase_total');

		$this->document->setTitle($this->language->get('heading_title'));

    $this->load->model('setting/setting');
    $this->load->model('extension/total/foc_increase_total');

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			// hack: prevent cleaning json data
			if (isset($this->request->post['total_foc_increase_total_rules'])) {
				$this->request->post['total_foc_increase_total_rules'] = $_POST['total_foc_increase_total_rules'];
			}

			$this->model_setting_setting->editSetting('total_foc_increase_total', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/total/foc_increase_total', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/total/foc_increase_total', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total', true);

		if (isset($this->request->post['total_foc_increase_total_status'])) {
			$data['total_foc_increase_total_status'] = $this->request->post['total_foc_increase_total_status'];
		} else {
			$data['total_foc_increase_total_status'] = $this->config->get('total_foc_increase_total_status');
		}

		if (isset($this->request->post['total_foc_increase_total_sort_order'])) {
			$data['total_foc_increase_total_sort_order'] = $this->request->post['total_foc_increase_total_sort_order'];
		} else {
			$data['total_foc_increase_total_sort_order'] = $this->config->get('total_foc_increase_total_sort_order');
		}

		if (isset($this->request->post['total_foc_increase_total_rules'])) {
			$data['total_foc_increase_total_rules'] = $this->request->post['total_foc_increase_total_rules'];
		}
		else {
			$data['total_foc_increase_total_rules'] = $this->config->get('total_foc_increase_total_rules');
		}

		$this->load->model('localisation/language');
		$this->load->model('localisation/currency');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		$data['language_id'] = $this->config->get('config_language_id');
		$data['language_code'] = $this->config->get('config_language');
		$data['ocInfo'] = array();

		$currencies_list = $this->model_localisation_currency->getCurrencies();
		$currencies = array_values($currencies_list);

		$currency = $this->config->get('config_currency');

		$currency_symbol = $this->currency->getSymbolRight($currency);
		if (empty($currency_symbol)) {
			$currency_symbol = $this->currency->getSymbolLeft($currency);
		}

		foreach ($data['languages'] as $language) {
			$data['ocInfo'][$language['language_id']] = array(
				'languages' => array_values($data['languages']),
				'options' => $this->model_extension_total_foc_increase_total->getOptionsList($language['language_id']),
				'optionsValues' => $this->model_extension_total_foc_increase_total->getOptionsValuesList($language['language_id']),
				'currencies' => $currencies,
				'currencySymbol' => $currency_symbol
			);
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->document->addScript('view/javascript/foc_increase_total/runtime-main.js');
		$this->document->addScript('view/javascript/foc_increase_total/main.js');
		$this->document->addScript('view/javascript/foc_increase_total/2.js');
		$this->document->addStyle('view/stylesheet/foc_increase_total/main.css');

		$data['scripts'] = $this->document->getScripts();
		$data['styles'] = $this->document->getStyles();

		$this->response->setOutput($this->load->view('extension/total/foc_increase_total', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/total/foc_increase_total')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}