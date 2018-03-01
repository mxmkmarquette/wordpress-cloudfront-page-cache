<?php
namespace O10n;

/**
 * CloudFront Page Cache Settings admin template
 *
 * @package    optimization
 * @subpackage optimization/admin
 * @author     Optimization.Team <info@optimization.team>
 */
if (!defined('ABSPATH') || !defined('O10N_ADMIN')) {
    exit;
}

// print form header
$this->form_start(__('CloudFront Page Cache Settings', 'o10n'), 'cloudfront');

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

<table class="form-table">
	<tr valign="top">
		<th scope="row">&nbsp;</th>
		<td>
			<label><input type="checkbox" name="o10n[cloudfront.enabled]" data-json-ns="1" value="1"<?php $checked('cloudfront.enabled'); ?> /> Enabled</label>
			<p class="description">Enable the CloudFront Page Cache.</p>
		</td>
	</tr>
	<tr valign="top" data-ns="cloudfront"<?php $visible('cloudfront'); ?>>
		<th scope="row">Public Host (CNAME)</th>
		<td>
			<input type="text" name="o10n[cloudfront.host]" data-json-ns="1" size="60" value="<?php $value('cloudfront.host'); ?>" placeholder="<?php print esc_attr($host_placeholder); ?>" />
			<p class="description">Enter the public host name (accessed by visitors) that is configured as CloudFront CNAME or Route 53 ALIAS.</p>
		</td>
	</tr>
	<tr valign="top" data-ns="cloudfront"<?php $visible('cloudfront'); ?>>
		<th scope="row">Origin Host</th>
		<td>
			<input type="text" name="o10n[cloudfront.origin]" data-json-ns="1" size="60" value="<?php $value('cloudfront.origin'); ?>" placeholder="<?php print esc_attr($origin_placeholder); ?>" />
			<p class="description">Enter the origin host name configured as CloudFront origin. By default, the origin differentiates from the public host by the subdomain www.</p>
		</td>
	</tr>
	<tr valign="top" data-ns="cloudfront"<?php $visible('cloudfront'); ?>>
		<th scope="row">CloudFront Domain</th>
		<td>
			<input type="text" name="o10n[cloudfront.domain]" data-json-ns="1" size="60" value="<?php $value('cloudfront.domain'); ?>" placeholder="your-distribution.cloudfront.net" />
			<p class="description">Enter the CloudFront Domain Name, e.g. <em>d1hyhu0m6pwrmw.cloudfront.net</em>. </p>
		</td>
	</tr>
	<tr valign="top" data-ns="cloudfront"<?php $visible('cloudfront'); ?>>
		<th scope="row">Max Cache Age</th>
		<td>
			<input type="number" name="o10n[cloudfront.max_age]" data-json-ns="1" size="20" value="<?php $value('cloudfront.max_age'); ?>" placeholder="Time in seconds..." />
			<p class="description">Enter the default CloudFront page cache age in seconds. The cache expire time is controlled by <a href="https://developers.google.com/web/fundamentals/performance/optimizing-content-efficiency/http-caching" target="_blank" rel="noopener">HTTP cache headers</a>. The default expire time is 7 days.</p>
			<p class="description">You can manually set the cache expire time for a page using PHP methods in functions.php (<a href="javascript:void(0);" onclick="jQuery('#example_http_cache').toggle();">show example</a>).</p>
			<pre id="example_http_cache" style="display:none;">
