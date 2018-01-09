<?php
namespace CloudFrontPageCache;

/**
 * Speed tests admin template
 *
 * @package    cloudfront-page-cache
 * @subpackage optimization/admin
 * @author     Optimization.Team <info@optimization.team>
 */

if (!defined('ABSPATH')) {
    exit;
}

$lgcode = strtolower(get_locale());
if (strpos($lgcode, '_') !== false) {
    $lgparts = explode('_', $lgcode);
    $lgcode = $lgparts[0];
    $google_intlcode = $lgparts[0] . '-' . $lgparts[1];
}
if ($lgcode === 'en') {
    $lgcode = '';
}

$google_lgcode = $lgcode;
if (!$google_intlcode) {
    $google_intlcode = 'en-us';
}

$public_host = $this->options->get('host');
$origin_host = $this->options->get('origin');
$url = str_replace('//'.$origin_host, '//'.$public_host, home_url());
$thinkhost = 'https://testmysite.' . (($google_intlcode === 'en-us') ? 'think' : '') . 'withgoogle.com/';
$thinkurl = $thinkhost . 'intl/'.$google_intlcode.'?url=' . urlencode($url);

$currenturl = home_url();
// use public host
$currenturl_host = $this->options->get('host');
if (!$currenturl_host) {
    // parse url
    $currenturl_host = parse_url($currenturl, PHP_URL_HOST);
} else {
    $currenturl = str_replace($this->options->get('origin'), $currenturl_host, $currenturl);
}

?>

<a href="<?php print esc_url($thinkurl);?>" target="_blank" rel="noopener"><img src="<?php print plugins_url('admin/images/google-ai-benchmark.png', 'cloudfront-page-cache/cloudefront-page-cache.php'); ?>" border="0" style="float:right;border:1px solid #e5e5e5;border-radius:2px;"></a>
<h1>International Website Speed Tests</h1>

<p>The following free services enable to test the international performance of the CloudFront page cache installation. For SEO purposes, we advise to achieve an <span style="color:#079c2d;font-weight:bold;">Excellent</span> score in Google's latest <a href="<?php print esc_url($thinkurl);?>" target="_blank" rel="noopener">AI + benchmark based mobile speed test</a>.</p>

<div class="clearfix"></div>

<h2 style="line-height:25px;">Securi International Speed test <a href="<?php print esc_url('https://performance.sucuri.net/domain/' . $currenturl_host . '?utm_source=wordpress&utm_medium=plugin&utm_term=optimization&utm_campaign=CloudFront%20Page%20Cache'); ?>" target="_blank" rel="noopener" class="button button-small">Start Test</a></h2>
<h2 style="line-height:25px;">KeyCDN Speed test <a href="<?php print esc_url('https://tools.keycdn.com/speed?url='.urlencode($currenturl).'&utm_source=wordpress&utm_medium=plugin&utm_term=optimization&utm_campaign=CloudFront%20Page%20Cache'); ?>" target="_blank" rel="noopener" class="button button-small">Start Test</a></h2>
<h2 style="line-height:25px;">Uptrends Speed test <a href="<?php print esc_url('https://www.uptrends.com/tools/website-speed-test?url='.urlencode($currenturl).'&utm_source=wordpress&utm_medium=plugin&utm_term=optimization&utm_campaign=CloudFront%20Page%20Cache'); ?>" target="_blank" rel="noopener" class="button button-small">Start Test</a></h2>
<h2 style="line-height:25px;">Dotcom-Tools.com Speed test <a href="<?php print esc_url('https://www.dotcom-tools.com/website-speed-test.aspx?url='.urlencode($currenturl).'&utm_source=wordpress&utm_medium=plugin&utm_term=optimization&utm_campaign=CloudFront%20Page%20Cache'); ?>" target="_blank" rel="noopener" class="button button-small">Start Test</a></h2>

<hr />

<h2 style="line-height:25px;">WebPageTest.org Speed test <a href="<?php print esc_url('https://www.webpagetest.org/?url='.urlencode($currenturl).'&utm_source=wordpress&utm_medium=plugin&utm_term=optimization&utm_campaign=CloudFront%20Page%20Cache'); ?>" target="_blank" rel="noopener" class="button button-small">Start Test</a></h2>

<h2 style="line-height:25px;">Google Page Speed test <a href="<?php print esc_url('https://developers.google.com/speed/pagespeed/insights/?url=' . urlencode($currenturl)); ?>" target="_blank" rel="noopener" class="button button-small">Start Test</a></h2>

<h2 style="line-height:25px;">Google AI based mobile speed test <a href="<?php print esc_url($thinkurl); ?>" target="_blank" rel="noopener" class="button button-small">Start Test</a></h2>

<h2 style="line-height:25px;">GTMetrix test <a href="<?php print esc_url('https://gtmetrix.com/?url='.urlencode($currenturl).'&utm_source=wordpress&utm_medium=plugin&utm_term=optimization&utm_campaign=CloudFront%20Page%20Cache'); ?>" target="_blank" rel="noopener" class="button button-small">Start Test</a></h2>

<hr />
For an indepth website performance analysis, take a look at <a href="https://developers.google.com/web/tools/lighthouse/" target="_blank" rel="noopener">Google Lighthouse</a>.