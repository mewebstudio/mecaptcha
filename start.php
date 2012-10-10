<?php

Autoloader::map(array(
	'MeCaptcha\\Captcha' => __DIR__.DS.'classes'.DS.'captcha.php',
));

Laravel\Validator::register('mecaptcha', function($attribute, $value, $parameters)
{
	return MeCaptcha\Captcha::check($value);
});