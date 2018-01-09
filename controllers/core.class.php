<?php 
namespace CloudFrontPageCache;

/**
 * CloudFront Page Cache core class.
 *
 * @package    cloudfront-page-cache/cloudfrontpagecache
 * @subpackage cloudfront-page-cache/controllers
 * @author     PageSpeed.pro <info@pagespeed.pro>
 */
if (!defined('ABSPATH')) {
    exit;
}

class Core
{
    // Core instance
    protected static $instance = null;

    protected $controllers = array(); // controller instances

    // core controllers
    protected $core_controllers = array(
        'error',    // error controller
        'options',  // options controller
        'admin',    // admin controller
        'shutdown',
        'pagecache',
        'aws',
        'install'
    );

    // admin contorllers (plugin settings pages etc.)
    private $admin_controllers = array(
        'AdminCP',
        'AdminMenu',
        'AdminMenuBar',
        'AdminPluginIndex',
        'AdminView',
        'AdminInvalidation'
    );

    // global admin controllers (frontend, /wp-admin/ etc.)
    protected $global_admin_controllers = array(
        'AdminMenuBar'
    );

    // @todo
    private $loading_failed_msg = 'Failed to load CloudFront Page Cache. Please contact the administrator of this website.'; // loading failed message

    /**
     * Autoload controller dependencies
     *
     * @param string $class_name The class name for which to load dependencies.
     */
    final private function autoload($class_name)
    {
        // restrict to namespace
        if (strpos($class_name, 'CloudFrontPageCache\\') === false) {
            return;
        }

        // already loaded
        if (class_exists($class_name)) {
            return;
        }

        // load class file
        $class_name_part_original = substr($class_name, 20);
        $class_name_part = strtolower($class_name_part_original);

        if ($class_name_part !== 'admin' && strpos($class_name_part, 'admin') === 0) {

            // forbidden outside /wp-admin/
            if (!is_admin() && !in_array($class_name_part_original, $this->global_admin_controllers)) {
                throw new Exception('Admin controller loaded outside admin environment.', 'core');
            }

            // admin controller
            $class_file = CF_FPC_PATH . 'controllers/admin/'.substr($class_name_part, 5).'.class.php';
        } else {
            $class_file = CF_FPC_PATH . 'controllers/'.$class_name_part.'.class.php';
        }
        
        if (!file_exists($class_file)) {
            if (class_exists('CloudFrontPageCache\\Exception')) {
                throw new Exception('Class file does not exist ' . $class_name, 'core');
            }
            wp_die(__($this->loading_failed_msg, 'cloudfront-page-cache') . '<hr />A controller class file '.$class_file.' is missing.');
        }
        require_once $class_file;

        // loading failed
        if (!class_exists($class_name)) {
            if (class_exists('CloudFrontPageCache\\Exception')) {
                throw new Exception('Failed to load class ' . $class_name, 'core');
            }
            wp_die(__($this->loading_failed_msg, 'cloudfront-page-cache') . '<hr />Failed to load a controller class.');
        }
    }

    /**
     * Instantiate and setup core controller
     *
     * @static
     */
    public static function load()
    {
        // allow instantiation once
        if (!is_null(self::$instance)) {

            // developer debug message
            _doing_it_wrong(__FUNCTION__, __('Forbidden'), CF_FPC_VERSION);

            // print error to regular users
            wp_die(__('The CloudFront Page Cache controller is instantiated multiple times. This may indicate an attack. Please contact the administrator of this website.', 'cloudfront-page-cache'));
        }

        // instantiate core controller
        self::$instance = new self();
        self::$instance->setup();
    }

