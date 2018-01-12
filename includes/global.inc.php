<?php
namespace CloudFrontPageCache;

/**
 * Global functions
 *
 * @package    cloudfront-page-cache
 * @subpackage cloudfront-page-cache/controllers
 * @author     Optimization.Team <info@optimization.team>
 */

// return optimization controller
function instance($controller = false)
{
    return Core::get_instance($controller);
}

// set CloudFront cache age for page
function set_age($age)
{
    Core::get_instance('pagecache')::set_age($age);
}

// set CloudFront cache expire date
function set_expire($timestamp)
{
    Core::get_instance('pagecache')::set_expire($timestamp);
}
