<?php
namespace CloudFrontPageCache;

/**
 * Options Controller
 *
 * @package    cloudfront-page-cache
 * @subpackage cloudfront-page-cache/controllers
 * @author     PageSpeed.pro <info@pagespeed.pro>
 */
if (!defined('ABSPATH')) {
    exit;
}

class Options extends Controller implements Controller_Interface
{
    private $data; // options data

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
    public function setup()
    {
        // get options
        $this->data = get_option('cf-page-cache', array());
        if (!is_array($this->data)) {
            $this->data = array();
        }

        // disable public access to options
        //add_filter('option_cf-page-cache', array($this,'restrict_option_access'), $this->first_priority, 1);
        //add_filter('pre_option_cf-page-cache', array($this,'restrict_option_access'), $this->first_priority, 2);
    }

    /**
     * Get option
     *
     * @param  string $key     Option key.
     * @param  string $Default Default value for non existing options.
     * @return mixed  Option data.
     */
    final public function get($key, $default = false)
    {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    /**
     * Get boolean option
     *
     * @param  string  $key     Option key.
     * @param  string  $Default Default value for non existing options.
     * @return boolean True/false
     */
    final public function bool($key, $default = false)
    {
        if (isset($this->data[$key])) {
            return ($this->data[$key]) ? true : false;
        }

        return $default;
    }

    /**
     * Get all options
     *
     * @return array Option data.
     */
    final public function getAll()
    {
        return $this->data;
    }


    /**
     * Restrict access to option
     *
     * @param  mixed  $value       Option value.
     * @param  string $option_name Option name.
     * @return array  Empty array.
     */
    final public function restrict_option_access($value, $option_name = false)
    {
        return array(); // return empty
    }
}
