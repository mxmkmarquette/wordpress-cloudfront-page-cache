[![Build Status](https://travis-ci.org/o10n-x/wordpress-cloudfront-page-cache.svg?branch=master)](https://travis-ci.org/o10n-x/wordpress-cloudfront-page-cache)

<p align="right"><img src="https://github.com/o10n-x/wordpress-cloudfront-page-cache/blob/master/docs/images/amazon-cloudfront.png" height="125"> <img src="https://github.com/o10n-x/wordpress-cloudfront-page-cache/blob/master/docs/images/aws-cloudfront-100.png"></p> 

## CloudFront Page Cache

This plugin provides a low cost and high performance international page cache solution based on [Amazon AWS CloudFront CDN](https://aws.amazon.com/cloudfront/).

* [Documentation](https://github.com/o10n-x/wordpress-cloudfront-page-cache/tree/master/docs)
* [Description](https://github.com/o10n-x/wordpress-cloudfront-page-cache#description)
* [Version history (Changelog)](https://github.com/o10n-x/wordpress-cloudfront-page-cache/releases)

**This plugin is removed from WordPress.org. Read the story [here](https://github.com/o10n-x/wordpress-css-optimization/issues/4).**

## Installation

![Github Updater](https://github.com/afragen/github-updater/raw/develop/assets/GitHub_Updater_logo_small.png)

This plugin can be installed and updated using [Github Updater](https://github.com/afragen/github-updater) ([installation instructions](https://github.com/afragen/github-updater/wiki/Installation))

## WordPress WPO Collection

This plugin is part of a Website Performance Optimization collection that include [CSS](https://github.com/o10n-x/wordpress-css-optimization), [Javascript](https://github.com/o10n-x/wordpress-javascript-optimization), [HTML](https://github.com/o10n-x/wordpress-html-optimization), [Web Font](https://github.com/o10n-x/wordpress-font-optimization), [HTTP/2](https://github.com/o10n-x/wordpress-http2-optimization), [Progressive Web App (Service Worker)](https://github.com/o10n-x/wordpress-pwa-optimization) and [Security Header](https://github.com/o10n-x/wordpress-security-header-optimization) optimization. 

The WPO optimization plugins provide in all essential tools that enable to achieve perfect [Google Lighthouse Test](https://developers.google.com/web/tools/lighthouse/) scores and to validate a website as [Google PWA](https://developers.google.com/web/progressive-web-apps/), an important ranking factor for Google's [Speed Update](https://searchengineland.com/google-speed-update-page-speed-will-become-ranking-factor-mobile-search-289904) (July 2018).

![Google Lighthouse Perfect Performance Scores](https://github.com/o10n-x/wordpress-css-optimization/blob/master/docs/images/google-lighthouse-pwa-validation.jpg)

The WPO optimization plugins are designed to work together with single plugin performance. The plugins provide the latest optimization technologies and many unique innovations.

### JSON shema configuration

The WPO optimization plugins are based on JSON schema based configuration that enables full control of the optimization using JSON. This provides several great advantages for website performance optimization.

Read more about [JSON schemas](https://github.com/o10n-x/wordpress-o10n-core/tree/master/schemas).

#### Advantage 1: platform independent

The WPO plugins are not like most other WordPress plugins. The plugins are purely focused on optimization technologies instead of controlling / modifying WordPress. This makes the underlaying optimization technologies platform independent. The same technologies and configuration can be used on Magento, a Microsoft .NET CMS or a Node.js based CMS. 

#### Advantage 2: saving time

The JSON configuration enables much quicker tuning for experts and it enables to quickly copy and paste a proven configuration to a new website.

#### Advantage 3: public optimization knowledge and help

The JSON configuration can be easily shared and discussed on forums, enabling to build public knowledge about the options. Because the optimization configuration is independent from WordPress, the knowledge will be valid for any platform which increases the value, making it more likely to be able to receive free help.

#### Advantage 4: a basis for Artificial Intelligence

The JSON configuration concept, when completed, enables fine grained tuning of the optimization, not just on a per page level but even per individual visitor or based on the environment. This will enable to optimize the performance based on the [save-data](https://developers.google.com/web/updates/2016/02/save-data) header or for example to change an individual CSS optimization setting specifically for iPhone 5 devices. 

While the JSON shema concept makes it more easy for human editors to perform such complex environment based optimization, it also provides a basis for a future AI to take full control over the optimization, enabling to achieve the absolute best website performance result for each individual user automatically.

While the AI may one day supplement or take over, experts will have a clear view of what the AI is doing (it produces simple JSON that is used by humans) and will be able to override at any point.

## Google PageSpeed vs Google Lighthouse Scores

While a Google PageSpeed 100 score is still of value, websites with a high Google PageSpeed score may score very bad in Google's new [Lighthouse performance test](https://developers.google.com/web/tools/lighthouse/). 

The following scores are for the same site. It shows that a perfect Google PageSpeed score does not correlate to a high Google Lighthouse performance score.

![Perfect Google PageSpeed 100 Score](https://github.com/o10n-x/wordpress-css-optimization/blob/master/docs/images/google-pagespeed-100.png) ![Google Lighthouse Critical Performance Score](https://github.com/o10n-x/wordpress-css-optimization/blob/master/docs/images/lighthouse-performance-15.png)

### Google PageSpeed score is outdated

For the open web to have a chance of survival in a mobile era it needs to compete with and win from native mobile apps. Google is dependent on the open web for it's advertising revenue. Google therefor seeks a way to secure the open web and the main objective is to rapidly enhance the quality of the open web to meet the standards of native mobile apps.

For SEO it is therefor simple: websites will need to meet the standards set by the [Google Lighthouse Test](https://developers.google.com/web/tools/lighthouse/) (or Google's future new tests). A website with perfect scores will be preferred in search over low performance websites. The officially announced [Google Speed Update](https://searchengineland.com/google-speed-update-page-speed-will-become-ranking-factor-mobile-search-289904) (July 2018) shows that Google is going as far as it can to drive people to enhance the quality to ultra high levels, to meet the quality of, and hopefully beat native mobile apps.

A perfect Google Lighthouse Score includes validation of a website as a [Progressive Web App (PWA)](https://developers.google.com/web/progressive-web-apps/).

Google offers another new website performance test that is much tougher than the Google PageSpeed score. It is based on a AI neural network and it can be accessed on https://testmysite.thinkwithgoogle.com

## Description

An advantage of Amazon AWS CloudFront as a page cache is that they provide the lowest costs. For an average business website, the total costs will be literally less than 1 dollar per month. Amazon provides free SSL certificates and there are no hidden costs.

An other advantage is that CloudFront supports root domains when using [Amazon AWS Route53 DNS service](https://aws.amazon.com/route53/), making it possible to use CloudFront's CDN for https://yourdomain.com/

Amazon provides dedicated IP's for geographic regions. This means that a website will physically load from a location near the visitor. For a visitor from Sweden the website may be physically loaded from a server and IP in Stockholm.

![CloudFront performance PageSpeed.pro](https://github.com/o10n-x/wordpress-cloudfront-page-cache/blob/master/docs/images/pagespeed-aws-cloudfront.png)

Amazon AWS CloudFront is among the [fastest](https://encrypted.google.com/search?q=cloudfront+vs) CDN providers available with the greatest global network. This makes it a perfect option for any website that wants to reach an international audience or that simply wants a fast and secure page cache for a low cost VPS.

![CloudFront network 2017](https://github.com/o10n-x/wordpress-cloudfront-page-cache/blob/master/docs/images/aws-cloudfront-network-2017.png)

We are interested to learn about your experiences and feedback when using this plugin. Please submit your feedback on the [Github community forum](https://github.com/o10n-x/wordpress-cloudfront-page-cache/issues).