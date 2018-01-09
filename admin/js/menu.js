jQuery(function($) {

    // add icon in menu
    if ($('#menu-settings').length) {
        $('#menu-settings li a[href="options-general.php?page=cloudfront-page-cache"]').html('<img src="' + cfpagecachedir + 'admin/images/aws-block.svg" width="16" height="16" align="absmiddle" title="AWS CloudFront Page Cache" style="margin-right:2px;"> Page Cache');
    }
});