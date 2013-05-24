<?php  defined('C5_EXECUTE') or die('Access denied.');
$form = Loader::helper('form');
$co = new Config();
$co->setPackageObject(Package::getByHandle('recaptcha'));
$recaptcha_key_public = $co->get('recaptcha_key_public');
$recaptcha_key_private = $co->get('recaptcha_key_private');
Loader::library('3rdparty/recaptcha/recaptchalib', 'recaptcha');
$recaptcha_signupUrl = recaptcha_get_signup_url($_SERVER['HTTP_HOST'], SITE);
?>
<div class="clearfix">
	<?php  echo $form->label('recaptcha_key_public', t('reCAPTCHA public key')); ?>
	<div class="input">
		<?php  echo $form->text('recaptcha_key_public', is_string($recaptcha_key_public) ? $recaptcha_key_public : '', array('class' => 'span5')); ?>
	</div>
</div>
<div class="clearfix">
	<?php  echo $form->label('recaptcha_key_private', t('reCAPTCHA private key')); ?>
	<div class="input">
		<?php  echo $form->text('recaptcha_key_private', is_string($recaptcha_key_private) ? $recaptcha_key_private : '', array('class' => 'span5')); ?>
	</div>
</div>
<div class="clearfix">
	<div class="input">
		<?php  echo $form->label('', t('You can get the public and private keys from <a target="_blank" href="%s">this page</a>.', $recaptcha_signupUrl)); ?>
	</div>
</div>
