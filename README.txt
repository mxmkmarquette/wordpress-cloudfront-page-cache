=== CloudFront Page Cache CDN ===
Contributors: o10n
Donate link: https://github.com/o10n-x/
Tags: cache, cloudfront, aws, amazon, page cache, site cache, cloud, seo, international, performance, speed, page speed, pagespeed, fpc, full 
Requires at least: 4.0
Requires PHP: 5.5
Tested up to: 4.9.4
Stable tag: 1.0.34
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Low cost and high performance page cache based on Amazon's CloudFront CDN. CloudFront provides international fast website speed and dedicated geographic IP's.

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

Documentation is available on Github:

https://github.com/o10n-x/wordpress-cloudfront-page-cache/tree/master/docs

== Screenshots ==

1. CloudFront Page Cache
2. CloudFront Page Cache Settings
3. CloudFront Invalidation Form
4. International CloudFront Performance
5. CloudFront Network (2017)

== Changelog ==

= 1.0.34 =
* Added: plugin update protection (plugin index).

= 1.0.33 =
* Core update (see changelog.txt)

= 1.0.25 =
* Added: JSON profile editor for all optimization modules.

= 1.0.24 =
Core update (see changelog.txt)

= 1.0.12 =
* Bugfix: uninstaller.

= 1.0.11 =
Core update (see changelog.txt)

= 1.0.10 =
* Bugfix: settings link on plugin index.

= 1.0.9 =
Core update (see changelog.txt)

= 1.0.8 =
* AWS SDK requires PHP5.5+. Minimum PHP version increased to 5.5.

= 1.0.7 =
* Added rspec unit tests and AWS API test.

= 1.0.6 =
Core update (see changelog.txt)

= 1.0.4 =
* Improved admin menu.
* Added PHPUnit test.
* Added Travis CI build test.

= 1.0.3 =
* Conversion of plugin to Page Cache Module. See [https://github.com/o10n-x/](https://github.com/o10n-x/) for optimization plugins.

= 1.0.2 =
* Added functionality to set CloudFront cache age or expire date (HTTP headers).
* Added default CloudFront cache age setting.

= 1.0 =
* The first version.

Please provide feedback on the [Github forum](https://github.com/o10n-x/wordpress-cloudfront-page-cache/issues).

== Upgrade Notice ==

None.