# CloudFront Page Cache Documentation

### CloudFront Setup

The setup of CloudFront, despite the many options, is very simple. The most important settings for this plugin to work are the correct configuration of the origin host, the CNAME (public host), creating a SSL certificate (optional) and setting the `X-CF-PAGE-CACHE` header. The other settings are mostly for optimization purposes and we will provide some advise for achieving the best settings for your website.

To get started, login to [AWS CloudFront Console](https://console.aws.amazon.com/cloudfront/home), click the button [Create Distribution](https://console.aws.amazon.com/cloudfront/home#create-distribution:) and choose the *Web* Distribution type.

1. Enter the origin host name in the **Origin Domain Name** field. By default, this plugin differentiates between origin and public hosts by the presence of `www.` so if your public host is *www.your-domain.com* then you would enter *yourdomain.com* as your origin host. If you want to use your root domain then the www. version will be your origin host. You can customize this behaviour in the plugin settings.

2. In the **Origin Custom Headers** field, add the header `X-CF-PAGE-CACHE` with the value `1`.

3. In the **Alternate Domain Names (CNAMEs)** field, enter the public host (non-www. or www. version of the origin host).

4. Set **Cache Based on Selected Request Headers** to `Whitelist` and add the HTTP headers `Host` and `Origin` to the list. This will prevent direct access to the CloudFront domain.
 
The other settings are optional but we advise to install a SSL certificate, force SSL using *Viewer Policy: Redirect HTTP to HTTPS*, forward query strings using *Forward all, cache based on whitelist* (useful for debugging and cache busting) and *Compress Objects Automatically* (enabled). You should also look at the option *Origin Protocol Policy*. If your origin forces SSL then the setting should be *HTTPS only*.

**Done**

CloudFront will setup the distribution in a few minutes.

### Plugin Setup

Enable the plugin by configuring the plugin settings.

### DNS Setup

To connect the CloudFront public cache frontend to your website, you will need to add a CNAME record in your DNS server for the public host that you configured as CNAME in the CloudFront distribution. The CNAME should point to the CloudFront Domain Name of your distribution. In our case it is *d1hyhu0m6pwrmw.cloudfront.net*.

![docs](https://github.com/o10n-x/wordpress-cloudfront-page-cache/blob/master/docs/images/pagespeed-cloudfront-cname.png)

**Note: root domains do not support CNAME (see below).**

If you are unfamiliar with configuring the DNS server you can send a request to your hosting provider or domain registrar to add a CNAME record for `www.your-domain.com` pointing to your CloudFront distribution Domain Name.
 
#### Root domain as public domain (https://your-domain.com/)

CloudFront supports the use of a root domain but only when using an `ALIAS` DNS record in <a href="https://aws.amazon.com/route53/?<?php print $this->aws_tracking; ?>" target="_blank" rel="noopener">Amazon AWS Route 53</a> (cloud geo DNS), an international DNS service. Amazon provides an easy option to select the CloudFront distribution as the alias from the Route 53 settings for your domain. 

![Route 53 config for CloudFront](https://github.com/o10n-x/wordpress-cloudfront-page-cache/blob/master/docs/images/route-53-alias.png)

If you are currently not using Route 53 and would like to use a root domain as the public host name then it is required to move your existing DNS to Route 53. To move the DNS, you can simply copy your existing DNS entries and enter the Route 53 nameservers at your domain registrar.


## How to test if it is working?
CloudFront adds HTTP headers with the cache status. To test if the cache is working you can open the browser console (F12 in most browsers) and open the Network tab.

![CloudFront HTTP cache headers](https://github.com/o10n-x/wordpress-cloudfront-page-cache/blob/master/docs/images/cf-http-headers-chrome.png)

## Webserver Setup

If you use a `.htaccess`, Apache or Nginx based www. redirect then it will be required to make a change in the server configuration to allow the CloudFront origin pull request to access the website on the www. or non-www. subdomain respectively.

### Apache (.htaccess) Example

```apache
RewriteEngine On
...

<span style="color:#E48700;font-weight:bold;"># CloudFront origin pull detection</span>
<span style="color:#1166BB;font-weight:bold;">RewriteCond %{HTTP:X-CF-PAGE-CACHE} !=1 [NC]
RewriteCond %{REQUEST_URI} !^/wp-(admin|login) # enable /wp-admin/ access on origin</span>
...
RewriteCond %{HTTP_HOST} !^www\.
RewriteRule ^(.*)$ https://www.%{HTTP_HOST}/$1 [R=301,L]
```

### Nginx Example

```nginx
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
```

To redirect www. to non-www., simply change the `$http_host` condition to match the www. domain and redirect to non-www.
    
### Handling POST requests

CloudFront does not support HTTP POST requests. If you want to use a submission form that needs to post data to the server then it is required to use a script on the origin host that is not redirected to the public (www. or non-www.) host.

This plugin provides support for processing POST requests on the origin host by automatically rewriting `admin-ajax.php`. You can control the URLs that are rewritten to the origin host using the filter `cfpc-origin-hosts-filter`.

```php
/** rewrite paths to origin host */
function cloudfront_page_cache_origin_hosts_rewrite($paths) {
    
    # add ajax script to handle POST requests
    $paths[] = '/my-ajax-script.php';

    return $paths;
}
add_filter('cfpc-origin-hosts-filter', 'cloudfront_page_cache_origin_hosts_rewrite');
```

This plugin does not rewrite URLs in the HTML. It only modifies the result of native WordPress URL filters such as `home_url` and `admin_url`.

### Caching dynamic content

CloudFront enables to cache dynamic content based on HTTP headers and cookies in the advanced settings of the CloudFront Console. For help setting up dynamic content caching, post your question to the [AWS CloudFront support forum](https://forums.aws.amazon.com/forum.jspa?forumID=46).

#### CloudFront Cache Settings
![docs](https://github.com/o10n-x/wordpress-cloudfront-page-cache/blob/master/docs/images/cookie-cache.png)
