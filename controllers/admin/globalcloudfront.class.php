<?php
namespace O10n;

/**
 * Global CloudFront Page Cache Admin Controller
 *
 * @package    optimization
 * @subpackage optimization/controllers/admin
 * @author     Optimization.Team <info@optimization.team>
 */
if (!defined('ABSPATH')) {
    exit;
}

class AdminGlobalcloudfront extends ModuleAdminController implements Module_Admin_Controller_Interface
{

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
            'options'
        ));
    }

    /**
     * Setup controller
     */
    protected function setup()
    {

        // add admin bar menu
        add_action('admin_bar_menu', array( $this, 'admin_bar'), 100);
    }

    /**
     * Admin bar option
     *
     * @param  object       Admin bar object
     */
    final public function admin_bar($admin_bar)
    {
        // current url
        if (is_admin()
            || (defined('DOING_AJAX') && DOING_AJAX)
            || in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'))
        ) {
            $currenturl = home_url();
        } else {
            $currenturl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }

        // WPO plugin or more than 1 optimization module, add to optimization menu
        if (defined('O10N_WPO_VERSION') || count($this->core->modules()) > 1) {
            $admin_bar->add_node(array(
                'parent' => 'o10n',
                'id' => 'o10n-cloudfront',
                'title' => '<img src="' . $this->core->modules('cloudfront')->dir_url() . 'admin/images/aws-block.svg" style="width:16px;height:16px;margin-right:.2em;" align="absmiddle" alt="CloudFront" style="margin-right:2px;"> ' . __('Page Cache', 'o10n') . '',
                'href' => add_query_arg(array( 'page' => 'o10n-cloudfront' ), admin_url('admin.php'))
            ));

            $admin_base = 'admin.php';
        } else {
            $admin_bar->add_menu(array(
                'id' => 'o10n-cloudfront',
                'title' => '<span class="ab-label"><img src="' . $this->core->modules('cloudfront')->dir_url() . 'admin/images/aws-block.svg" style="width:16px;height:16px;margin-top:-2px;" align="absmiddle" alt="CloudFront"></span>',
                'href' => add_query_arg(array( 'page' => 'o10n-cloudfront' ), admin_url('options-general.php')),
                'meta' => array( 'title' => __('CloudFront Page Cache', 'o10n'), 'class' => 'ab-sub-secondary' )
            ));

            $admin_base = 'options-general.php';
        }

        // use public host
        $currenturl_host = $this->options->get('cloudfront.host');
        if (!$currenturl_host) {
            // parse url
            $currenturl_host = parse_url($currenturl, PHP_URL_HOST);
        } else {
            $currenturl = str_replace($this->options->get('cloudfront.origin'), $currenturl_host, $currenturl);
        }

        $settings_url = add_query_arg(array( 'page' => 'o10n-cloudfront' ), admin_url($admin_base));

        if (!defined('O10N_WPO_VERSION') || count($this->core->modules()) <= 1) {

            // title header
            $admin_bar->add_group(array(
                'parent' => 'o10n-cloudfront',
                'id' => 'o10n-cloudfront-top'
            ));
            $admin_bar->add_node(array(
                'parent' => 'o10n-cloudfront-top',
                'id' => 'o10n-cloudfront-title',
                'title' => '<span style="font-weight:bold;">' . __('CloudFront Page Cache', 'o10n') . '</span>',
                'meta' => array( 'title' => __('Clear CloudFront cache for current page.', 'o10n') )
            ));
        }

        if ($this->options->bool('cloudfront.invalidation.enabled')) {
            
            // cache invalidation group
            $admin_bar->add_group(array(
                'parent' => 'o10n-cloudfront',
                'id' => 'o10n-cloudfront-second',
                'meta' => array(
                    'class' => 'ab-sub-secondary',
                )
            ));

            if (!is_admin()) {

                // path to invalidate
                $path = preg_replace('#http(s):\/\/[^\/]+(/|$)#Ui', '/', $currenturl);

                $admin_bar->add_node(array(
                    'parent' => 'o10n-cloudfront-second',
                    'id' => 'o10n-cloudfront-clear-page',
                    'title' => '<span class="dashicons dashicons-trash o10n-menu-icon"></span> ' . __('Invalidate Page', 'o10n'),
                    'href' => add_query_arg(array( 'page' => 'o10n-cloudfront', 'tab' => 'invalidation', 'purge' => 'page', 'path' => $path, 'return' => $currenturl, 't' => time() ), admin_url($admin_base)),
                    'meta' => array( 'title' => __('Clear CloudFront cache for current page.', 'o10n'), 'onclick' => 'return o10n_cloudfront_confirm_purge(this);' )
                ));
            }

            $admin_bar->add_node(array(
                'parent' => 'o10n-cloudfront-second',
                'id' => 'o10n-cloudfront-clear',
                'title' => '<span class="dashicons dashicons-trash o10n-menu-icon"></span> ' . __('Invalidate All /*', 'o10n'),
                'href' => add_query_arg(array( 'page' => 'o10n-cloudfront', 'tab' => 'invalidation', 'purge' => 'cf', 'return' => (is_admin()) ? false : $currenturl, 't' => time() ), admin_url($admin_base)),
                'meta' => array( 'title' => __('Clear CloudFront cache for all pages.', 'o10n'), 'onclick' => 'return o10n_cloudfront_confirm_purge(this);' )
            ));

            $admin_bar->add_node(array(
                'parent' => 'o10n-cloudfront-second',
                'id' => 'o10n-cloudfront-clear-plugins',
                'title' => '<span class="dashicons dashicons-trash o10n-menu-icon"></span> ' . __('Purge Plugin Caches', 'o10n'),
                'href' => add_query_arg(array( 'page' => 'o10n-cloudfront', 'tab' => 'invalidation', 'purge' => 'plugins', 'return' => (is_admin()) ? false : $currenturl, 't' => time() ), admin_url($admin_base)),
                'meta' => array( 'title' => __('Clear the cache of page cache related plugins such as Autoptimize, WP Super Cache and others.', 'o10n'), 'onclick' => 'return o10n_cloudfront_confirm_purge(this);' )
            ));

            $admin_bar->add_node(array(
                'parent' => 'o10n-cloudfront-second',
                'id' => 'o10n-cloudfront-clear-all',
                'title' => '<span class="dashicons dashicons-trash o10n-menu-icon"></span> ' . __('Invalidate All + Plugin Caches', 'o10n'),
                'href' => add_query_arg(array( 'page' => 'o10n-cloudfront', 'tab' => 'invalidation', 'purge' => 'all', 'return' => (is_admin()) ? false : $currenturl, 't' => time() ), admin_url($admin_base)),
                'meta' => array( 'title' => __('Invalidate all pages on CloudFront (/*) + clear the cache of plugins such as Autoptimize, WP Super Cache and others.', 'o10n'), 'onclick' => 'return o10n_cloudfront_confirm_purge(this);' )
            ));
        }

        // support
        $admin_bar->add_node(array(
            'parent' => 'o10n-cloudfront',
            'id' => 'o10n-cloudfront-support',
            'title' => '<span class="dashicons dashicons-phone o10n-menu-icon"></span> ' . __('AWS CloudFront Support', 'o10n'),
            'href' => 'https://forums.aws.amazon.com/forum.jspa?forumID=46',
            'meta' => array( 'title' => __('AWS CloudFront Support', 'o10n'), 'target' => '_blank' )
        ));

        // console
        $admin_bar->add_node(array(
            'parent' => 'o10n-cloudfront',
            'id' => 'o10n-cloudfront-console',
            'title' => '<span class="dashicons dashicons-admin-generic o10n-menu-icon"></span> ' . __('AWS CloudFront Console', 'o10n'),
            'href' => 'https://console.aws.amazon.com/cloudfront/home',
            'meta' => array( 'title' => __('AWS CloudFront Console', 'o10n'), 'target' => '_blank' )
        ));

        $admin_bar->add_node(array(
            'parent' => 'o10n-cloudfront',
            'id' => 'o10n-cloudfront-speed-tests',
            'title' => '<span class="dashicons dashicons-dashboard o10n-menu-icon"></span> ' . __('Speed Tests', 'o10n'),
            'href' => false,
            'meta' => array( 'title' => __('Speed Tests', 'o10n') )
        ));

        $admin_bar->add_node(array(
            'parent' => 'o10n-cloudfront-speed-tests',
            'id' => 'o10n-cloudfront-securi-speed-test',
            'title' => __('Securi', 'o10n'),
            'href' => 'https://performance.sucuri.net/domain/' . $currenturl_host . '?utm_source=wordpress&utm_medium=plugin&utm_term=optimization&utm_campaign=CloudFront%20Page%20Cache',
            'meta' => array( 'title' => __('Securi Speed Test', 'o10n'), 'target' => '_blank' )
        ));

        $admin_bar->add_node(array(
            'parent' => 'o10n-cloudfront-speed-tests',
            'id' => 'o10n-cloudfront-keycdn-speed-test',
            'title' => __('KeyCDN', 'o10n'),
            'href' => 'https://tools.keycdn.com/speed?url='.urlencode($currenturl).'&utm_source=wordpress&utm_medium=plugin&utm_term=optimization&utm_campaign=CloudFront%20Page%20Cache',
            'meta' => array( 'title' => __('KeyCDN Speed Test', 'o10n'), 'target' => '_blank' )
        ));

        $admin_bar->add_node(array(
            'parent' => 'o10n-cloudfront-speed-tests',
            'id' => 'o10n-cloudfront-uptrends-speed-test',
            'title' => __('Uptrends', 'o10n'),
            'href' => 'https://www.uptrends.com/tools/website-speed-test?url='.urlencode($currenturl).'&utm_source=wordpress&utm_medium=plugin&utm_term=optimization&utm_campaign=CloudFront%20Page%20Cache',
            'meta' => array( 'title' => __('Uptrends Speed Test', 'o10n'), 'target' => '_blank' )
        ));

        $admin_bar->add_node(array(
            'parent' => 'o10n-cloudfront-speed-tests',
            'id' => 'o10n-cloudfront-dotcom-speed-test',
            'title' => __('Dotcom-Tools.com', 'o10n'),
            'href' => 'https://www.dotcom-tools.com/website-speed-test.aspx?url='.urlencode($currenturl).'&utm_source=wordpress&utm_medium=plugin&utm_term=optimization&utm_campaign=CloudFront%20Page%20Cache',
            'meta' => array( 'title' => __('Dotcom-Tools Speed Test', 'o10n'), 'target' => '_blank' )
        ));

        $admin_bar->add_node(array(
            'parent' => 'o10n-cloudfront-speed-tests',
            'id' => 'o10n-cloudfront-webpagetest',
            'title' => __('WebPageTest.org', 'o10n'),
            'href' => 'https://www.webpagetest.org/?url='.urlencode($currenturl).'&utm_source=wordpress&utm_medium=plugin&utm_term=optimization&utm_campaign=CloudFront%20Page%20Cache',
            'meta' => array( 'title' => __('WebPageTest.org Speed Test', 'o10n'), 'target' => '_blank' )
        ));

        $admin_bar->add_node(array(
            'parent' => 'o10n-cloudfront-speed-tests',
            'id' => 'o10n-cloudfront-gtmetrix',
            'title' => __('GTMetrix', 'o10n'),
            'href' => 'https://gtmetrix.com/?url='.urlencode($currenturl).'&utm_source=wordpress&utm_medium=plugin&utm_term=optimization&utm_campaign=CloudFront%20Page%20Cache',
            'meta' => array( 'title' => __('GTMetrix Speed Test', 'o10n'), 'target' => '_blank' )
        ));
    }
}
