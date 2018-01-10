<?php
namespace CloudFrontPageCache;

/**
 * Admin footer template
 *
 * @package    cloudfront-page-cache
 * @subpackage optimization/admin
 * @author     Optimization.Team <info@optimization.team>
 */
if (!defined('ABSPATH')) {
    exit;
}

    if ($this->active_view !== 'intro') {
        ?>
 </div></div>
<div class="clear"></div>
<br />
<div style="padding:5px;padding-left:10px;padding-top:1px;background:white;">
	<h3>Help &amp; Reporting Issues</h3>
	<p>For support and reporting bugs/issues, please use the <a href="https://github.com/optimalisatie/cloudfront-page-cache/issues" target="_blank" rel="noopener">Github Issues forum</a> to enable more people to see and potentially answer your question. Alternatively, you can use the <a href="https://wordpress.org/support/plugin/cf-page-cache/" target="_blank" rel="noopener">WordPress support forum</a>.</p>

	<p style="height:17px;"><span style="float:left;">The source code of this plugin is available on <a href="https://github.com/optimalisatie/cloudfront-page-cache/" target="_blank" rel="noopener">Github</a></span> <span style="float:left;margin-left:7px;"><a class="github-button" data-manual="1" href="https://github.com/optimalisatie/cloudfront-page-cache" data-icon="octicon-star" data-show-count="true" aria-label="Star optimalisatie/cloudfront-page-cache on GitHub">Star</a></span></p>

	<div class="clear"></div>
</div>
<script>
jQuery(function() {
	var s = document.createElement('script');
	s.async = true;
	s.src = 'https://buttons.github.io/buttons.js';
	document.head.appendChild(s);
});
</script>

<?php
    }
?>

<style>
#wpfooter {
	position:relative!important;
	clear:both;
}
</style>