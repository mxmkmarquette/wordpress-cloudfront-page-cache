=== HTTP/2 Optimization ===
Contributors: o10n
Donate link: https://github.com/o10n-x/
Tags: http2, spdy, server push, push, service worker, cache digest, pwa
Requires at least: 4.0
Requires PHP: 5.4
Tested up to: 4.9.4
Stable tag: 0.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Advanced HTTP/2 optimization toolkit. HTTP/2 Server Push, Service Worker based Cache-Digest and more.

== Description ==

This plugin provides a low cost and high performance international page cache solution based on [Amazon AWS CloudFront CDN](https://aws.amazon.com/cloudfront/).

An advantage of Amazon AWS CloudFront as a page cache is that they provide the lowest costs. For an average business website, the total costs will be literally less than 1 dollar per month. Amazon provides free SSL certificates and there are no hidden costs.

An other advantage is that CloudFront supports root domains when using [Amazon AWS Route53 DNS service](https://aws.amazon.com/route53/), making it possible to use CloudFront's CDN for https://yourdomain.com/

Amazon AWS CloudFront is among the [fastest](https://encrypted.google.com/search?q=cloudfront+vs) CDN providers available with the greatest global network. This makes it a perfect option for any website that wants to reach an international audience or that simply wants a fast and secure page cache for a low cost VPS.

We are interested to learn about your experiences and feedback when using this plugin. Please submit your feedback on the [Github community forum](https://github.com/o10n-x/wordpress-cloudfront-page-cache/issues).

== Installation ==

### WordPress plugin installation

1. Upload the `cf-page-cache/` directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to the plugin setup page or follow the below instructions.

### CloudFront Page Cache installation

The setup of CloudFront, despite the many options, is very simple. The most important settings for this plugin to work are the correct configuration of the origin host, the CNAME (public host), creating a SSL certificate (optional) and setting the `X-CF-PAGE-CACHE` header. The other settings are mostly for optimization purposes and we will provide some advise for achieving the best settings for your website.

**For professional installation support, you can [submit your question](https://forums.aws.amazon.com/forum.jspa?forumID=46) on the CloudFront Support Forum.**

To get started, login to [AWS CloudFront Console](https://console.aws.amazon.com/cloudfront/home), click the button **Create Distribution** and choose the *Web* Distribution type.

1. Enter the origin host name in the **Origin Domain Name** field. By default, this plugin differentiates between origin and public hosts by the presence of `www.` so if your public host is *www.your-domain.com* then you would enter *yourdomain.com* as your origin host. If you want to use your root domain then the www. version will be your origin host. You can customize this behaviour in the plugin settings.</p>
2. In the **Origin Custom Headers** field, add the header `X-CF-PAGE-CACHE` with the value `1`.
3. In the **Alternate Domain Names (CNAMEs)** field, enter the public host (non-www. or www. version of the origin host).
4. Set **Cache Based on Selected Request Headers** to `Whitelist` and add the HTTP headers `Host` and `Origin` to the list. This will prevent direct access to the CloudFront domain.

The other settings are optional but we advise to install a SSL certificate, force SSL using *Viewer Policy: Redirect HTTP to HTTPS*, forward query strings using *Forward all, cache based on whitelist* (useful for debugging and cache busting) and *Compress Objects Automatically* (enabled). You should also look at the option *Origin Protocol Policy*. If your origin forces SSL then the setting should be *HTTPS only*.

**Done**

CloudFront will setup the distribution in a few minutes.

== Screenshots ==

1. CloudFront Page Cache
2. CloudFront Page Cache Settings
3. CloudFront Invalidation Form
4. International CloudFront Performance
5. CloudFront Network (2017)

== Changelog ==

= 1.0.3 =
Conversion of plugin to Page Cache Module for Performance Optimization Collection. See [https://github.com/o10n-x/](https://github.com/o10n-x/)

= 1.0.2 =
* Added functionality to set CloudFront cache age or expire date (HTTP headers).
* Added default CloudFront cache age setting.

= 1.0 =
* The first version.


== Changelog ==

= 0.0.3 =

Core update (see changelog.txt)

= 0.0.1 =

Beta release. Please provide feedback on [Github forum](https://github.com/o10n-x/wordpress-http2-optimization/issues).

== Upgrade Notice ==

None.