    /**
     * Setup core controller
     */
    final protected function setup()
    {

        // autoload controller class files
        spl_autoload_register(array($this,'autoload'));

        // core controllers
        $controllers = $this->core_controllers;

        // admin control panel controller
        if (is_admin()) {
            $controllers = array_merge($controllers, $this->admin_controllers);
        } else {

            // fast checking for login
            $loggedin = false;
            if (!empty($_COOKIE)) {
                foreach ($_COOKIE as $key => $val) {
                    if (strpos($key, "wordpress_logged_in") === 0) {
                        $loggedin = true;
                        break;
                    }
                }
            }

            // loggedin user
            if ($loggedin) {
                $controllers = array_merge($controllers, $this->global_admin_controllers);
            }
        }
        
        // load controllers
        foreach ($controllers as $controller_name) {
            $this->load_controller($controller_name);
        }

        // allow extension with custom controllers
        $custom_controllers = apply_filters('cloudfrontpagecache_controllers', false);
        if (!empty($custom_controllers) && is_array($custom_controllers)) {
            $custom_controllers = array_map('strtolower', array_unique(array_values($custom_controllers)));
            if (!empty(array_intersect($controllers, $custom_controllers))) {

                // print fatal error to public
                // @todo
                wp_die(__($this->loading_failed_msg, 'cloudfront-page-cache') . '<hr />Custom optimization controllers conflict with internal controllers');
            }
            foreach ($custom_controllers as $controller_name) {
                $this->load_controller($controller_name);
            }
        }

        // setup completed
        try {
            do_action('cloudfrontpagecache_setup_completed');
        } catch (Exception $e) {
                  
            // print fatal error to public
            wp_die(__($this->loading_failed_msg, 'cloudfront-page-cache') . '<hr />'.$e->getMessage().'<pre>File: '.$this->controllers['file']->safe_path($e->getFile()).'<br />Line: '.$e->getLine().'</pre>');
        }
    }

    /**
     * Load controller
     *
     * @param string $controller_name      Controller name.
     * @param string $controller_classname Controller class name.
     */
    final protected function load_controller($controller_name, $controller_classname = false)
    {

        // no class override
        if (!$controller_classname) {
            $controller_classname = 'CloudFrontPageCache\\' . ucfirst($controller_name);
        }

        // load controller
        try {
            $this->controllers[$controller_name] = & $controller_classname::load($this);
        } catch (Exception $e) {
            $file = (isset($this->controllers['file'])) ? $this->controllers['file']->safe_path($e->getFile()) : str_replace(trailingslashit(ABSPATH), '/', $e->getFile());
              
            // print fatal error to public
            wp_die(__($this->loading_failed_msg, 'cloudfront-page-cache') . '<hr />'.$e->getMessage().'<pre>Class: <strong>'.$controller_classname.'</strong><br />File: '.$file.'<br />Line: '.$e->getLine().'</pre>');
        }

        // controller loaded hook
        do_action('cloudfrontpagecache_controller_loaded', $controller_name);
    }

    /**
     * Forward exception to error controller
     *
     * @param CloudFrontPageCache\Exception $error Exception to forward.
     */
    final public static function forward_exception(Exception $error)
    {
        self::$instance->controllers['error']->handle($error);
    }

    /**
     * Trigger cron
     */
    final public function cron($trigger)
    {
        switch ($trigger) {
            /*case "cache": // Prune cache
                $this->controllers['cache']->prune();
            break;*/
        }
    }

    /**
     * Return instance
     */
    final public static function get_instance($controller = false)
    {
        if ($controller) {
            if (!isset(self::$instance->controllers[$controller])) {
                throw new Exception('Public called controller instance does not exist: "'.esc_html($controller).'".', 'core');
            }

            return self::$instance->controllers[$controller];
        }

        return self::$instance;
    }

    // construction is forbidden
    final protected function __construct()
    {
        if (!in_array(get_called_class(), array('CloudFrontPageCache\\Core'))) {
            wp_die(__($this->loading_failed_msg, 'cloudfront-page-cache') . '<hr />CloudFront Page Cache Core class extension is not allowed.');
        }
    }

    // cloning is forbidden.
    final private function __clone()
    {
    }

    // unserializing instances of this class is forbidden.
    final private function __wakeup()
    {
    }
}
