=== CloudFront Page Cache ===
Contributors: optimalisatie
Donate link: https://pagespeed.pro/
Tags: cache, cloudfront, aws, amazon, page cache, site cache, cloud, seo, international, performance, speed, page speed, pagespeed, fpc, full page cache, html cache
Requires at least: 3.0.1
Tested up to: 4.9
Requires PHP: 5.4
Stable tag: 4.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Low cost and high performance page cache based on Amazon's CloudFront CDN for international SEO. CloudFront provides international fast website speed and dedicated geographic IP's for local SEO advantage.

== Description ==

This plugin provides a low cost and high performance international page cache solution based on [Amazon AWS CloudFront CDN](https://aws.amazon.com/cloudfront/).

This is the first version of the plugin but the underlying technologies have been tested for over 5 years and some of our clients have achieved long term top 10 positions in Google in over 20 countries using a single server in Amsterdam.

A big advantage of using CloudFront as a page cache for international SEO is that Amazon provides dedicated IP's for geographic regions. This means that a website will physically load from a location near the visitor. For a visitor from Sweden the website may be physically loaded from a server and IP in Stockholm.

An other advantage of Amazon AWS CloudFront as a page cache is that they provide the lowest costs. For an average business website, the total costs will litterally be less than 1 dollar per month. Amazon provides free SSL certificates and there are no hidden costs.

Amazon AWS CloudFront is among the [fastest](https://encrypted.google.com/search?q=cloudfront+vs) CDN providers available with the greatest global network. This makes it a perfect option for any website that wants to reach an international audience or that simply wants a fast and secure page cache for a low cost VPS.

## Solution for emerging markets

Internet connectivity, speed and reliability are a major issue in some regions of the world affecting hundreds of millions of people. Regions such as Asia, India and Indonesia may also have many innovators and small business startups who produce or sell products that could be very attractive to other regions of the world, but they may lack financial resources to reach customers beyond their local market.

The CloudFront page cache solution makes it possible to solve slow and unreliable internet issues for just $0.05 USD in total costs per month for a small blog. This plugin enables to use a 5 USD VPS for a heavy WordPress + WooCommerce installation while being capable of handling thousands of visitors per day (with a fast page speed and good results in Google) for just $0.50+ USD per month in AWS costs. The solution also enables a website to grow from 100 visitors per day to 100.000 visitors per day without a problem (besides costs). For a small business website, the total costs will be about $0.05 to $0.10 USD per month while international website speed + Google rankings are of high value.

### Demo website

An example is our demo website [www.e-scooter.co](https://www.e-scooter.co/) which is hosted on a cheap VPS in Switzerland. The website was created in July 2017 and it already has #1 positions in Google for premium search terms in the U.S., India and other regions. The total CloudFront bill for December 2017, including www.pagespeed.pro, www.fastestwebsite.co and some other websites was $0.74 USD.

We are interested to learn about your experiences and feedback when using this plugin. Please submit your feedback to [info@pagespeed.pro](mailto:info@pagespeed.pro).

== Installation ==

### WordPress plugin installation

1. Upload the `cloudfront-page-cache/` directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to the plugin setup page or follow the below instructions.

### CloudFront Page Cache installation

The setup of CloudFront, despite the many options, is very simple. The most important settings for this plugin to work are the correct configuration of the origin host, the CNAME (public host), creating a SSL certificate (optional) and setting the `X-CF-PAGE-CACHE` header. The other settings are mostly for optimization purposes and we will provide some advise for achieving the best settings for your website.

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

= 1.0.2 =
* Added functionality to set CloudFront cache age or expire date (HTTP headers).
* Added default CloudFront cache age setting.

= 1.0 =
* The first version.