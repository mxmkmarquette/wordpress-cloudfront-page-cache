<?php
namespace CloudFrontPageCache;

/**
 * Admin header template
 *
 * @package    cloudfront-page-cache
 * @subpackage optimization/admin
 * @author     Optimization.Team <info@optimization.team>
 */
if (!defined('ABSPATH')) {
    exit;
}

    if (isset($_GET['view'])) {
        ?>

<!-- header -->
<div class="wrap">

    <!-- hide notices from other plugins -->
    <div class="other-notices-notice" style="display:none;"><strong class="count"></strong> notices from other plugins have been hidden. <a href="javascript:void(0);" class="show" data-hide="Hide notices">Show notices</a></div>
    <div class="other-notices" style="display:none;"><div class="wp-header-end"></div></div>

    <a href="https://aws.amazon.com/cloudfront/?sc_channel=O10N&sc_campaign=cloudfront_page_cache&sc_publisher=wordpress&sc_medium=plugin&sc_content=cloudfront_page_cache" target="_blank" rel="noopener"><img src="<?php print plugins_url('admin/images/amazon-cloudfront.png', 'cloudfront-page-cache/cloudefront-page-cache.php'); ?>" height="80" style="position:absolute;top:1em;right:1em;z-index:10;"></a> 
    <h1 class="cloudfrontpagecache-title" style="vertical-align:middle;">CloudFront <strong>Page Cache</strong></h1>

    <div id="cloudfrontpagecache-notices"><?php
        /** Display optimization errors / notices */
        do_action('cf-page-cache-notices'); ?>
        </div>
</div>

<div class="wrap cloudfrontpagecache-wrap" style="position:relative;">

<?php 
        // include navbar template
        require_once CF_FPC_PATH . 'admin/header-navbar.inc.php'; ?>
	<div id="poststuff">
<?php
    }
