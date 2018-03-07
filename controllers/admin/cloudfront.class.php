<?php
namespace O10n;

/**
 * CloudFront Page Cache Admin Controller
 *
 * @package    optimization
 * @subpackage optimization/controllers/admin
 * @author     Optimization.Team <info@optimization.team>
 */
if (!defined('ABSPATH')) {
    exit;
}

class AdminCloudfront extends ModuleAdminController implements Module_Admin_Controller_Interface
{
    protected $admin_base = 'options-general.php';

    // tab menu
    protected $tabs = array(
        'intro' => array(
            'title' => '<span class="dashicons dashicons-admin-home"></span>',
            'title_attr' => 'Intro'
        ),
        'settings' => array(
            'title' => 'Settings'
        ),
        'invalidation' => array(
            'title' => 'Cache Invalidation'
        )
    );

    /**
     * Load controller
     *
     * @param  Core       $Core Core controller instance.
     * @return Controller Controller instance.
     */
    public static function &load(Core $Core)
    {
        // instantiate controller
        return parent::construct($Core, array(
            'AdminView',
            'options'
        ));
    }

    /**
     * Setup controller
     */
    protected function setup()
    {
        // settings link on plugin index
        add_filter('plugin_action_links_' . $this->core->modules('cloudfront')->basename(), array($this, 'settings_link'));

        // meta links on plugin index
        add_filter('plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2);

        // title on plugin index
        add_action('pre_current_active_plugins', array( $this, 'plugin_title'), 10);

        // admin options page
        add_action('admin_menu', array($this, 'admin_menu'), 50);

        // reorder menu
        add_filter('custom_menu_order', array($this, 'reorder_menu'), 100);
      
        // enqueue scripts
        add_action('admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), $this->first_priority);

        // upgrade
        add_action('plugins_loaded', array( $this, 'plugin_upgrade'), 100);
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

        $module_url = $this->core->modules('cloudfront')->dir_url();
        $version = $this->core->modules('cloudfront')->version();

        // preload menu icon?>
<link rel="preload" href="<?php print $module_url; ?>admin/images/aws-block.svg" as="image" type="image/svg+xml" />
<?php

        // global admin script
        wp_enqueue_script("o10n-cloudfront-page-cache-menu", $module_url . 'admin/js/view-cloudfront-admin.js', array( 'jquery' ), $version);
        wp_localize_script("o10n-cloudfront-page-cache-menu", "o10n_cloudfront_dir", $module_url);
    }

    /**
     * Admin menu option
     */
    public function admin_menu()
    {
        global $submenu;

        // WPO plugin or more than 1 optimization module, add to optimization menu
        if (defined('O10N_WPO_VERSION') || count($this->core->modules()) > 1) {
            add_submenu_page('o10n', __('CloudFront Page Cache', 'o10n'), __('CloudFront', 'o10n'), 'manage_options', 'o10n-cloudfront', array(
                 &$this->AdminView,
                 'display'
             ));

            // change base to admin.php
            $this->admin_base = 'admin.php';
        } else {

            // add menu entry
            add_submenu_page('options-general.php', __('CloudFront Page Cache', 'o10n'), __('CloudFront', 'o10n'), 'manage_options', 'o10n-cloudfront', array(
                 &$this->AdminView,
                 'display'
             ));
        }
    }

    /**
     * Settings link on plugin overview.
     *
     * @param  array $links Plugin settings links.
     * @return array Modified plugin settings links.
     */
    final public function settings_link($links)
    {
        $settings_link = '<a href="'.esc_url(add_query_arg(array('page' => 'o10n-cloudfront','tab' => 'settings'), admin_url($this->admin_base))).'">'.__('Settings').'</a>';
        array_unshift($links, $settings_link);

        return $links;
    }

    /**
     * Show row meta on the plugin screen.
     */
    final public function plugin_row_meta($links, $file)
    {
        if ($file == $this->core->modules('cloudfront')->basename()) {
            $lgcode = strtolower(get_locale());
            if (strpos($lgcode, '_') !== false) {
                $lgparts = explode('_', $lgcode);
                $lgcode = $lgparts[0];
            }
            if ($lgcode === 'en') {
                $lgcode = '';
            }

            $row_meta = array(
                'o10n_cloudfront_console' => '<a href="' . esc_url('https://console.aws.amazon.com/cloudfront/home') . '" target="_blank" title="' . esc_attr(__('CloudFront Console', 'o10n')) . '">' . __('CloudFront Console', 'o10n') . '</a>',

                'o10n_cloudfront_forum' => '<a href="' . esc_url('https://forums.aws.amazon.com/forum.jspa?forumID=46') . '" target="_blank" title="' . esc_attr(__('CloudFront Support Forum', 'o10n')) . '" style="font-weight:bold;color:#E47911;">' . __('AWS Support Forum', 'o10n') . '</a>'
            );

            return array_merge($links, $row_meta);
        }

        return (array) $links;
    }

    /**
     * Plugin title modification
     */
    public function plugin_title()
    {
        ?><script>jQuery(function($){var r=$('*[data-plugin="<?php print $this->core->modules('cloudfront')->basename(); ?>"]');
            $('.plugin-title strong',r).html('<?php print $this->core->modules('cloudfront')->name(); ?><a href="https://optimization.team" class="g100" style="font-size: 10px;float: right;font-weight: normal;opacity: .2;line-height: 14px;">O10N</span>');
});</script><?php
    }

    /**
     * Reorder menu
     */
    public function reorder_menu($menu_order)
    {
        global $submenu;

        // move to end of list
        if (defined('O10N_WPO_VERSION') || count($this->core->modules()) > 1) {
            $menukey = 'o10n';
        } else {
            $menukey = 'options-general.php';
        }
        foreach ($submenu[$menukey] as $key => $item) {
            if ($item[2] === 'o10n-cloudfront') {
                $submenu[$menukey][] = $item;
                unset($submenu[$menukey][$key]);
            }
        }
    }

    /**
     * Upgrade plugin
     */
    final public function plugin_upgrade()
    {
        if ($this->core->modules('cloudfront')->version() === '1.0.3') {
            $old_options = get_option('cf-page-cache', false);

            // convert old options to new options
            if ($old_options) {
                $options = array(
                    'cloudfront.enabled' => (isset($old_options['enabled'])) ? $old_options['enabled'] : false,
                    'cloudfront.host' => (isset($old_options['host'])) ? $old_options['host'] : '',
                    'cloudfront.origin' => (isset($old_options['origin'])) ? $old_options['origin'] : '',
                    'cloudfront.domain' => (isset($old_options['domain'])) ? $old_options['domain'] : '',
                    'cloudfront.max_age' => (isset($old_options['cache_age'])) ? $old_options['cache_age'] : '',
                    'cloudfront.invalidation.enabled' => (isset($old_options['invalidation'])) ? $old_options['invalidation'] : false,
                    'cloudfront.invalidation.distribution_id' => (isset($old_options['distribution_id'])) ? $old_options['distribution_id'] : '',
                    'cloudfront.invalidation.api_key' => (isset($old_options['api_key'])) ? $old_options['api_key'] : '',
                    'cloudfront.invalidation.api_secret' => (isset($old_options['api_secret'])) ? $old_options['api_secret'] : '',
                    'cloudfront.invalidation.aws_region' => (isset($old_options['aws_region'])) ? $old_options['aws_region'] : ''
                );

                $this->options->save($options);

                delete_option('cf-page-cache');
                delete_option('cf-page-cache-version');
            }
        }
    }
}
