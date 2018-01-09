<?php
namespace CloudFrontPageCache;

/**
 * Setup guide admin template
 *
 * @package    cloudfront-page-cache
 * @subpackage optimization/admin
 * @author     Optimization.Team <info@optimization.team>
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<style>
span.n {
	    font-weight: bold;
    background: #f91;
    color:black;
    border-radius: 1em;
    width: 20px;
    height: 20px;
    text-align: center;
    display: inline-block;
    line-height: 20px;
    font-size: 12px;
    margin-right:2px;
}
</style> 

<h1>CloudFront Setup</h1>

<p>The setup of CloudFront, despite the many options, is very simple. The most important settings for this plugin to work are the correct configuration of the origin host, the CNAME (public host), creating a SSL certificate (optional) and setting the <code>X-CF-PAGE-CACHE</code> header. The other settings are mostly for optimization purposes and we will provide some advise for achieving the best settings for your website.</p>
<p>To get started, login to <a href="https://console.aws.amazon.com/cloudfront/home" target="_blank" rel="noopener">AWS CloudFront Console</a>, click the button <a href="https://console.aws.amazon.com/cloudfront/home#create-distribution:" target="_blank" rel="noopener" class="button button-primary">Create Distribution</a> and choose the <u>Web</u> Distribution type.</p>
<p><span class="n">1</span> Enter the origin host name in the <strong>Origin Domain Name</strong> field. By default, this plugin differentiates between origin and public hosts by the presence of <code>www.</code> so if your public host is <em>www.your-domain.com</em> then you would enter <em>yourdomain.com</em> as your origin host. If you want to use your root domain then the www. version will be your origin host. You can customize this behaviour in the plugin settings.</p>
<p><span class="n">2</span> In the <strong>Origin Custom Headers</strong> field, add the header <code>X-CF-PAGE-CACHE</code> with the value <code>1</code>.</p>
<p><span class="n">3</span> In the <strong>Alternate Domain Names (CNAMEs)</strong> field, enter the public host (non-www. or www. version of the origin host).</p>
<p><span class="n">4</span> Set <strong>Cache Based on Selected Request Headers</strong> to <code>Whitelist</code> and add the HTTP headers <code>Host</code> and <code>Origin</code> to the list. This will prevent direct access to the CloudFront domain.</p> 
<p>The other settings are optional but we advise to install a SSL certificate, force SSL using <em>Viewer Policy: Redirect HTTP to HTTPS</em>, forward query strings using <em>Forward all, cache based on whitelist</em> (useful for debugging and cache busting) and <em>Compress Objects Automatically</em> (enabled). You should also look at the option <em>Origin Protocol Policy</em>. If your origin forces SSL then the setting should be <em>HTTPS only</em>.</p>

<p><strong>Done</strong></p>
<p>CloudFront will setup the distribution in a few minutes.</p>

<h1>Plugin Setup</h1>

<p>Enable the plugin by configuring the <a href="<?php print esc_url(add_query_arg(array( 'page' => 'cloudfront-page-cache', 'view' => 'settings' ), admin_url('options-general.php'))); ?>">settings</a>.</p>

<h1>DNS Setup</h1>
<p>To connect the CloudFront public cache frontend to your website, you will need to add a CNAME record in your DNS server for the public host that you configured as CNAME in the CloudFront distribution. The CNAME should point to the CloudFront Domain Name of your distribution. In our case it is <em>d1hyhu0m6pwrmw.cloudfront.net</em>.</p>
<fieldset><legend>CloudFront Console</legend>
<img src="<?php print plugins_url('admin/images/pagespeed-cloudfront-cname.png', 'cloudfront-page-cache/cloudefront-page-cache.php'); ?>" style="max-width:100%;">
</fieldset>
<p><strong>Note: root domains do not support CNAME (see below).</strong></p>
<p>If you are unfamiliar with configuring the DNS server you can send a request to your hosting provider or domain registrar to add a CNAME record for <code>www.your-domain.com</code> pointing to your CloudFront distribution Domain Name.</p>
 
<h3>Root domain as public domain (https://your-domain.com/)</h3>
<p>CloudFront supports the use of root domains but only when using an <code>ALIAS</code> DNS record in <a href="https://aws.amazon.com/route53/?<?php print $this->aws_tracking; ?>" target="_blank" rel="noopener">Amazon AWS Route 53</a> (cloud geo DNS), an international DNS service. Amazon provides an easy option to select the CloudFront distribution as the alias from the Route 53 settings for your domain. </p>
<p><img src="<?php print plugins_url('admin/images/route-53-alias.png', 'cloudfront-page-cache/cloudefront-page-cache.php'); ?>"></p>
<p>If you are currently not using Route 53 and would like to use a root domain as the public host name then it is required to move your existing DNS to Route 53. To move the DNS, you can simply copy your existing DNS entries and enter the Route 53 nameservers at your domain registrar.</p>

<h1>How to test if it is working?</h1>
<p>CloudFront adds HTTP headers with the cache status. To test if the cache is working you can open the browser console (F12 in most browsers) and open the Network tab.</p>
<p><img src="<?php print plugins_url('admin/images/cf-http-headers-chrome.png', 'cloudfront-page-cache/cloudefront-page-cache.php'); ?>?x"></p>

<h1>Webserver Setup</h1>

<p>If you use a <code>.htaccess</code>, Apache or Nginx based www. redirect then it will be required to make a change in the server configuration to allow the CloudFront origin pull request to access the website on the www. or non-www. subdomain respectively.</p>

<h3>Apache (.htaccess) Example</h3>
<pre>
RewriteEngine On
...

<span style="color:#E48700;font-weight:bold;"># CloudFront origin pull detection</span>
<span style="color:#1166BB;font-weight:bold;">RewriteCond %{HTTP:X-CF-PAGE-CACHE} !=1 [NC]
RewriteCond %{REQUEST_URI} !^/wp-(admin|login) # enabe /wp-admin/ access on origin</span>
...
RewriteCond %{HTTP_HOST} !^www\.
RewriteRule ^(.*)$ https://www.%{HTTP_HOST}/$1 [R=301,L]
</pre>
<br />
<h3>Nginx Example</h3>
<pre>
server {
    listen 443 ssl http2;
    server_name your-domain.com;
    ... 

    <span style="color:#E48700;font-weight:bold;"># CloudFront origin pull detection</span>
    <span style="color:#1166BB;font-weight:bold;">set $cloudfront_origin "";
    if ($http_x_cf_page_cache = "1") { 
        set $cloudfront_origin "y";
    }</span>

    <span style="color:#E48700;font-weight:bold;"># conditional non-www. to www. redirect</span>
    <span style="color:#1166BB;font-weight:bold;">if ($http_host = "your-domain.com") {
        set $cloudfront_origin "${cloudfront_origin}x";
    }</span>

    <span style="color:#E48700;font-weight:bold;"># enable /wp-admin on origin</span>
    <span style="color:#1166BB;font-weight:bold;">if ($request_uri ~ "/wp-admin/"){
        set $cloudfront_origin "${cloudfront_origin}a";
    }
    if ($request_uri ~ "/wp-login.php"){
        set $cloudfront_origin "${cloudfront_origin}a";
    }</span>

    <span style="color:#E48700;font-weight:bold;"># x = generic non-www. request</span>
    <span style="color:#1166BB;font-weight:bold;">if ($cloudfront_origin = "x") {
        return 301 https://www.your-domain.com$request_uri;
    }</span>

    ...
    location ~ /\. { deny all; }
}
</pre>
<p>To redirect www. to non-www., simply change the <code>$http_host</code> condition to match the www. domain and redirect to non-www.</p>
    
<h1>Handling POST requests</h1>
<p>CloudFront does not support HTTP POST requests. If you want to use a submission form that needs to post data to the server then it is required to use a script on the origin host that is not redirected to the public (www. or non-www.) host.</p>
<p>This plugin provides support for processing POST requests on the origin host by automatically rewriting <code>admin-ajax.php</code>. You can control the URLs that are rewritten to the origin host using the filter <code>cfpc-origin-hosts-filter</code>.</p>
<pre>
/** rewrite paths to origin host */
function cloudfront_page_cache_origin_hosts_rewrite($paths) {
    
    # add ajax script to handle POST requests
    $paths[] = '/my-ajax-script.php';

    return $paths;
}
add_filter('cfpc-origin-hosts-filter', 'cloudfront_page_cache_origin_hosts_rewrite');
</pre>
<p>This plugin does not rewrite URLs in the HTML. It only modifies the result of native WordPress URL filters such as <code>home_url</code> and <code>admin_url</code>.</p>