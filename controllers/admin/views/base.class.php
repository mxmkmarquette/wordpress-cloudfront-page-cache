<?php
namespace CloudFrontPageCache;

/**
 * Admin View Base Controller
 *
 * @package    cloudfront-page-cache
 * @subpackage cloudfront-page-cache/controllers/admin
 * @author     PageSpeed.pro <info@pagespeed.pro>
 */
if (!defined('ABSPATH')) {
    exit;
}

class AdminViewBase extends Controller implements AdminView_Controller_Interface
{
    protected $json_path = ''; // dot notated path in profile

    // JSON editor
    protected $json_editor = false;
    protected $json_editor_mode = 'tree';

    /**
     * Load controller
     *
     * @param  Core       $Core Core controller instance.
     * @param  string     $View View key.
     * @return Controller Controller instance.
     */
    public static function &load(Core &$Core)
    {
        // instantiate controller
        return parent::construct($Core, array(
            'AdminView'
        ));
    }

    /**
     * Setup controller
     */
    protected function setup()
    {
        if (empty(static::$view_key)) {
            throw new Exception('View controller did not define a reference key.', 'admin');
        }
    }

    /**
     * Setup view
     */
    public function setup_view()
    {
    }

    /**
     * Return view key
     *
     * @return string key
     */
    public static function view_key()
    {
        return static::$view_key;
    }

    /**
     * Return view template
     */
    public function template($view_key = false)
    {
        if (!$view_key) {
            $view_key = static::$view_key;
        }

        $template = CF_FPC_PATH . 'admin/'.$view_key.'.inc.php'; // AdminView->active()
        if (!file_exists($template)) {
            throw new Exception('View template does not exist.', 'admin');
        }

        // print view template
        return $template;
    }
}

/**
 * Admin View Controller interface
 */
interface AdminView_Controller_Interface
{
    public static function load(Core &$Core); // the method to instantiate the controller
    public function setup_view(); // setup the view
}
