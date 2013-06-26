<?php  defined('C5_EXECUTE') or die('Access denied.');

class RecaptchaSystemCaptchaTypeController extends SystemCaptchaTypeController {

	/** Display the captcha label. */
	public function label() {
		$form = Loader::helper('form');
		print $form->label('captcha', t('Please type the letters and numbers shown in the image.'));
	}

	protected static function showSmaller() {
		static $result;
		if(!isset($result)) {
			$result = false;
			if(version_compare(APP_VERSION, '5.6.0', '>=')) {
				Loader::library('3rdparty/mobile_detect');
				$md = new Mobile_Detect();
				if ($md->isMobile() && (!$md->isTablet())) {
					$result = true;
				}
			}
		}
		return $result;
	}

	/** Display the captcha (if the reCAPTCHA keys are set). */
	public function display() {
		$keys = self::getKeys(true);
		if(!$keys) {
			echo t('Please specify the reCAPTCHA keys');
		}
		else {
			Loader::library('3rdparty/recaptcha/recaptchalib', 'recaptcha');
			if(self::showSmaller()) {
				$th = Loader::helper('text');
				?><script type="text/javascript">
var RecaptchaOptions = {
	theme : "custom",
	custom_theme_widget: "recaptcha_widget"
};
</script>
<div id="recaptcha_widget" style="display:none">
   <div id="recaptcha_image"></div>
   <div class="recaptcha_only_if_incorrect_sol" style="color:red"><?php echo t('Incorrect. Please try again'); ?></div>
   <div class="recaptcha_only_if_image"><?php echo t('Enter the words above'); ?></div>
   <div class="recaptcha_only_if_audio"><?php echo t('Enter the numbers you hear'); ?></div>
   <input type="text" id="recaptcha_response_field" name="recaptcha_response_field" />
   <div><a href="javascript:Recaptcha.reload()"><?php echo t('Get another CAPTCHA'); ?></a></div>
   <div class="recaptcha_only_if_image"><a href="javascript:Recaptcha.switch_type('audio')"><?php echo t('Get an audio CAPTCHA'); ?></a></div>
   <div class="recaptcha_only_if_audio"><a href="javascript:Recaptcha.switch_type('image')"><?php echo t('Get an image CAPTCHA'); ?></a></div>
   <div><a href="javascript:Recaptcha.showhelp()"><?php echo t('Help'); ?></a></div>
 </div>
 <script type="text/javascript" src="http://www.google.com/recaptcha/api/challenge?k=<?php echo $th->specialchars($keys['recaptcha_key_public']); ?>"></script>
 <script type="text/javascript">
$(document).ready(function() {
	function f() {
		var ok, div, img, i;
		ok = false;
		if(div = document.getElementById("recaptcha_image")) {
			img = null;
			for(i = 0; i < div.childNodes.length; i++) {
				if(div.childNodes[i].tagName == "IMG") {
					img = div.childNodes[i];
					div.style.width = "250px";
					img.style.width = "250px";
					img.style.height = "48px";
					ok = true;
					break;
				}
			}
		}
		if(!ok) {
			setTimeout(function() { f(); }, 50);
			return;
		}
	}
	f();
});
</script>
 <noscript>
 	<iframe src="http://www.google.com/recaptcha/api/noscript?k=<?php echo $th->specialchars($keys['recaptcha_key_public']); ?>" height="300" width="500" frameborder="0"></iframe><br>
   <textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
   <input type="hidden" name="recaptcha_response_field" value="manual_challenge" />
 </noscript><?php
			}
			else {
				echo recaptcha_get_html($keys['recaptcha_key_public']);
			}
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
