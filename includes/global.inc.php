<?php
namespace O10n\CloudFront;

/**
 * Global functions
 *
 * @package    optimization
 * @subpackage optimization/controllers
 * @author     Optimization.Team <info@optimization.team>
 */

if (function_exists(__NAMESPACE__ . '\set_max_age')) {
    return;
}

// set CloudFront cache age for page
function set_max_age($age)
{
    \O10n\Core::get('cloudfront')->set_max_age($age);
}

// set CloudFront cache expire date
function set_expire($timestamp)
{
    \O10n\Core::get('cloudfront')->set_expire($timestamp);
}

// set CloudFront no cache
function nocache()
{
    \O10n\Core::get('cloudfront')->set_max_age(-1);
}
