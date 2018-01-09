<?php
/**
 * CloudFront Page Cache CDN
 *
 * Low cost and high performance page cache based on Amazon's CloudFront CDN that provides international fast website speed and dedicated geographic IP's for local SEO advantage.
 *
 * @link              https://pagespeed.pro/
 * @since             1.0
 * @package           optimization
 *
 * @wordpress-plugin
 *
 * Plugin Name:       CloudFront Page Cache CDN
 * Description:       Low cost and high performance page cache based on Amazon's CloudFront CDN that provides international fast website speed and dedicated geographic IP's for local SEO advantage.
 * Version:           1.0.0
 * Author:            PageSpeed.pro
 * Author URI:        https://pagespeed.pro/
 * Text Domain:       cloudfront-page-cache
 * Domain Path:       /languages
 */

define('CF_FPC_VERSION', '1.0.0');
define('CF_FPC_URI', plugin_dir_url(__FILE__));
define('CF_FPC_PATH', plugin_dir_path(__FILE__));
define('CF_FPC_PLUGIN', plugin_basename(__FILE__));
define('CF_FPC_SELF', __FILE__);

if (! defined('WPINC')) {
    die;
}

// abort loading during upgrades
if (defined('WP_INSTALLING') && WP_INSTALLING) {
    return;
}

// require PHP 5.3+
if (version_compare(PHP_VERSION, '5.3', '<')) {
    add_action('admin_notices', create_function('', "echo '<div class=\"error\"><p>".__('The CloudFront Page Cache plugin requires PHP 5.3 to function properly. Please upgrade PHP or deactivate this plugin.', 'cloudfront-page-cache') ."</p></div>';"));

    return;
} else {
    try {

        // load the core plugin class
        require CF_FPC_PATH . 'controllers/core.class.php';
        
        // load CloudFront page cache controller
        CloudFrontPageCache\Core::load();

        // catch CloudFrontPageCache errors
    } catch (Exception $err) {

        // plugin failed to load
        if (is_admin()) {
            add_action('admin_notices', create_function('', "echo '<div class=\"error\"><h1>".__('CloudFront Page Cache plugin failed to load', 'cloudfront-page-cache') ."</h1><p>".$err->getMessage()."</p></div>';"), (PHP_INT_MAX * -1));
        }

        // write error to log
        error_log('CloudFront Page Cache: failed to load on ' . parse_url($_SERVER['REQUEST_URI'] . ' | Error: '.$err->getMessage(), PHP_URL_PATH));

        return;

        // catch other exceptions (from dependencies, libraries etc.)
    } catch (\Exception $err) {

        // add admin notice
        if (is_admin()) {
            add_action('admin_notices', create_function('', "echo '<div class=\"error\"><h1>".__('CloudFront Page Cache experienced a problem when loading a dependency.', 'cloudfront-page-cache') ."</h1><p>".$err->getMessage()."</p></div>';"), (PHP_INT_MAX * -1));
        }
        
        // write error to log
        error_log('CloudFront Page Cache: failed to load dependency on ' . parse_url($_SERVER['REQUEST_URI'] . ' | Error: '.$err->getMessage(), PHP_URL_PATH));

        return;
    }
    
    // load public functions in global scope
    require CF_FPC_PATH . 'includes/global.inc.php';
}
