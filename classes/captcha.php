<?php

/**
 *
 * MeCaptcha
 *
 * Captcha package for the Laravel framework.
 * (c) 2012 Muharrem ERİN (me@mewebstudio.com)
 * Based on Fuel-Captcha (c) 2011 Mikhail Khasaya, Kruglov Sergei
 *
 * @package		MeCaptcha
 * @version		1.0
 * @author		Muharrem ERİN
 * @license		MIT License (see LICENSE for more info)
 * @copyright	2011-2012 Muharrem ERİN
 * 
 */


namespace MeCaptcha;

class Captcha
{

    public static $config = array();

    public static $fonts = array();

    public static $backgrounds = array();

    public static $char;

    /**
     * Captcha make function 
     *
     * @access  public
     * @return void
     */
    public static function make($sizes = NULL, $id = NULL) {

        static::$config = \Config::get('mecaptcha::config');

        if (is_array($sizes))
        {
            if (isset($sizes[0])) static::$config['length'] = $sizes[0];
            if (isset($sizes[1])) static::$config['width'] = $sizes[1];
            if (isset($sizes[2])) static::$config['height'] = $sizes[2];
            if (isset($sizes[3])) static::$config['space'] = $sizes[3];
        }
        if ($id != '')
        {
            static::$id = $id;
        }
        static::create();

    }

    /**
     * Fonts
     *
     * @access  public
     * @return  void
     */
    public static function fonts() {

	foreach (glob(static::$config['fontsDir'].'*.ttf') as $filename)
	{
	    static::$fonts[] = $filename;
	}

    }

    /**
     * Backgrounds
     *
     * @access  public
     * @return  void
     */
    public static function backgrounds() {

	foreach (glob(static::$config['backgroundsDir'].'*.png') as $filename)
	{
	    static::$backgrounds[] = $filename;
	}

    }

    /**
     * Select Background
     *
     * @access  public
     * @return  void
     */
    public static function selectBackground()
    {

        return static::$backgrounds[rand(0, count(static::$backgrounds) - 1)];

    }

    /**
     * Select Font
     *
     * @access  public
     * @return  void
     */
    public static function selectFont()
    {

        return static::$fonts[rand(0, count(static::$fonts) - 1)];

    }

    /**
     * Select Font Color
     *
     * @access  public
     * @return  void
     */
    public static function selectColor()
    {

        return static::$config['colors'][rand(0, count(static::$config['colors']) - 1)];

    }

    /**
     * Select Font Size
     *
     * @access  public
     * @return  void
     */
    public static function selectFontSize()
    {

        return static::$config['fontSizes'][rand(0, count(static::$config['fontSizes']) - 1)];

    }

    /**
     * Generates a captcha image, writing it to the output
     * It is used internally by this bundle when pointing to "/captcha" (see [bundle]\routes.php)
     * Typically, you won't use this function, but use the above img() function instead
     *
     * @access	public
     * @return	void
     */
    public static function create() {

        static::fonts();
        static::backgrounds();

        $bg_image = static::selectBackground();

        static::$char = \Str::random(static::$config['length'], static::$config['type']);

        \Session::put('session_captcha_hash', \Hash::make(\Str::lower(static::$char)));

        $bg_image_info = getimagesize($bg_image);
        if ($bg_image_info['mime'] == 'image/jpg' || $bg_image_info['mime'] == 'image/jpeg')
        {
            $old_image = imageCreateFromJPEG($bg_image);
        }
        elseif ($bg_image_info['mime'] == 'image/gif')
        {
            $old_image = imageCreateFromGIF($bg_image);
        }
        elseif ($bg_image_info['mime'] == 'image/png')
        {
            $old_image = imageCreateFromPNG($bg_image);
        }

        $new_image = imageCreateTrueColor(static::$config['width'], static::$config['height']);
        $bg = imagecolorallocate($new_image, 255, 255, 255);
        imagefill($new_image, 0, 0, $bg);

        imagecopyresampled($new_image, $old_image, 0, 0, 0, 0, static::$config['width'], static::$config['height'], $bg_image_info[0], $bg_image_info[1]);

        $bg = imagecolorallocate($new_image, 255, 255, 255);
        for ($i = 0; $i < strlen(static::$char); $i++)
        {
            $color_cols = explode(',', static::selectColor());
            $fg = imagecolorallocate($new_image, trim($color_cols[0]), trim($color_cols[1]), trim($color_cols[2]));
            imagettftext($new_image, static::selectFontSize(), rand(-10, 15), 10 + ($i * static::$config['space']), rand(static::$config['height'] - 10, static::$config['height'] - 5), $fg, static::selectFont(), static::$char[$i]);
        }

        header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
        header('Pragma: no-cache');
        header("Content-type: image/png");
        header('Content-Disposition: inline; filename=' . static::$config['id'] . '.png');
        return imagePNG($new_image);

    }

    /**
     * Checks if the supplied captcha test value matches the stored one
     * 
     * @param	string	$value
     * @access	public
     * @return	bool
     */
    public static function check($value)
    {

	$session_captcha_hash = \Session::get('session_captcha_hash', null);

	return $value != null && $session_captcha_hash != null && \Hash::check(\Str::lower($value), $session_captcha_hash);

    }

    /**
     * Returns an URL to the captcha image
     * For example, you can use in your view something like
     * <img src="<?php echo MeCaptcha\Captcha::img(); ?>" alt="" />
     *
     * @access	public
     * @return	string
     */
    public static function img() {

	return \URL::to('captcha?' . mt_rand(1, 100000), NULL, false, false);

    }

}

// end of file captcha.php