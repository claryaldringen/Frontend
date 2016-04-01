<?php

class OrderFormRules {

	const PHONE = 'OrderFormRules::validatePhoneNumber';

	public static function validatePhoneNumber(\Nette\Forms\IControl $control) {
		$value = $control->getValue();
		$value = str_replace([' ', '+', '.'], '', $value);
		$match = [];
		preg_match('/[0-9]+/', $value, $match);
		return !empty($match) && $match[0] == $value;
	}
}
