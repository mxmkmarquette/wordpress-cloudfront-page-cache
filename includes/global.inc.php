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
