<?php

Route::get('captcha', function()
{
	MeCaptcha\Captcha::make();
});