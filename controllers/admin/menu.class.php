<?php
namespace CloudFrontPageCache;

/**
 * Link Filter Controller
 *
 * @package    cloudfront-page-cache
 * @subpackage cloudfront-page-cache/controllers/admin
 * @author     PageSpeed.pro <info@pagespeed.pro>
 */
if (!defined('ABSPATH')) {
    exit;
}

class AdminMenu extends Controller implements Controller_Interface
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
        return parent::construct($Core, array(
            'AdminMenuBar',
            'AdminView'
        ));
    }

    /**
     * Setup controller
     */
    protected function setup()
    {

        // admin options page
        add_action('admin_menu', array($this, 'admin_menu'), 30);

        // reorder menu
        add_filter('custom_menu_order', array($this, 'reorder_menu'), 100);
        
        // enqueue scripts
        add_action('admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), $this->first_priority);
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

        // preload menu icon ?>
<link rel="preload" href="<?php print CF_FPC_URI; ?>admin/images/aws-block.svg" as="image" type="image/svg+xml" />
<?php

        // global admin script
        wp_enqueue_script('cloudfront-page-cache-menu', CF_FPC_URI . 'admin/js/menu.js', array( 'jquery' ), CF_FPC_VERSION);
        wp_localize_script("cloudfront-page-cache-menu", "cfpagecachedir", CF_FPC_URI);
    }
    
    /**
     * Admin menu option
     */
    public function admin_menu()
    {
        global $submenu;

        add_submenu_page('options-general.php', __('Amazon CloudFront Page Cache', 'cloudfront-page-cache'), __('CF Page Cache', 'cloudfront-page-cache'), 'manage_options', 'cloudfront-page-cache', array(
            &$this->AdminView,
            'display'
        ));
    }

    /**
     * Reorder menu
     */
    public function reorder_menu($menu_order)
    {
        global $submenu;

        // move CSS Editor to end of list
        foreach ($submenu['options-general.php'] as $key => $item) {
            if ($item[2] === 'cloudfront-page-cache') {
                $submenu['options-general.php'][] = $item;
                unset($submenu['options-general.php'][$key]);
            }
        }
    }
}
