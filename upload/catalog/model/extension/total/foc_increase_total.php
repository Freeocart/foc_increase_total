<?php

class ModelExtensionTotalFocIncreaseTotal extends Model {

	const OPTION_VALUE_IS_ANY = -1;

	public function getRules () {
		$rules = $this->config->get('total_foc_increase_total_rules');
		$language_id = $this->config->get('config_language_id');

		if (isset($rules[$language_id])) {
			return json_decode($rules[$language_id], true);
		}

		return array();
	}

	public function checkRuleForProduct ($product, $rule) {
		switch ($rule['type']) {
			case 'option':
				if (empty($product['option']) || empty($rule['value'])) {
					return false;
				}

				$ruleOptionId = (int)$rule['option_id'];
				$ruleValue = is_null($rule['value']) ? null : (int)$rule['value'];
				$skipValueCheck = $ruleValue === self::OPTION_VALUE_IS_ANY;

				foreach ($product['option'] as $option) {
					if ((int)$option['option_id'] === $ruleOptionId) {
						if ($skipValueCheck || (int)$option['option_value_id'] === $ruleValue) {
							return true;
						}
					}
				}
			break;
			case 'language':
				if (!isset($rule['language_id'])) {
					return false;
				}

				$language_id = (int) $this->config->get('config_language_id');
				if ($language_id === $rule['language_id']) {
					return true;
				}
			break;
			case 'currency':
				if (!isset($rule['currency_id'])) {
					return false;
				}

				$this->load->model('localisation/currency');
				$currency_code = $this->session->data['currency'];
				$currency = $this->model_localisation_currency->getCurrencyByCode($currency_code);

				if ((int)$currency['currency_id'] === (int) $rule['currency_id']) {
					return true;
				}
			break;
			case 'countries':
				if (!isset($rule['value']) || !is_array($rule['value'])) {
					return false;
				}

				$country = $this->session->data['shipping_address']['country_id'];

				if (in_array($country, $rule['value'])) {
					return true;
				}
			break;
			case 'attribute':
				if (!isset($rule['attribute_group_id']) || !isset($rule['attribute_id'])) {
					return false;
				}

				$attributesGroups = $this->model_catalog_product->getProductAttributes($product['product_id']);

				if (empty($attributesGroups)) {
					return false;
				}

				foreach ($attributesGroups as $attributeGroup) {
					if ($rule['attribute_group_id'] === $attributeGroup['attribute_group_id']) {
						foreach ($attributeGroup['attribute'] as $attribute) {
							if ($rule['attribute_id'] === $attribute['attribute_id']) {
								if ($rule['check_value']) {
									if ($rule['value'] === $attribute['text']) {
										return true;
									}
									return false;
								}
								return true;
							}
						}
					}
				}
			break;
		}

		return false;
	}

	public function checkRulesForProduct ($product, $rules = array(), $used = array()) {
		$result = array();

		if ($rules === null) {
			return $result;
		}

		foreach ($rules as $id => $config) {
			$use = false;

			foreach ($config['rules'] as $rule) {
				if (!$this->checkRuleForProduct($product, $rule)) {
					$use = false;
					break;
				}
				else {
					$use = true;
				}
			}

			// применяем наценку
			if ($use) {
				if (!$config['once'] || !in_array($id, $used)) {
					$result = array(
						'id' => $id,
						'increase' => $config['increase'],
						'useLabel' => $config['useLabel'],
						'label' => $config['label']
					);
				}
				break;
			}
		}

		return $result;
	}

  public function getTotal($total) {
		$this->load->model('catalog/product');

		$rules = $this->getRules();
		$increaseTotal = 0;
		$used = array();

		$labels = array();

		foreach ($this->cart->getProducts() as $product) {
			$result = $this->checkRulesForProduct($product, $rules, $used);

			if (!empty($result)) {
				$increaseTotal += $result['increase'];

				if ($result['useLabel']) {
					if (!isset($labels[$result['id']])) {
						$labels[$result['id']] = array(
							'code'       => 'foc_increase_total',
							'title'      => $result['label'],
							'value'      => $result['increase'],
							'sort_order' => 10,
						);
					}
					else {
						$labels[$result['id']]['value'] += $result['increase'];
					}
				}

				$used[] = $result['id'];
			}
		}

		foreach ($labels as $label) {
			$total['totals'][] = $label;
		}

		$total['total'] += $increaseTotal;
	}
}