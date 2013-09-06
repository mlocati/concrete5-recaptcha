<?php defined('C5_EXECUTE') or die('Access denied.');

class RecaptchaSystemCaptchaTypeController extends SystemCaptchaTypeController {

	/** Display the captcha label. */
	public function label() {
		$form = Loader::helper('form');
		print $form->label('captcha', t('Please type the letters and numbers shown in the image.'));
	}

	/** Display the captcha (if the reCAPTCHA keys are set). */
	public function display() {
		$keys = self::getKeys(true);
		if(!$keys) {
			echo t('Please specify the reCAPTCHA keys');
		}
		else {
			Loader::library('3rdparty/recaptcha/recaptchalib', 'recaptcha');
			$use_ssl = false;
			if(isset($_SERVER) && is_array($_SERVER) && array_key_exists('HTTPS', $_SERVER) && is_string($_SERVER['HTTPS']) && (!empty($_SERVER['HTTPS'])) && (strcasecmp($_SERVER['HTTPS'], 'off') != 0)) {
				$use_ssl = true;
			}
			echo recaptcha_get_html($keys['recaptcha_key_public'], null, $use_ssl);
		}
	}

	/** Displays the text input field that must be entered when used with a corresponding image. */
	public function showInput($args = false)
	{ }

	/** Checks the captcha code the user has entered.
	* @return boolean true if the code was correct, false if not.
	* @throws Exception Throws an Exception if the reCAPTCHA keys are not set.
	*/
	public function check() {
		$keys = self::getKeys(true);
		if(!$keys) {
			throw new Exception(t('Please specify the reCAPTCHA keys'));
		}
		Loader::library('3rdparty/recaptcha/recaptchalib', 'recaptcha');
		$resp = recaptcha_check_answer(
			$keys['recaptcha_key_private'],
			Loader::helper('validation/ip')->getRequestIP(),
			@$_POST['recaptcha_challenge_field'],
			@$_POST['recaptcha_response_field']
		);
		return $resp->is_valid ? true : false;
	}

	/** Save the reCAPTCHA-specific options.
	* @param array $options
	*/
	public function saveOptions($options) {
		$co = new Config();
		$co->setPackageObject(Package::getByHandle('recaptcha'));
		foreach(array('recaptcha_key_public', 'recaptcha_key_private') as $name) {
			$value = isset($options[$name]) && is_string($options[$name]) ? trim($options[$name]) : '';
			if(strlen($value)) {
				$co->save($name, $value);
			}
			else {
				$co->clear($name);
			}
		}
	}

	/** Returns the reCAPTCHA keys.
	* @param bool $onlyIfAllValid [default: false] Set to true if the keys are needed: if they are not specified you'll get a null.
	* @return null|array
	*/
	private static function getKeys($onlyIfAllValid = false) {
		$keys = array();
		$co = new Config();
		$co->setPackageObject(Package::getByHandle('recaptcha'));
		$allValid = true;
		foreach(array('recaptcha_key_public', 'recaptcha_key_private') as $name) {
			$value = $co->get($name);
			if(!is_string($value)) {
				$value = '';
			}
			$keys[$name] = $value;
			if(!strlen($value)) {
				$allValid = false;
			}
		}
		return ($allValid || (!$onlyIfAllValid)) ? $keys : null;
	}

}
