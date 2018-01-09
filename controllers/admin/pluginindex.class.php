<?php
namespace CloudFrontPageCache;

/**
 * Plugin Index List Controller
 *
 * @package    cloudfront-page-cache
 * @subpackage cloudfront-page-cache/controllers/admin
 * @author     PageSpeed.pro <info@pagespeed.pro>
 */
if (!defined('ABSPATH')) {
    exit;
}

class AdminPluginIndex extends Controller implements Controller_Interface
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
        
        // settings link on plugin index
        add_filter('plugin_action_links_cloudfront-page-cache/cloudfront-page-cache.php', array($this, 'settings_link'));

        // meta links on plugin index
        add_filter('plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2);

        // title on plugin index
        add_action('pre_current_active_plugins', array( $this, 'plugin_title'), 10);
    }

    /**
     * Settings link on plugin overview.
     *
     * @param  array $links Plugin settings links.
     * @return array Modified plugin settings links.
     */
    final public function settings_link($links)
    {
        $settings_link = '<a href="admin.php?page=cloudfront-page-cache">'.__('Settings').'</a>';
        array_unshift($links, $settings_link);

        return $links;
    }

    /**
     * Show row meta on the plugin screen.
     */
    public static function plugin_row_meta($links, $file)
    {
        if ($file == CF_FPC_PLUGIN) {
            $lgcode = strtolower(get_locale());
            if (strpos($lgcode, '_') !== false) {
                $lgparts = explode('_', $lgcode);
                $lgcode = $lgparts[0];
            }
            if ($lgcode === 'en') {
                $lgcode = '';
            }

            $row_meta = array(
                'cloudfrontpagecache_scores' => '<a href="' . esc_url('https://console.aws.amazon.com/cloudfront/home') . '" target="_blank" title="' . esc_attr(__('Amazon AWS CloudFront Console', 'cloudfront-page-cache')) . '">' . __('AWS CloudFront Console', 'cloudfront-page-cache') . '</a>'
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
        ?><script>
jQuery(function() { if (typeof cfpagecachedir !== 'undefined') { var img = '<img src="' + cfpagecachedir + 'admin/images/aws-block.svg" style="width:48px;height:48px;" align="absmiddle" title="AWS CloudFront Page Cache" style="margin-right:2px;">'; } else { var img = ''; } jQuery('*[data-plugin="cloudfront-page-cache/cloudfront-page-cache.php"] .plugin-title strong').html(img + ' CloudFront Page Cache'); });
</script><?php
    }
}