if (!is_admin()) {
    O10n\CloudFront\set_max_age(86400 * 7); // expire in 7 days

    // alternative
    O10n\CloudFront\set_expire(strtotime('<?php print date('Y-m-d H:i', (time() + (86400 * 7))); ?>')); // timestamp

    // alternative
    O10n\CloudFront\nocache(); // do not cache
}
			</pre>
		</td>
	</tr>
	<tr valign="top" data-ns="cloudfront"<?php $visible('cloudfront'); ?>>
		<th scope="row">Dynamic Content</th>
		<td>
			<p>See the <a href="https://github.com/o10n-x/wordpress-cloudfront-page-cache/tree/master/docs" target="_blank">Setup Guide</a> on Github for information on how to setup caching for dynamic content.</p>
		</td>
	</tr>
	<tr valign="top" data-ns="cloudfront"<?php $visible('cloudfront'); ?>>
		<th scope="row">Cache Invalidation</th>
		<td>
			<label><input type="checkbox" name="o10n[cloudfront.invalidation.enabled]" data-json-ns="1" value="1"<?php $checked('cloudfront.invalidation.enabled'); ?> /> Enabled</label>
			<p class="description">Enable <a href="https://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/Invalidation.html?<?php print $view->aws_tracking; ?>" target="_blank" rel="noopener">invalidation</a> of the page cache when updating WordPress posts, via the admin menu bar or using the invalidation form.</p>
		</td>
	</tr>
	<tr valign="top" class="autopurge" data-ns="cloudfront.invalidation"<?php $visible('cloudfront.invalidation'); ?>>
		<th scope="row">Distribution ID</th>
		<td>
			<input type="text" name="o10n[cloudfront.invalidation.distribution_id]" data-json-ns="1" size="40" value="<?php print $value('cloudfront.invalidation.distribution_id'); ?>" />
			<p class="description">Enter the CloudFront Distribution ID. You can find the ID on the console overview page and in the distribution settings.</p>
		</td>
	</tr>
	<tr valign="top" class="autopurge" data-ns="cloudfront.invalidation"<?php $visible('cloudfront.invalidation'); ?>>
		<th scope="row">Access Key ID</th>
		<td>
			<input type="text" name="o10n[cloudfront.invalidation.api_key]" data-json-ns="1" size="40" value="<?php $value('cloudfront.invalidation.api_key'); ?>" />
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
			<p class="description">Documentation: <a href="http://aws.amazon.com/developers/access-keys/?<?php print $view->aws_tracking; ?>" target="_blank" rel="noopener">Setup Access Key</a> | <a href="https://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/cf-api-permissions-ref.html?<?php print $view->aws_tracking; ?>#required-permissions-invalidations" target="_blank" rel="noopener">Setup Permissions</a>.</p>
		</td>
	</tr>
	<tr valign="top" class="autopurge" data-ns="cloudfront.invalidation"<?php $visible('cloudfront.invalidation'); ?>>
		<th scope="row">Secret Access Key</th>
		<td>
			<input type="password" name="o10n[cloudfront.invalidation.api_secret]" data-json-ns="1" size="40" value="<?php $value('cloudfront.invalidation.api_secret'); ?>" />
			<p class="description">Enter the CloudFront API secret.</p>
		</td>
	</tr>
	<tr valign="top" class="autopurge" data-ns="cloudfront.invalidation"<?php $visible('cloudfront.invalidation'); ?>>
		<th scope="row">AWS Region</th>
		<td>
			<input type="text" name="o10n[cloudfront.invalidation.aws_region]" data-json-ns="1" size="20" value="<?php $value('cloudfront.invalidation.aws_region'); ?>" placeholder="us-east-1" />
			<p class="description">Optionally, enter an <a href="https://docs.aws.amazon.com/AWSEC2/latest/UserGuide/using-regions-availability-zones.html?<?php print $view->aws_tracking; ?>#concepts-available-regions" target="_blank" rel="noopener">AWS region</a> for the API connection. The default is <code>us-east-1</code>.</p>
		</td>
	</tr>
	<tr valign="top" class="autopurge" data-ns="cloudfront.invalidation"<?php $visible('cloudfront.invalidation'); ?>>
		<th scope="row">Test API Connection</th>
		<td>
			<label><input type="checkbox" name="o10n[cloudfront.invalidation.api_test]" data-json-ns="1" value="1"<?php $checked('cloudfront.invalidation.aws_test'); ?> /> Enabled</label>
			<p class="description">Test the Amazon AWS API connection when saving settings.</p>
		</td>
	</tr>
	<tr valign="top" data-ns="cloudfront"<?php $visible('cloudfront'); ?>>
		<th scope="row" style="padding-top:0px;">&nbsp;</th>
		<td style="padding-top:0px;">
			<div class="info_white">
				<strong>Warning:</strong> CloudFront provides a free invalidation budget of 1000 invalidations per month. Additional cache invalidations are charged at $0.005 USD per path requested for invalidation. See the <a href="https://aws.amazon.com/cloudfront/pricing/?<?php print $view->aws_tracking; ?>" target="_blank" rel="noopener">pricing documentation</a> for more information. To clear all cached pages with a single path you can use the wildcard path <code>/*</code>.
			</div>
			<p class="info_yellow suboption"><strong><span class="dashicons dashicons-lightbulb"></span></strong> To prevent invalidation costs during testing it is possible to use a Windows, Mac or Linux hosts file. <a href="https://encrypted.google.com/search?q=configure+hosts+file+windows+mac" target="_blank" rel="noopener">Search Google</a> for instructions.</p>
		</td>
	</tr>
</table>

<hr />
<?php
    submit_button(__('Save'), 'primary large', 'is_submit', false);

// print form header
$this->form_end();
