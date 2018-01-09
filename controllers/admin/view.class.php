<?php
namespace CloudFrontPageCache;

/**
 * Admin View Controller
 *
 * @package    cloudfront-page-cache
 * @subpackage cloudfront-page-cache/controllers/admin
 * @author     PageSpeed.pro <info@pagespeed.pro>
 */
if (!defined('ABSPATH')) {
    exit;
}

class AdminView extends Controller implements Controller_Interface
{
    // available view controllers
    private $view_controllers = array();

    // active view controller
    private $active_view = false; // reference key
    private $active_view_controller = false; // controller object

    private $aws_tracking = 'sc_channel=O10N&sc_campaign=cloudfront_page_cache&sc_publisher=wordpress&sc_medium=plugin&sc_content=cloudfront_page_cache';

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
            'options',
            'AdminInvalidation'
        ));
    }

    /**
     * Setup controller
     */
    protected function setup()
    {
        // load base AdminView class
        if (!class_exists('AdminViewBase')) {
            require_once CF_FPC_PATH . 'controllers/admin/views/base.class.php';
        }

        // load view controllers
        $files = new \FilesystemIterator(CF_FPC_PATH . 'controllers/admin/views/', \FilesystemIterator::SKIP_DOTS);
        foreach ($files as $fileinfo) {

            // filename
            $filename = $fileinfo->getFilename();

            // check file extension
            if ($filename === 'base.class.php') {
                continue 1;
            }

            // class name
            if (strpos($filename, '-') !== false) {
                $controller_classname = 'CloudFrontPageCache\AdminView' . ucfirst(str_replace('.class.php', '', implode('', array_map('ucfirst', explode('-', $filename)))));
            } else {
                $controller_classname = 'CloudFrontPageCache\AdminView' . ucfirst(str_replace('.class.php', '', implode('', array_map('ucfirst', explode('-', $filename)))));
            }

            // include controller file
            require_once CF_FPC_PATH . 'controllers/admin/views/' . $filename;

            // add controller key to index
            $this->view_controllers[$controller_classname::view_key()] = $controller_classname;
        }

        // AJAX request
        if (defined('DOING_AJAX')) {

            // not a plugin related request
            if (!isset($_REQUEST['action']) || strpos($_REQUEST['action'], 'cloudfrontpagecache_') !== 0) {
                return;
            }

            // AJAX requests use the view parameter
            if (!isset($_REQUEST['view']) || !isset($this->view_controllers[$_REQUEST['view']])) {
                throw new Exception('Invalid view reference in AJAX request.', 'admin');
            }

            // set active view
            $this->active_view = $_REQUEST['view'];
        } else {

            // not a plugin related request
            if (!isset($_GET['page']) || strpos($_GET['page'], 'cloudfront-page-cache') !== 0) {
                return;
            }

            // first try the view parameter
            if (isset($_GET['view']) && isset($this->view_controllers[$_GET['view']])) {

                // set active view
                $this->active_view = $_GET['view'];
            } else {

                // extract view from page paramater
                // @todo
                $this->active_view = ($_GET['page'] === 'cloudfront-page-cache') ? 'intro' : substr($_GET['page'], 19);

                // try tab specific view controller
                if (isset($_GET['view'])) {
                    $view_controller_name = $_GET['view'];

                    if (isset($this->view_controllers[$view_controller_name])) {
                        $this->active_view = $view_controller_name;
                    } else {
                        wp_die('Invalid view');
                    }
                }

                // invalid view
                if (!isset($this->view_controllers[$this->active_view])) {
                    throw new Exception('No view controller defined for view ' . esc_html($this->active_view), 'admin');
                    $this->active_view = false;

                    return;
                }
            }
        }

        // setup view controller
        if ($this->active_view) {

            // load controller
            $this->active_view_controller = $this->view_controllers[$this->active_view]::load($this->core);

            // setup controller
            $this->active_view_controller->setup_view();
        
            // enqueue scripts
            add_action('admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), $this->first_priority);
        }
    }

    /**
     * Enqueue scripts and styles
     */
    final public function enqueue_scripts()
    {
        // skip if user is not logged in
        if (!is_admin() || !is_user_logged_in()) {
            return;
        }

        // invalidation button CSS
        wp_enqueue_style('cf_page_cache_global', CF_FPC_URI . 'admin/css/global.css');
    }

    /**
     * Print admin view template
     */
    final public function display()
    {
        // verify admin permissions
        if ($this->active_view !== 'intro' && !current_user_can('manage_options')) {
            wp_die('No permission to access this area.');
        }

        // header
        require CF_FPC_PATH . 'admin/header.inc.php';

        // include view template
        $view = & $this->active_view_controller;
        $view_template = $this->active_view_controller->template();
        require $view_template;

        // footer
        require CF_FPC_PATH . 'admin/footer.inc.php';
    }

    /**
     * Return active view reference key
     *
     * @return string View reference key.
     */
    final public function active()
    {
        return $this->active_view;
    }

    /**
     * Return active view controller
     *
     * @return object View controller.
     */
    final public function active_controller()
    {
        return $this->active_view_controller;
    }
}
