<?php
namespace CloudFrontPageCache;

/**
 * Invalidation admin template
 *
 * @package    cloudfront-page-cache
 * @subpackage optimization/admin
 * @author     Optimization.Team <info@optimization.team>
 */
if (!defined('ABSPATH') || !defined('CF_FPC_ADMIN')) {
    exit;
}

// get options
$options = $this->options->getAll();

// site host
$host = parse_url(site_url(), PHP_URL_HOST);
$host_www = (strpos($host, 'www.') === 0);

$last_invalidation = get_option('cf-page-cache-last-invalidation', array());
if (!empty($last_invalidation)) {
    $last_invalidation = implode(PHP_EOL, $last_invalidation);
}

?>
<form method="post" action="<?php echo add_query_arg(array( 'page' => 'cloudfront-page-cache', 'view' => 'invalidation', 'action' => 'cloudfront_invalidation' ), admin_url('admin-post.php')); ?>" class="clearfix" enctype="multipart/form-data">
	<?php wp_nonce_field('cloudfront-invalidation'); ?>
	<div class="wrap" style="padding-top:0px;margin-top:0px;">
		<div id="poststuff" style="padding-top:0px;margin-top:0px;">
			<div id="post-body" class="metabox-holder">
				<div id="post-body-content">

<p>Enter paths to invalidate. One path per line. To clear all cached pages and assets use the wildcard <code>/*</code>. (<a href="https://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/Invalidation.html?<?php print $this->aws_tracking; ?>" target="_blank" rel="noopener">documentation</a>)</p>
<textarea name="cfpc_invalidations" style="width:100%;max-width:600px;height:150px;" placeholder="<?php print esc_attr('/blog-post/
/blog-category/*
/wp-content/themes/your-theme/images/*'); ?>"><?php if ($last_invalidation) {
    print $last_invalidation;
} ?></textarea>
<p>
<?php
    submit_button(__('Send invalidation request'), 'primary large', 'is_submit', false);

 ?>
 </p>
 <h3 style="margin-top:1.5em;">CloudFront Cache Invalidation</h3>

				<p>To clear the cache on all international CloudFront edge servers requires sending an invalidation request. You can specify either the path for individual pages and assets, e.g. <code>/my-blog-post/</code> or a path that ends with the * wildcard, which might apply to one page or to many pages and assets, as shown in the following examples:</p>
<ol>
<li>/my-blog-post/</li>
<li>/my-category/*</li>
<li>/wp-content/themes/my-theme/css/*</li>
</ol>
<p>Cache invalidation is not entirely free. Amazon provides a monthly free budget of <?php print $this->AdminInvalidation->budget(); ?> invalidations. Additional path invalidations are charged at $<?php print number_format_i18n($this->AdminInvalidation->overusage_price(), 3); ?> USD per invalidation (<a href="https://aws.amazon.com/cloudfront/pricing/?<?php print $this->aws_tracking; ?>" target="_blank" rel="noopener">view pricing details</a>).</p>
<p class="notice inline"><strong>Note:</strong> This plugin never automatically invalidates the cache unless you configure automatic CloudFront cache purging from the WordPress post edit form. The budget counter is displayed near the publish button to keep the budget usage in check.</p>
<p>To prevent invalidation usage when assets are regularly updated, it is advised to use a <a href="https://encrypted.google.com/search?q=cache+busting+hash" target="_blank" rel="noopener">cache busting hash</a> strategy. This would enable unlimited free cache busting usage of CloudFront.</p>


 				</div>
 			</div>
 		</div>
 	</div>
 </form>
