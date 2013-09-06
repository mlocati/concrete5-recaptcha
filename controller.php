<?php defined('C5_EXECUTE') or die('Access denied.');

class RecaptchaPackage extends Package {

	protected $pkgHandle = 'recaptcha';
	protected $appVersionRequired = '5.5';
	protected $pkgVersion = '0.9.0';

	public function getPackageName() {
		return t('reCAPTCHA');
	}

	public function getPackageDescription() {
		return t('Allow to use the reCAPTCHA captcha.');
	}

	public function install() {
		$pkg = parent::install();
		$this->installOrUpgrade($pkg, '0.0.0');
	}

	public function upgrade() {
		$currentVersion = $this->getPackageVersion();
		parent::upgrade();
		$this->installOrUpgrade($this, $currentVersion);
	}

	private function installOrUpgrade($pkg, $upgradeFromVersion) {
		$currentLocale = Localization::activeLocale();
		if ($currentLocale != 'en_US') {
			Localization::changeLocale('en_US');
		}
		Loader::model('system/captcha/library');
		if(!SystemCaptchaLibrary::getByHandle('recaptcha')) {
			SystemCaptchaLibrary::add('recaptcha', t('reCAPTCHA'), $pkg);
		}
		if ($currentLocale != 'en_US') {
			Localization::changeLocale($currentLocale);
		}
	}

	public function uninstall() {
		Loader::model('system/captcha/library');
		$active = SystemCaptchaLibrary::getActive();
		if($active && ($active->getSystemCaptchaLibraryHandle() == 'recaptcha')) {
			foreach(SystemCaptchaLibrary::getList() as $anotherCaptcha) {
				if($anotherCaptcha->getSystemCaptchaLibraryHandle() != 'recaptcha') {
					$anotherCaptcha->activate();
					break;
				}
			}
		}
		parent::uninstall();
	}
}
