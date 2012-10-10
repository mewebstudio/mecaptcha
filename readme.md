# MeCaptcha Bundle
MeCaptcha is a captcha bundle for the Laravel framework. It does not require any external dependencies and is easy to use.

## Installation

	php artisan bundle:install mecaptcha

Note: **MeCaptcha requires to have a Session Driver set**. Check /application/config/session.php. I recommend you to set **'driver' => 'file'**, especially for development. Setting "'driver' => 'cookie'" on localhost may cause domain-related problems.

## Bundle Registration

Add the following to your **/application/bundles.php** file:

	'mecaptcha' => array('auto' => true, 'handles' => 'mecaptcha'),

## Usage

In **/application/routes.php** place something like:

	// on "get" we display /views/layouts/register.php, which contains our registration form
	Route::get('register', function()
	{
		return View::make('layouts.register');
	});

	// on "post" we validate the input
	Route::post('register', function()
	{
		$rules = array(
			'captcha' => 'mecaptcha|required'
		);
		$messages = array(
			'mecaptcha' => 'Invalid captcha',
		);

		$validation = Validator::make(Input::all(), $rules, $messages);

		if ($validation->valid())
		{
			// valid captcha
		} else
		{
			return Redirect::to('register')->with_errors($validation);
		}
	});

Feel free to add to the above code other validation rules according to your application.

Next, in your view (say, **/views/layouts/register.php**), place something like:

	echo Form::open('register', 'POST', array('class' => 'register_form'));
	... [other fields] ...
	echo Form::text('captcha', '', array('class' => 'captchainput', 'placeholder' => 'Insert captcha...'));
	echo Form::image(MeCaptcha\Captcha::img(), 'captcha', array('class' => 'captchaimg'));
	... [other fields] ...
	echo Form::close();

## Customisation

You can configure all settings in **config/config.php**. Each line of the config is thoroughly documented.

## Further information
This bundle is maintained by Muharrem ERİN (me@mewebstudio.com). If you have any questions or suggestions, email me. You can always grab the latest version from http://github.com/mewebstudio/mecaptcha