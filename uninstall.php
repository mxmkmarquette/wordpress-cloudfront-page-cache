<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://pagespeed.pro/
 *
 * @package    cloudfront-page-cache
 */

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Remove settings
 */
delete_option('cf-page-cache');
delete_option('cf-page-cache-version');
delete_option('cf-page-cache-invalidation-count');
delete_option('cf-page-cache-invalidations-inprogress');
delete_option('cf-page-cache-last-invalidation');
