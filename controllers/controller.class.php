<?php
namespace CloudFrontPageCache;

/**
 * Controller class
 *
 * @package    cloudfront-page-cache
 * @subpackage cloudfront-page-cache/controllers
 * @author     PageSpeed.pro <info@pagespeed.pro>
 */
if (!defined('ABSPATH')) {
    exit;
}

abstract class Controller
{
    private static $instances = array(); // instantiated controllers

    protected $core; // core controller

    // controller instances
    protected $i18n;
    protected $error;
    protected $install;
    protected $options;
    protected $pagecache;
    protected $admin;
    protected $shutdown;
    protected $aws;

    // admin controller instances
    protected $AdminCP;
    protected $AdminPluginIndex;
    protected $AdminMenu;
    protected $AdminMenuBar;
    protected $AdminAjax;
    protected $AdminView;
    protected $AdminMenuTabs;
    protected $AdminInvalidation;

    protected $wpdb; // WordPress database

    private $bind_after_setup; // controllers to bind after setup

    // base controllers to bind
    private $base_controllers = array(
        'error'
    );

    protected $first_priority; // first priority integer
    protected $content_path; // wp-content/ directory path

    /**
     * Construct the controller
     *
     * @param Core  &$Core The root optimization controller.
     * @param array $bind  An array with controllers to bind to the child.
     */
    final protected function __construct(Core &$Core, $bind)
    {
        global $wpdb; // WordPress database
        $this->wpdb = & $wpdb; // reference to WordPress database controller
        $this->core = & $Core; // reference to core controller
        $this->first_priority = (PHP_INT_MAX * -1); // first priority integer

        // wp-content/ path
        $this->content_path = trailingslashit(WP_CONTENT_DIR);

        // bind non existent controllers after setup
        $this->bind_after_setup = array();

        // bind child controllers
        $bind = (!empty($bind)) ? array_unique(array_merge($this->base_controllers, $bind)) : $this->base_controllers;
        foreach ($bind as $controller_name) {
            $controller_classname = 'CloudFrontPageCache\\' . ucfirst($controller_name);
            if (isset(self::$instances[$controller_classname])) {
                if (!property_exists($this, $controller_name)) {
                    throw new Exception('Child controller not protected in Controller: ' . $controller_name, 'core');
                }
                $this->$controller_name = & self::$instances[$controller_classname];
            } else {
                $this->bind_after_setup[$controller_classname] = $controller_name;
            }
        }

        // bind controllers after setup
        if (!empty($this->bind_after_setup)) {
            add_action('cloudfrontpagecache_controller_setup_completed', array($this,'after_controller_setup'), $this->first_priority, 1);
            add_action('cloudfrontpagecache_setup_completed', array($this,'after_optimization_setup'), $this->first_priority);
        }
    }

    /**
     * Construct the controller
     *
     * @param  Core       &$Core The root optimization controller.
     * @param  array      $bind  An array with controllers to bind to the child.
     * @return Controller The instantiated controller.
     */
    final protected static function &construct(Core &$Core, $bind = false)
    {
        // verify calling controller
        $controller_classname = get_called_class();
        if (substr($controller_classname, 0, 20) !== 'CloudFrontPageCache\\') {
            throw new Exception('Invalid caller.', 'core');
        }

        // allow instantiation once
        if (isset(self::$instances[$controller_classname])) {

            // developer debug message
            _doing_it_wrong($controller_classname, __('Forbidden'), CF_FPC_VERSION);

            // print error to regular users
            wp_die('The '.htmlentities($controller_classname, ENT_COMPAT, 'utf-8').' controller is instantiated multiple times. This may indicate an attack. Please contact the administrator of this website.');
        }

        // instantiate controller
        self::$instances[$controller_classname] = new $controller_classname($Core, $bind);

        // setup controller
        if (method_exists(self::$instances[$controller_classname], 'setup')) {
            self::$instances[$controller_classname]->setup();
        }

        // controller setup completed
        do_action('cloudfrontpagecache_controller_setup_completed', $controller_classname);

        // return controller
        return self::$instances[$controller_classname];
    }

    /**
     * After optimization controller setup hook.
     *
     * @param string $controller_classname The class name of the controller to bind.
     */
    final public function after_controller_setup($controller_classname)
    {

        // bind child controller directly after instantiation and setup
        if (!isset($this->bind_after_setup[$controller_classname])) {

            // development class override
            if (strpos($controller_classname, 'OptimizationDev\\') !== false) {
                $controller_classname = str_replace('OptimizationDev\\', 'CloudFrontPageCache\\', $controller_classname);
            } else {
                return;
            }
        }

        if (isset($this->bind_after_setup[$controller_classname])) {
            if (!isset(self::$instances[$controller_classname])) {
                throw new Exception('Controller ' . $controller_classname . ' not instantiated.', 'core');
            }
            $controller_name = $this->bind_after_setup[$controller_classname];

            // admin controller?
            
            $this->$controller_name = & self::$instances[$controller_classname];
            unset($this->bind_after_setup[$controller_classname]);
        }
    }

    /**
     * After Core optimization controller setup hook.
     */
    final public function after_optimization_setup()
    {

        // bind child controllers and throw exception for unmet dependencies
        if (!empty($this->bind_after_setup)) {
            foreach ($this->bind_after_setup as $controller_classname => $controller_name) {
                if (isset(self::$instances[$controller_classname])) {
                    $this->$controller_name = & self::$instances[$controller_classname];
                } else {
                    throw new Exception('Failed to bind controller ' . $controller_name . '.', 'core');
                }
            }
        }
    }
}

/**
 * Controller interface
 */
interface Controller_Interface
{
    public static function load(Core &$Core); // the method to instantiate the controller
}
