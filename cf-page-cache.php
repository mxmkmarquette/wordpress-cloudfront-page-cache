<?php
namespace O10n;

/**
 * CloudFront Page Cache CDN
 *
 * Low cost and high performance page cache based on Amazon's CloudFront CDN. CloudFront provides international fast website speed and dedicated geographic IP's.
 *
 * @link              https://github.com/o10n-x/
 * @package           o10n
 *
 * @wordpress-plugin
 * Plugin Name:       CloudFront Page Cache CDN
 * Description:       Low cost and high performance page cache based on Amazon's CloudFront CDN. CloudFront provides international fast website speed and dedicated geographic IP's.
 * Version:           1.0.6
 * Author:            Optimization.Team
 * Author URI:        https://optimization.team/
 * Text Domain:       o10n
 * Domain Path:       /languages
 */

if (! defined('WPINC')) {
    die;
}

// abort loading during upgrades
if (defined('WP_INSTALLING') && WP_INSTALLING) {
    return;
}

// settings
$module_version = '1.0.6';
$minimum_core_version = '0.0.12';
$plugin_path = dirname(__FILE__);

// load the optimization module loader
if (!class_exists('\O10n\Module')) {
    require $plugin_path . '/core/controllers/module.php';
}

// load module
new Module(
    'cloudfront',
    'CloudFront Page Cache',
    $module_version,
    $minimum_core_version,
    array(
        'core' => array(
            'cloudfront'
        ),
        'admin' => array(
            'AdminCloudfront',
            'AdminCloudfrontinvalidation'
        ),
        'admin_global' => array(
            'AdminGlobalcloudfront'
        )
    ),
    false,
    array(),
    __FILE__
);

// load public functions in global scope
require $plugin_path . '/includes/global.inc.php';
