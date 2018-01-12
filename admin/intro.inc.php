<?php
namespace CloudFrontPageCache;

/**
 * Intro admin template
 *
 * @package    cloudfront-page-cache
 * @subpackage optimization/admin
 * @author     Optimization.Team <info@optimization.team>
 */

if (!defined('ABSPATH')) {
    exit;
}

// get budget stats
$budget_stats = $this->AdminInvalidation->budget_stats();

$budget_color = '';
if ($budget_stats['percentage'] >= 90) {
    $budget_color = 'color:red;font-weight:bold;';
} elseif ($budget_stats['percentage'] >= 80) {
    $budget_color = 'color:maroon;';
}

?>
<div class="wrap cloudfrontpagecache-wrapper">

	<div class="metabox-prefs"> 
		<div class="wrap about-wrap" style="position:relative;">
			<h1>CloudFront Page Cache v<?php print CF_FPC_VERSION; ?></h1>

			<p class="about-text" style="min-height:auto;">Thank you for using the international page cache plugin by <a href="https://pagespeed.pro/" target="_blank" rel="noopener" style="color:black;text-decoration:none;">PageSpeed.<span class="g100" style="padding-top:1px;padding-bottom:0px;">PRO</span></a>.</p>

			<div style="position:absolute;top:0px;right:0px;text-align:center;">
				<a href="https://aws.amazon.com/cloudfront/?<?php print $this->aws_tracking; ?>" target="_blank" rel="noopener"><img src="<?php print plugins_url('admin/images/aws-cloudfront-100.png', 'cf-page-cache/cf-page-cache.php'); ?>" border="0" style="border:0px;"></a>
			</div>

			<h2 class="nav-tab-wrapper wp-clearfix" style="padding-bottom:0px;">
				<a href="<?php print esc_url(add_query_arg(array( 'page' => 'cloudfront-page-cache' ), admin_url('options-general.php'))); ?>" class="nav-tab nav-tab-active">Welcome</a>
				<a href="<?php print esc_url(add_query_arg(array( 'page' => 'cloudfront-page-cache', 'view' => 'settings' ), admin_url('options-general.php'))); ?>" class="nav-tab">Settings</a>
				<a href="<?php print esc_url(add_query_arg(array( 'page' => 'cloudfront-page-cache', 'view' => 'setup' ), admin_url('options-general.php'))); ?>" class="nav-tab">Setup Guide</a>
				<a href="<?php print esc_url(add_query_arg(array( 'page' => 'cloudfront-page-cache', 'view' => 'tests' ), admin_url('options-general.php'))); ?>" class="nav-tab">Speed Tests</a>
				<?php if ($this->options->bool('invalidation')) {
    ?>
    <a href="<?php print esc_url(add_query_arg(array( 'page' => 'cloudfront-page-cache', 'view' => 'invalidation' ), admin_url('options-general.php'))); ?>" class="nav-tab">Invalidation
    <span style="float:right;font-size:10px;line-height:12px;margin-left:10px;display:block;text-align:right;font-weight:normal;">
        Budget <?php print date('M \'y', current_time('timestamp')); ?><br /><span style="<?php print $budget_color; ?>"><?php print (string)$budget_stats['usage']; ?></span><?php if ($budget_stats['costs'] === 0) {
        print '/' . (string)$this->AdminInvalidation->budget();
    } else {
        print '/1k';
    }
    if ($budget_stats['costs']) {
        print ' (+$' . number_format_i18n($budget_stats['costs'], 2) . ')';
    } ?></span>
    </a>
	<?php
} ?>
			</h2>

			<p>This plugin provides a low cost and high performance international page cache solution based on <a href="https://aws.amazon.com/cloudfront/?<?php print $this->aws_tracking; ?>" target="_blank" rel="noopener">Amazon AWS CloudFront CDN</a>.</p>
			<p>This is the first version of the plugin but the underlying technologies have been tested for over 5 years and some of our clients have achieved long term top 10 positions in Google in over 20 countries using a single server in Amsterdam.</p>
			<p>A big advantage of using CloudFront as a page cache for international SEO is that Amazon provides dedicated IP's for geographic regions. This means that a website will physically load from a location near the visitor. For a visitor from Sweden the website may be physically loaded from a server and IP in Stockholm.</p>
			<p><img src="<?php print plugins_url('admin/images/pagespeed-aws-cloudfront.png', 'cf-page-cache/cf-page-cache.php'); ?>"></p>

			<p>An other advantage of Amazon AWS CloudFront as a page cache is that they provide the lowest costs. For an average business website, the total costs will litterally be less than 1 dollar per month. Amazon provides free SSL certificates and there are no hidden costs.</p>
			<p>Amazon AWS CloudFront is among the <a href="https://encrypted.google.com/search?q=cloudfront+vs" target="_blank" rel="noopener">fastest</a> CDN providers available with the greatest global network. This makes it a perfect option for any website that wants to reach an international audience or that simply wants a fast and secure page cache for a low cost VPS.</p>

			<p><img src="<?php print plugins_url('admin/images/aws-cloudfront-network-2017.png', 'cf-page-cache/cf-page-cache.php'); ?>"></p>

			<h2>Solution for emerging markets</h2>
			<p>Internet connectivity, speed and reliability are a major issue in some regions of the world affecting hundreds of millions of people. Regions such as Asia, India and Indonesia may also have many innovators and small business startups who produce or sell products that could be very attractive to other regions of the world, but they may lack financial resources to reach customers beyond their local market.</p>

			<p>The CloudFront page cache solution makes it possible to solve slow and unreliable internet issues for just $0.05 USD in total costs per month for a small blog. This plugin enables to use a 5 USD VPS for a heavy WordPress + WooCommerce installation while being capable of handling thousands of visitors per day (with a fast page speed and good results in Google) for just $0.50+ USD per month in AWS costs. The solution also enables a website to grow from 100 visitors per day to 100.000 visitors per day without a problem (besides costs). For a small business website, the total costs will be about $0.05 to $0.10 USD per month while international website speed + Google rankings are of high value.</p>

			<h3>Demo website</h3>
			<p>An example is our demo website <a href="https://www.e-scooter.co/?utm_source=wordpress&amp;utm_medium=plugin&amp;utm_term=optimization&amp;utm_campaign=cloudfront-page-cache" target="_blank" rel="noopener">www.e-scooter.co</a> which is hosted on a cheap VPS in Switzerland. The website was created in July 2017 and it already has #1 positions in Google for premium search terms in the U.S., India and other regions. It receives hundreds of visitors per day who browse many pages. The total CloudFront bill for December 2017, including www.pagespeed.pro, www.fastestwebsite.co and some other websites was $0.74 USD.</p>

			<p>We are interested to learn about your experiences and feedback when using this plugin. Please submit your feedback to <a href="mailto:info@pagespeed.pro">info@pagespeed.pro</a></p>
			<p>If you are happy with the plugin, please consider to <a href="https://wordpress.org/support/plugin/cf-page-cache/reviews/" target="_blank" rel="noopener">write a review</a>.</p>

		</div>
	</div>
	</div>
