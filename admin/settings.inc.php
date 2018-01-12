<?php
namespace CloudFrontPageCache;

/**
 * Settings admin template
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

$host_placeholder = $host;
$origin_placeholder = $host;
if (!$host_www) {
    $host_placeholder = str_replace('www.', '', $host);
    if (strpos($host, 'www.') !== 0) {
        $origin_placeholder = 'www.' . $host;
    }
} elseif (strpos($host, 'www.') !== 0) {
    $host_placeholder = 'www.' . $host;
} else {
    $origin_placeholder = str_replace('www.', '', $host);
}


?>
<form method="post" action="<?php echo add_query_arg(array( 'page' => 'cloudfront-page-cache', 'view' => 'settings', 'action' => 'cloudfront_page_cache' ), admin_url('admin-post.php')); ?>" class="clearfix" enctype="multipart/form-data">
	<?php wp_nonce_field('cloudfront-page-cache'); ?>
	<div class="wrap" style="padding-top:0px;margin-top:0px;">
		<div id="poststuff" style="padding-top:0px;margin-top:0px;">
			<div id="post-body" class="metabox-holder">
				<div id="post-body-content">

<table class="form-table">
	<tr valign="top">
		<th scope="row">&nbsp;</th>
		<td>
			<label><input type="checkbox" name="cfpc[enabled]" value="1"<?php if (isset($options['enabled']) && $options['enabled']) {
    print ' checked';
} ?> /> Enabled</label>
			<p class="description">Enable the CloudFront Page Cache.</p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">Public Host (CNAME)</th>
		<td>
			<input type="text" name="cfpc[host]" size="60" value="<?php print esc_attr((isset($options['host'])) ? esc_attr($options['host']) : ''); ?>" placeholder="<?php print esc_attr($host_placeholder); ?>" />
			<p class="description">Enter the public host name (accessed by visitors) that is configured as CloudFront CNAME or Route 53 ALIAS.</p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">Origin Host</th>
		<td>
			<input type="text" name="cfpc[origin]" size="60" value="<?php print esc_attr((isset($options['origin'])) ? esc_attr($options['origin']) : ''); ?>" placeholder="<?php print esc_attr($origin_placeholder); ?>" />
			<p class="description">Enter the origin host name configured as CloudFront origin. By default, the origin differentiates from the public host by the subdomain www.</p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">CloudFront Domain</th>
		<td>
			<input type="text" name="cfpc[domain]" size="60" value="<?php print esc_attr((isset($options['domain'])) ? $options['domain'] : ''); ?>" placeholder="your-distribution.cloudfront.net" />
			<p class="description">Enter the CloudFront Domain Name, e.g. <em>d1hyhu0m6pwrmw.cloudfront.net</em>. </p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">Default Cache Age</th>
		<td>
			<input type="number" name="cfpc[cache_age]" size="20" value="<?php print esc_attr((isset($options['cache_age'])) ? $options['cache_age'] : ''); ?>" placeholder="Time in seconds..." />
			<p class="description">Enter the default CloudFront page cache age in seconds. The cache expire time is controlled by <a href="https://developers.google.com/web/fundamentals/performance/optimizing-content-efficiency/http-caching" target="_blank" rel="noopener">HTTP cache headers</a>. The default expire time is 7 days.</p>
			<p class="description">You can manually set the cache expire time for a page using the methods <code>CloudFrontPageCache\set_age( .. )</code> and <code>CloudFrontPageCache\set_expire( .. )</code> in your theme's functions.php (<a href="javascript:void(0);" onclick="jQuery('#example_http_cache').toggle();">show example</a>).</p>
			<pre id="example_http_cache" style="display:none;">
if (!is_admin()) {
    CloudFrontPageCache\set_age(86400 * 7);
}
			</pre>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">Dynamic Content</th>
		<td>
			<p>See the <a href="<?php print esc_url(add_query_arg(array( 'page' => 'cloudfront-page-cache', 'view' => 'setup' ), admin_url('options-general.php'))); ?>#dynamic-content">Setup Guide</a> for information on how to setup caching for dynamic content, a unique feature of CloudFront.</p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">Cache Invalidation</th>
		<td>
			<label><input type="checkbox" name="cfpc[invalidation]" value="1" onchange="if (jQuery(this).is(':checked')) { jQuery('.autopurge').show(); } else { jQuery('.autopurge').hide(); } "<?php if (isset($options['invalidation']) && $options['invalidation']) {
    print ' checked';
} ?> /> Enabled</label>
			<p class="description">Enable <a href="https://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/Invalidation.html?<?php print $this->aws_tracking; ?>" target="_blank" rel="noopener">invalidation</a> of the page cache when updating WordPress posts, via the admin menu bar or using the invalidation form.</p>

			<div class="notice inline" style="margin:0px;margin-top:1em;padding-top:7px;padding-bottom:5px;">
			<strong>Warning:</strong> CloudFront provides a free invalidation budget of 1000 invalidations per month. Additional cache invalidations are charged at $0.005 USD per path requested for invalidation. See the <a href="https://aws.amazon.com/cloudfront/pricing/?<?php print $this->aws_tracking; ?>" target="_blank" rel="noopener">pricing documentation</a> for more information. To clear all cached pages with a single path you can use the wildcard path <code>/*</code>.
			<p>This plugin provides a counter functionality to keep track of invalidation usage and cache invalidation can be enabled or disabled manually when updating a post.</p>
			<p style="margin-bottom:0px;"><strong>Tip:</strong> to prevent invalidation costs during testing it is possible to use a Windows, Mac or Linux hosts file to directly access the public host from the server IP, bypassing CloudFront. <a href="https://encrypted.google.com/search?q=configure+hosts+file+windows+mac" target="_blank" rel="noopener">Search Google</a> for instructions.</p></div>
		</td>
	</tr>
	<tr valign="top" class="autopurge" style="<?php print (isset($options['invalidation']) && $options['invalidation']) ? '' : 'display:none;'; ?>">
		<th scope="row">Distribution ID</th>
		<td>
			<input type="text" name="cfpc[distribution_id]" size="40" value="<?php print esc_attr((isset($options['distribution_id'])) ? $options['distribution_id'] : ''); ?>" />
			<p class="description">Enter the CloudFront Distribution ID. You can find the ID on the console overview page and in the distribution settings.</p>
		</td>
	</tr>
	<tr valign="top" class="autopurge" style="<?php print (isset($options['invalidation']) && $options['invalidation']) ? '' : 'display:none;'; ?>">
		<th scope="row">Access Key ID</th>
		<td>
			<input type="text" name="cfpc[api_key]" size="40" value="<?php print esc_attr((isset($options['api_key'])) ? $options['api_key'] : ''); ?>" />
			<p class="description">Enter an AWS IAM Access Key ID with Cache Invalidation permission for your CloudFront distribution. You can create an access key in the <a href="https://console.aws.amazon.com/iam/" target="_blank" rel="noopener">IAM Console</a>. When you create a new user, you will need to configure a permission policy. You may select <code>CloudFrontFullAccess</code> for easy configuration and testing, however, it is advised to setup a restricted policy (<a href="javascript:void(0);" onclick="jQuery('#cf_permission_example').fadeToggle(200);">show example</a>).</p>
			<fieldset id="cf_permission_example" style="display:none;">
			<pre>
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Sid": "VisualEditor0",
            "Effect": "Allow",
            "Action": [
                "cloudfront:ListInvalidations",
                "cloudfront:GetInvalidation",
                "cloudfront:CreateInvalidation"
            ],
            "Resource": "*"
        }
    ]
}
			</pre>
			</fieldset>
			<p class="description">Documentation: <a href="http://aws.amazon.com/developers/access-keys/?<?php print $this->aws_tracking; ?>" target="_blank" rel="noopener">Setup Access Key</a> | <a href="https://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/cf-api-permissions-ref.html?<?php print $this->aws_tracking; ?>#required-permissions-invalidations" target="_blank" rel="noopener">Setup Permissions</a>.</p>
		</td>
	</tr>
	<tr valign="top" class="autopurge" style="<?php print (isset($options['invalidation']) && $options['invalidation']) ? '' : 'display:none;'; ?>">
		<th scope="row">Secret Access Key</th>
		<td>
			<input type="password" name="cfpc[api_secret]" size="40" value="<?php print esc_attr((isset($options['api_secret'])) ? $options['api_secret'] : ''); ?>" />
			<p class="description">Enter the CloudFront API secret.</p>
		</td>
	</tr>
	<tr valign="top" class="autopurge" style="<?php print (isset($options['invalidation']) && $options['invalidation']) ? '' : 'display:none;'; ?>">
		<th scope="row">AWS Region</th>
		<td>
			<input type="text" name="cfpc[aws_region]" size="20" value="<?php print esc_attr((isset($options['aws_region'])) ? $options['aws_region'] : ''); ?>" placeholder="us-east-1" />
			<p class="description">Optionally, enter an <a href="https://docs.aws.amazon.com/AWSEC2/latest/UserGuide/using-regions-availability-zones.html?<?php print $this->aws_tracking; ?>#concepts-available-regions" target="_blank" rel="noopener">AWS region</a> for the API connection. The default is <code>us-east-1</code>.</p>
		</td>
	</tr>
	<tr valign="top" class="autopurge" style="<?php print (isset($options['invalidation']) && $options['invalidation']) ? '' : 'display:none;'; ?>">
		<th scope="row">Test API Connection</th>
		<td>
			<label><input type="checkbox" name="cfpc[api_test]" value="1"<?php if (!isset($options['api_test']) || intval($options['api_test']) === 1) {
    print ' checked';
} ?> /> Enabled</label>
			<p class="description">Test the Amazon AWS API connection when saving settings.</p>
		</td>
	</tr>
	<tr valign="top" class="autopurge" style="<?php print (isset($options['invalidation']) && $options['invalidation']) ? '' : 'display:none;'; ?>">
		<th scope="row">Admin Menu Bar</th>
		<td>
			<label><input type="checkbox" name="cfpc[adminbar]" value="1"<?php if (!isset($options['adminbar']) || intval($options['adminbar']) === 1) {
    print ' checked';
} ?> /> Enabled</label>
			<p class="description">Add an admin menu bar entry for logged in administrators with the option to invalidate the page cache. The plugin offers an option to automatically purge all page related caches from plugins such as Autoptimize, WP Total Cache, WP Super Cache, WP Rocket, Varnish and many more. This will make sure that anything cached by CloudFront is fresh.</p>
		</td>
	</tr>
</table>
<hr />
<?php
    submit_button(__('Save'), 'primary large', 'is_submit', false);

 ?>

 				</div>
 			</div>
 		</div>
 	</div>
 </form>
