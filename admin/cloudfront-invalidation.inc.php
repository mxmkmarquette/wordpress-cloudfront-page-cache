<?php
namespace O10n;

/**
 * CloudFront Page Cache Invalidation admin template
 *
 * @package    optimization
 * @subpackage optimization/admin
 * @author     Optimization.Team <info@optimization.team>
 */
if (!defined('ABSPATH') || !defined('O10N_ADMIN')) {
    exit;
}

// print form header
$this->form_start(__('CloudFront Page Cache Invalidation', 'o10n'), 'cloudfront');

// site host
$host = parse_url(site_url(), PHP_URL_HOST);
$host_www = (strpos($host, 'www.') === 0);

$last_invalidation = get_option('o10n_cloudfront_last_invalidation', array());
if (!empty($last_invalidation)) {
    $last_invalidation = implode(PHP_EOL, $last_invalidation);
}
?>

<p>Enter paths to invalidate. One path per line. To clear all cached pages and assets use the wildcard <code>/*</code>. (<a href="https://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/Invalidation.html?<?php print $view->aws_tracking; ?>" target="_blank" rel="noopener">documentation</a>)</p>
<textarea name="o10n[invalidations]" style="width:100%;max-width:600px;min-height:150px; resize: vertical;" placeholder="<?php print esc_attr('/blog-post/
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
<p>Cache invalidation is not entirely free. Amazon provides a monthly free budget of <?php print $view->AdminCloudfrontinvalidation->budget(); ?> invalidations. Additional path invalidations are charged at $<?php print number_format_i18n($view->AdminCloudfrontinvalidation->overusage_price(), 3); ?> USD per invalidation (<a href="https://aws.amazon.com/cloudfront/pricing/?<?php print $view->aws_tracking; ?>" target="_blank" rel="noopener">view pricing details</a>).</p>
<p>To prevent invalidation usage when assets are regularly updated, it is advised to use a <a href="https://encrypted.google.com/search?q=cache+busting+hash" target="_blank" rel="noopener">cache busting hash</a> strategy. This would enable unlimited free cache busting usage of CloudFront.</p>

<p class="info_yellow suboption"><strong><span class="dashicons dashicons-lightbulb"></span></strong> To prevent invalidation costs during testing it is possible to use a Windows, Mac or Linux hosts file. <a href="https://encrypted.google.com/search?q=configure+hosts+file+windows+mac" target="_blank" rel="noopener">Search Google</a> for instructions.</p>
<?php

// print form header
$this->form_end();
