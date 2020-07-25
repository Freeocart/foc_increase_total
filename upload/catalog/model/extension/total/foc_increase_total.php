<?php

class ModelExtensionTotalFocIncreaseTotal extends Model {

	const OPTION_VALUE_IS_ANY = -1;

	/*
		How total will be calculated
	*/
	const RULES_TOTAL_SET_MAX_INCREASE_VALUE = 0;
	const RULES_TOTAL_SET_MIN_INCREASE_VALUE = 1;
	const RULES_TOTAL_SUM_INCREASE_VALUES = 2;

	/*
		Init vars
	*/
	public function __construct ($registry) {
		parent::__construct($registry);
		$this->_config = $this->__initConfig();
		$this->_validTotalModes = [
			self::RULES_TOTAL_SET_MAX_INCREASE_VALUE,
			self::RULES_TOTAL_SET_MIN_INCREASE_VALUE,
			self::RULES_TOTAL_SUM_INCREASE_VALUES
		];
		$this->_defaultTotalMode = self::RULES_TOTAL_SET_MAX_INCREASE_VALUE;
	}

	/*
		Load config object
	*/
	public function __initConfig () {
		$raw = $this->config->get('total_foc_increase_total_rules');
		$language_id = $this->config->get('config_language_id');

		if (isset($raw[$language_id])) {
			return json_decode($raw[$language_id], true);
		}

		return [];
	}

	/*
		Get rules object
		{
			objID: {
				rule
			}
		}
	*/
	public function getRulesets () {
		if (isset($this->_config['rulesets']) && is_array($this->_config['rulesets'])) {
			return $this->_config['rulesets'];
		}

		return [];
	}

	/*
		Get totalMode int
	*/
	public function getTotalCalculationMode () {
		if (isset($this->_config['totalMode'])) {
			$totalMode = $this->_config['totalMode'];

			if (in_array($totalMode, $this->_validTotalModes)) {
				return (int)$totalMode;
			}
		}

		return $this->_defaultTotalMode;
	}

	/*
		Check if rule valid for product
	*/
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

	/*
		Check all existing rules for product
	*/
	public function checkValidRulesetsToApplyForProduct ($product, $rulesets = array(), $used = array()) {
		$rulesetsToApply = [];

		if ($rulesets === null) {
			return $rulesetsToApply;
		}

		foreach ($rulesets as $id => $config) {
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

			if ($use) {
				$rulesetsToApply[] = [
					'id' => $id,
					'increase' => $config['increase'],
					'useLabel' => $config['useLabel'],
					'label' => $config['label'],
					'once' => $config['once']
				];
			}
		}

		return $rulesetsToApply;
	}

	public function reduceRulesetsToApply ($rulesets, $mode) {
		switch ($mode) {
			case self::RULES_TOTAL_SET_MAX_INCREASE_VALUE:
				return array_reduce($rulesets, [ $this, '__maxByIncreaseValue' ], []);
			case self::RULES_TOTAL_SET_MIN_INCREASE_VALUE:
				return array_reduce($rulesets, [ $this, '__minByIncreaseValue' ], []);
			default:
				return [];
		}
	}

	protected function __maxByIncreaseValue ($prev, $current) {
		return (empty($prev) || $prev['increase'] < $current['increase']) ? $current : $prev;
	}

	protected function __minByIncreaseValue ($prev, $current) {
		return (empty($prev) || $prev['increase'] > $current['increase']) ? $current : $prev;
	}

	public function rulesetToTotals ($ruleset, $quantity = 1) {
		$total = array(
			'code'       => 'foc_increase_total',
			'title'      => $ruleset['label'],
			'value'      => $this->calculateRulesetIncreaseTotal($ruleset, $quantity),
			'sort_order' => 10,
		);

		return $total;
	}

	public function calculateRulesetIncreaseTotal ($ruleset, $quantity = 1) {
		if ($ruleset['once']) {
			return $ruleset['increase'];
		}
		else {
			return $ruleset['increase'] * $quantity;
		}
	}

	/*
		Opencart getTotal API
	*/
  public function getTotal($total) {
		$this->load->model('catalog/product');

		$rulesets = $this->getRulesets();

		$increaseTotal = 0;
		$labels = [];
		foreach ($this->cart->getProducts() as $product) {
			$rulesetsToApply = $this->checkValidRulesetsToApplyForProduct($product, $rulesets);

			if (!empty($rulesetsToApply)) {
				$calculationMode = $this->getTotalCalculationMode();
				$reduced = [];

				if ($calculationMode === self::RULES_TOTAL_SUM_INCREASE_VALUES) {
					$reduced = $rulesetsToApply;
				}
				else {
					$reduced = [ $this->reduceRulesetsToApply($rulesetsToApply, $calculationMode) ];
				}

				if (!empty($reduced)) {
					foreach ($reduced as $ruleset) {
						$rulesetIncrease = $this->calculateRulesetIncreaseTotal($ruleset, $product['quantity']);
						$increaseTotal += $rulesetIncrease;

						if ($ruleset['useLabel']) {
							if (!isset($labels[$ruleset['id']])) {
								$labels[$ruleset['id']] = $this->rulesetToTotals($ruleset, $product['quantity']);
							}
							else {
								$labels[$ruleset['id']]['value'] += $rulesetIncrease;
							}
						}
					}
				}
			}
		}

		foreach ($labels as $label) {
			$total['totals'][] = $label;
		}

		$total['total'] += $increaseTotal;
	}
}