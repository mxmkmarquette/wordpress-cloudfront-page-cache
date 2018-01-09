<?php
namespace CloudFrontPageCache;

/**
 * Translation Controller
 *
 * @package    cloudfront-page-cache
 * @subpackage cloudfront-page-cache/controllers
 * @author     PageSpeed.pro <info@pagespeed.pro>
 */
if (!defined('ABSPATH')) {
    exit;
}

class I18n extends Controller implements Controller_Interface
{
    
    /**
     * Load controller
     *
     * @param  Core       $Core Core controller instance.
     * @return Controller Controller instance.
     */
    public static function &load(Core &$Core)
    {
        // instantiate controller
        return parent::construct($Core);
    }

    /**
     * Setup controller
     */
    protected function setup()
    {
        // load translation on WordPress init
        add_action('init', array($this,'load_textdomain'), $this->first_priority);
    }

    /**
     * Setup translation
     */
    final public function load_textdomain()
    {
        // load text domain
        load_plugin_textdomain(
            'cloudfront-page-cache',
            false,
            CF_FPC_PATH . 'languages/'
        );
    }
}
