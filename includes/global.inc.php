<?php
namespace CloudFrontPageCache;

/**
 * Global functions
 *
 * @package    cloudfront-page-cache
 * @subpackage cloudfront-page-cache/controllers
 * @author     Optimization.Team <info@optimization.team>
 */

// set CloudFront cache age for page
function set_age($age)
{
    Core::get('pagecache')->set_age($age);
}

// set CloudFront cache expire date
function set_expire($timestamp)
{
    Core::get('pagecache')->set_expire($timestamp);
}
