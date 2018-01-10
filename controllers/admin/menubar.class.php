<?php
namespace CloudFrontPageCache;

/**
 * Admin Top Menu Bar Controller
 *
 * @package    cloudfront-page-cache
 * @subpackage cloudfront-page-cache/controllers/admin
 * @author     PageSpeed.pro <info@pagespeed.pro>
 */
if (!defined('ABSPATH')) {
    exit;
}

class AdminMenuBar extends Controller implements Controller_Interface
{
    /**
     * Load controller
     *
     * @param  Core       $Core Core controller instance.
     * @return Controller Controller instance.
     */
    public static function &load(Core &$Core)
    {
        // instantiate controller
        return parent::construct($Core, array(
            'options'
        ));
    }

    /**
     * Setup controller
     */
    protected function setup()
    {

        // check settings
        if (!$this->options->bool('invalidation', true) || !$this->options->bool('adminbar', true)) {
            return;
        }

        // add admin bar menu
        add_action('admin_bar_menu', array( $this, 'admin_bar'), 100);
    }
    
    
    /**
     * Admin bar option
     *
     * @param  object       Admin bar object
     */
    final public function admin_bar($admin_bar)
    {
        if (is_admin()
            || (defined('DOING_AJAX') && DOING_AJAX)
            || in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'))
        ) {
            $is_admin = true;
            $currenturl = home_url();
        } else {
            $is_admin = false;
            $currenturl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        } ?>
<script>
/** CloudFront confirm purge cache action */
var cfpc_confirm_purge = function(el) {
    var title = jQuery(el).attr('title');
    if (!confirm('Are you sure you want to...\n\n'+title)) {
        return false;
    }
}
</script>
<?php
        // use public host
        $currenturl_host = $this->options->get('host');
        if (!$currenturl_host) {
            // parse url
            $currenturl_host = parse_url($currenturl, PHP_URL_HOST);
        } else {
            $currenturl = str_replace($this->options->get('origin'), $currenturl_host, $currenturl);
        }

        $settings_url = add_query_arg(array( 'page' => 'cloudfront-page-cache' ), admin_url('options-general.php'));
        $admin_bar->add_menu(array(
            'id' => 'cloudfront-page-cache',
            'title' => '<span class="ab-label"><img src="' . CF_FPC_URI . 'admin/images/aws-block.svg" style="width:16px;height:16px;" align="absmiddle" alt="CF" style="margin-right:2px;"> ' . __('Cache', 'cloudfront-page-cache') . '</span>',
            'href' => $settings_url,
            'meta' => array( 'title' => __('CloudFront Page Cache', 'cloudfront-page-cache'), 'class' => 'ab-sub-secondary' )

        ));

        if (!$is_admin) {
            $admin_bar->add_group(array(
                'parent' => 'cloudfront-page-cache',
                'id' => 'cloudfront-page-cache-top'
            ));

            // path to invalidate
            $path = preg_replace('#http(s):\/\/[^\/]+(/|$)#Ui', '/', $currenturl);

            $admin_bar->add_node(array(
                'parent' => 'cloudfront-page-cache-top',
                'id' => 'cloudfront-page-cache-clear-cf-page',
                'title' => __('CF: Invalidate Page', 'cloudfront-page-cache'),
                'href' => add_query_arg(array( 'page' => 'cloudfront-page-cache', 'view' => 'invalidation', 'purge' => 'page', 'path' => $path, 'return' => $currenturl, 't' => time() ), admin_url('options-general.php')),
                'meta' => array( 'title' => __('Clear CloudFront cache for current page.', 'cloudfront-page-cache'), 'onclick' => 'return cfpc_confirm_purge(this);' )
            ));
        }

        $admin_bar->add_group(array(
            'parent' => 'cloudfront-page-cache',
            'id' => 'cloudfront-page-cache-second',
            'meta' => array(
                'class' => 'ab-sub-secondary',
            )
        ));

        $admin_bar->add_node(array(
            'parent' => 'cloudfront-page-cache-second',
            'id' => 'cloudfront-page-cache-clear-cf',
            'title' => __('CF: Invalidate All <code>/*</code>', 'cloudfront-page-cache'),
            'href' => add_query_arg(array( 'page' => 'cloudfront-page-cache', 'view' => 'invalidation', 'purge' => 'cf', 'return' => ($is_admin) ? false : $currenturl, 't' => time() ), admin_url('options-general.php')),
            'meta' => array( 'title' => __('Clear CloudFront cache for all pages.', 'cloudfront-page-cache'), 'onclick' => 'return cfpc_confirm_purge(this);' )
        ));

        $admin_bar->add_node(array(
            'parent' => 'cloudfront-page-cache-second',
            'id' => 'cloudfront-page-cache-clear-plugins',
            'title' => __('Clear Plugin Caches', 'cloudfront-page-cache'),
            'href' => add_query_arg(array( 'page' => 'cloudfront-page-cache', 'view' => 'invalidation', 'purge' => 'plugins', 'return' => ($is_admin) ? false : $currenturl, 't' => time() ), admin_url('options-general.php')),
            'meta' => array( 'title' => __('Clear the cache of page cache related plugins such as Autoptimize, WP Super Cache and others.', 'cloudfront-page-cache'), 'onclick' => 'return cfpc_confirm_purge(this);' )
        ));

        $admin_bar->add_node(array(
            'parent' => 'cloudfront-page-cache-second',
            'id' => 'cloudfront-page-cache-clear-all',
            'title' => __('Clear All: CF + Plugin Caches', 'cloudfront-page-cache'),
            'href' => add_query_arg(array( 'page' => 'cloudfront-page-cache', 'view' => 'invalidation', 'purge' => 'all', 'return' => ($is_admin) ? false : $currenturl, 't' => time() ), admin_url('options-general.php')),
            'meta' => array( 'title' => __('Invalidate all pages on CloudFront (/*) + clear the cache of plugins such as Autoptimize, WP Super Cache and others.', 'cloudfront-page-cache'), 'onclick' => 'return cfpc_confirm_purge(this);' )
        ));

        $admin_bar->add_node(array(
            'parent' => 'cloudfront-page-cache',
            'id' => 'cloudfront-page-cache-speed-tests',
            'title' => __('Speed Tests', 'cloudfront-page-cache'),
            'href' => false,
            'meta' => array( 'title' => __('Speed Tests', 'cloudfront-page-cache') )
        ));

        $admin_bar->add_node(array(
            'parent' => 'cloudfront-page-cache-speed-tests',
            'id' => 'cloudfront-page-cache-securi-speed-test',
            'title' => __('Securi', 'cloudfront-page-cache'),
            'href' => 'https://performance.sucuri.net/domain/' . $currenturl_host . '?utm_source=wordpress&utm_medium=plugin&utm_term=optimization&utm_campaign=CloudFront%20Page%20Cache',
            'meta' => array( 'title' => __('Securi Speed Test', 'cloudfront-page-cache'), 'target' => '_blank' )
        ));

        $admin_bar->add_node(array(
            'parent' => 'cloudfront-page-cache-speed-tests',
            'id' => 'cloudfront-page-cache-keycdn-speed-test',
            'title' => __('KeyCDN', 'cloudfront-page-cache'),
            'href' => 'https://tools.keycdn.com/speed?url='.urlencode($currenturl).'&utm_source=wordpress&utm_medium=plugin&utm_term=optimization&utm_campaign=CloudFront%20Page%20Cache',
            'meta' => array( 'title' => __('KeyCDN Speed Test', 'cloudfront-page-cache'), 'target' => '_blank' )
        ));

        $admin_bar->add_node(array(
            'parent' => 'cloudfront-page-cache-speed-tests',
            'id' => 'cloudfront-page-cache-uptrends-speed-test',
            'title' => __('Uptrends', 'cloudfront-page-cache'),
            'href' => 'https://www.uptrends.com/tools/website-speed-test?url='.urlencode($currenturl).'&utm_source=wordpress&utm_medium=plugin&utm_term=optimization&utm_campaign=CloudFront%20Page%20Cache',
            'meta' => array( 'title' => __('Uptrends Speed Test', 'cloudfront-page-cache'), 'target' => '_blank' )
        ));

        $admin_bar->add_node(array(
            'parent' => 'cloudfront-page-cache-speed-tests',
            'id' => 'cloudfront-page-cache-dotcom-speed-test',
            'title' => __('Dotcom-Tools.com', 'cloudfront-page-cache'),
            'href' => 'https://www.dotcom-tools.com/website-speed-test.aspx?url='.urlencode($currenturl).'&utm_source=wordpress&utm_medium=plugin&utm_term=optimization&utm_campaign=CloudFront%20Page%20Cache',
            'meta' => array( 'title' => __('Dotcom-Tools Speed Test', 'cloudfront-page-cache'), 'target' => '_blank' )
        ));

        $admin_bar->add_node(array(
            'parent' => 'cloudfront-page-cache-speed-tests',
            'id' => 'cloudfront-page-cache-webpagetest',
            'title' => __('WebPageTest.org', 'cloudfront-page-cache'),
            'href' => 'https://www.webpagetest.org/?url='.urlencode($currenturl).'&utm_source=wordpress&utm_medium=plugin&utm_term=optimization&utm_campaign=CloudFront%20Page%20Cache',
            'meta' => array( 'title' => __('WebPageTest.org Speed Test', 'cloudfront-page-cache'), 'target' => '_blank' )
        ));

        $admin_bar->add_node(array(
            'parent' => 'cloudfront-page-cache-speed-tests',
            'id' => 'cloudfront-page-cache-gtmetrix',
            'title' => __('GTMetrix', 'cloudfront-page-cache'),
            'href' => 'https://gtmetrix.com/?url='.urlencode($currenturl).'&utm_source=wordpress&utm_medium=plugin&utm_term=optimization&utm_campaign=CloudFront%20Page%20Cache',
            'meta' => array( 'title' => __('GTMetrix Speed Test', 'cloudfront-page-cache'), 'target' => '_blank' )
        ));

        

        return;

        /**
         * Compare Critical CSS vs Full CSS
         */
        $admin_bar->add_node(array(
            'parent' => 'abovethefold-top',
            'id' => 'abovethefold-tools-compare',
            'title' => __('Critical CSS Quality Test', 'cloudfront-page-cache'),
            'href' => '', //$this->CTRL->view_url('critical-css-mirror'),
            'meta' => array( 'title' => __('Critical CSS Quality Test', 'cloudfront-page-cache'), 'target' => '_blank' )
        ));

        $admin_bar->add_node(array(
            'parent' => 'abovethefold-top',
            'id' => 'abovethefold-cache',
            'title' => __('Cache', 'cloudfront-page-cache')
        ));

        /**
         * Clear All
         */
        $clear_url = add_query_arg(array( 'page' => 'cloudfront-page-cache', 'clear' => 'all' ), admin_url('options-general.php'));
        $nonced_url = wp_nonce_url($clear_url, 'cloudfront-page-cache');
        $admin_bar->add_node(array(
            'parent' => 'abovethefold-cache',
            'id' => 'abovethefold-cache-clear-all',
            'title' => '<strong>'.__('Clear All Caches', 'cloudfront-page-cache') . '</strong>',
            'href' => $nonced_url,
            'meta' => array( 'title' => __('Clear All Caches', 'cloudfront-page-cache') )
        ));

        /**
         * CSS Minify cache clear
         */
        //if (isset($this->CTRL->options['cssminify']) && intval($this->CTRL->options['cssminify']) === 1) {
        if (true) {
            $clear_url = add_query_arg(array( 'page' => 'cloudfront-page-cache', 'clear' => 'cssminifycache' ), admin_url('options-general.php'));
            $nonced_url = wp_nonce_url($clear_url, 'cloudfront-page-cache');
            $admin_bar->add_node(array(
                'parent' => 'abovethefold-cache',
                'id' => 'abovethefold-cache-clear-cssminify',
                'title' => __('Clear CSS Cache', 'cloudfront-page-cache'),
                'href' => $nonced_url,
                'meta' => array( 'title' => __('Clear CSS Cache', 'cloudfront-page-cache') )
            ));
        }

        /**
         * CSS Minify cache clear
         */
        if (true) {
            $clear_url = add_query_arg(array( 'page' => 'cloudfront-page-cache', 'clear' => 'jsminifycache' ), admin_url('options-general.php'));
            $nonced_url = wp_nonce_url($clear_url, 'cloudfront-page-cache');
            $admin_bar->add_node(array(
                'parent' => 'abovethefold-cache',
                'id' => 'abovethefold-cache-clear-jsminify',
                'title' => __('Clear Javascript Cache', 'cloudfront-page-cache'),
                'href' => $nonced_url,
                'meta' => array( 'title' => __('Clear Javascript Cache', 'cloudfront-page-cache') )
            ));
        }

        /**
         * Page cache clear
         */
        $clear_url = add_query_arg(array( 'page' => 'cloudfront-page-cache', 'clear' => 'pagecache' ), admin_url('options-general.php'));
        $nonced_url = wp_nonce_url($clear_url, 'cloudfront-page-cache');
        $admin_bar->add_node(array(
            'parent' => 'abovethefold-cache',
            'id' => 'abovethefold-cache-clear-pagecache',
            'title' => __('Clear Page Cache', 'cloudfront-page-cache'),
            'href' => $nonced_url,
            'meta' => array( 'title' => __('Clear Page Cache', 'cloudfront-page-cache') )
        ));

        $admin_bar->add_node(array(
            'parent' => 'abovethefold-top',
            'id' => 'abovethefold-tools',
            'title' => __('Other Tools', 'cloudfront-page-cache')
        ));

        if (is_admin()
            || (defined('DOING_AJAX') && DOING_AJAX)
            || in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'))
        ) {
            $currenturl = home_url();
        } else {
            $currenturl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }

        /**
         * Extract Full CSS
         */
        $admin_bar->add_node(array(
            'parent' => 'abovethefold-tools',
            'id' => 'abovethefold-tools-extract',
            'title' => __('Extract Full CSS', 'cloudfront-page-cache'),
            'href' => '', // $this->CTRL->view_url('extract-css', array('output' => 'print')),
            'meta' => array( 'title' => __('Extract Full CSS', 'cloudfront-page-cache'), 'target' => '_blank' )
        ));

        /**
         * Google PageSpeed Score Test
         */
        $admin_bar->add_node(array(
            'parent' => 'cloudfront-page-cache',
            'id' => 'abovethefold-check-pagespeed-scores',
            'title' => __('Google PageSpeed Scores', 'cloudfront-page-cache'),
            'href' => 'https://testmysite.thinkwithgoogle.com/?url='.urlencode($currenturl) . '&hl=',
            'meta' => array( 'title' => __('Google PageSpeed Scores', 'cloudfront-page-cache'), 'target' => '_blank' )
        ));

        /**
         * Test Groups
         */
        $admin_bar->add_node(array(
            'parent' => 'cloudfront-page-cache',
            'id' => 'abovethefold-check-google',
            'title' => __('Google tests', 'cloudfront-page-cache')
        ));
        $admin_bar->add_node(array(
            'parent' => 'cloudfront-page-cache',
            'id' => 'abovethefold-check-speed',
            'title' => __('Speed tests', 'cloudfront-page-cache')
        ));
        $admin_bar->add_node(array(
            'parent' => 'cloudfront-page-cache',
            'id' => 'abovethefold-check-technical',
            'title' => __('Technical & security tests', 'cloudfront-page-cache')
        ));


        /**
         * Google Tests
         */
        $admin_bar->add_node(array(
            'parent' => 'abovethefold-check-google',
            'id' => 'abovethefold-check-pagespeed',
            'title' => __('Google PageSpeed Insights', 'cloudfront-page-cache'),
            'href' => 'https://developers.google.com/speed/pagespeed/insights/?url='.urlencode($currenturl) . '&hl=',
            'meta' => array( 'title' => __('Google PageSpeed Insights', 'cloudfront-page-cache'), 'target' => '_blank' )
        ));
        $admin_bar->add_node(array(
            'parent' => 'abovethefold-check-google',
            'id' => 'abovethefold-check-google-mobile',
            'title' => __('Google Mobile Test', 'cloudfront-page-cache'),
            'href' => 'https://www.google.com/webmasters/tools/mobile-friendly/?url='.urlencode($currenturl) . '&hl=',
            'meta' => array( 'title' => __('Google Mobile Test', 'cloudfront-page-cache'), 'target' => '_blank' )
        ));
        $admin_bar->add_node(array(
            'parent' => 'abovethefold-check-google',
            'id' => 'abovethefold-check-google-malware',
            'title' => __('Google Malware & Security', 'cloudfront-page-cache'),
            'href' => 'https://www.google.com/transparencyreport/safebrowsing/diagnostic/index.html?hl=#url='.urlencode(str_replace('www.', '', parse_url($currenturl, PHP_URL_HOST))),
            'meta' => array( 'title' => __('Google Malware & Security', 'cloudfront-page-cache'), 'target' => '_blank' )
        ));
        $admin_bar->add_node(array(
            'parent' => 'abovethefold-check-google',
            'id' => 'abovethefold-check-google-more',
            'title' => __('More tests', 'cloudfront-page-cache'),
            'href' => 'https://pagespeed.pro/tests#url='.urlencode($currenturl),
            'meta' => array( 'title' => __('More tests', 'cloudfront-page-cache'), 'target' => '_blank' )
        ));

        /**
         * Speed Tests
         */
        $admin_bar->add_node(array(
            'parent' => 'abovethefold-check-speed',
            'id' => 'abovethefold-check-webpagetest',
            'title' => __('WebPageTest.org', 'cloudfront-page-cache'),
            'href' => 'http://www.webpagetest.org/?url='.urlencode($currenturl).'',
            'meta' => array( 'title' => __('WebPageTest.org', 'cloudfront-page-cache'), 'target' => '_blank' )
        ));
        $admin_bar->add_node(array(
            'parent' => 'abovethefold-check-speed',
            'id' => 'abovethefold-check-pingdom',
            'title' => __('Pingdom Tools', 'cloudfront-page-cache'),
            'href' => 'http://tools.pingdom.com/fpt/?url='.urlencode($currenturl).'',
            'meta' => array( 'title' => __('Pingdom Tools', 'cloudfront-page-cache'), 'target' => '_blank' )
        ));
        $admin_bar->add_node(array(
            'parent' => 'abovethefold-check-speed',
            'id' => 'abovethefold-check-gtmetrix',
            'title' => __('GTmetrix', 'cloudfront-page-cache'),
            'href' => 'http://gtmetrix.com/?url='.urlencode($currenturl).'',
            'meta' => array( 'title' => __('GTmetrix', 'cloudfront-page-cache'), 'target' => '_blank' )
        ));
        $admin_bar->add_node(array(
            'parent' => 'abovethefold-check-speed',
            'id' => 'abovethefold-check-speed-more',
            'title' => __('More tests', 'cloudfront-page-cache'),
            'href' => 'https://pagespeed.pro/tests#url='.urlencode($currenturl),
            'meta' => array( 'title' => __('More tests', 'cloudfront-page-cache'), 'target' => '_blank' )
        ));

        /**
         * Technical & Security Tests
         */
        $admin_bar->add_node(array(
            'parent' => 'abovethefold-check-technical',
            'id' => 'abovethefold-check-securityheaders',
            'title' => __('SecurityHeaders.io', 'cloudfront-page-cache'),
            'href' => 'https://securityheaders.io/?q='.urlencode($currenturl).'&hide=on&followRedirects=on',
            'meta' => array( 'title' => __('SecurityHeaders.io', 'cloudfront-page-cache'), 'target' => '_blank' )
        ));
        $admin_bar->add_node(array(
            'parent' => 'abovethefold-check-technical',
            'id' => 'abovethefold-check-w3c',
            'title' => __('W3C HTML Validator', 'cloudfront-page-cache'),
            'href' => 'https://validator.w3.org/nu/?doc='.urlencode($currenturl).'',
            'meta' => array( 'title' => __('W3C HTML Validator', 'cloudfront-page-cache'), 'target' => '_blank' )
        ));
        $admin_bar->add_node(array(
            'parent' => 'abovethefold-check-technical',
            'id' => 'abovethefold-check-ssllabs',
            'title' => __('SSL Labs', 'cloudfront-page-cache'),
            'href' => 'https://www.ssllabs.com/ssltest/analyze.html?d='.urlencode($currenturl).'',
            'meta' => array( 'title' => __('SSL Labs', 'cloudfront-page-cache'), 'target' => '_blank' )
        ));
        $admin_bar->add_node(array(
            'parent' => 'abovethefold-check-technical',
            'id' => 'abovethefold-check-intodns',
            'title' => __('Into DNS', 'cloudfront-page-cache'),
            'href' => 'http://www.intodns.com/'.urlencode(str_replace('www.', '', parse_url($currenturl, PHP_URL_HOST))).'',
            'meta' => array( 'title' => __('Into DNS', 'cloudfront-page-cache'), 'target' => '_blank' )
        ));

        $admin_bar->add_node(array(
            'parent' => 'abovethefold-check-technical',
            'id' => 'abovethefold-check-gzip',
            'title' => __('GZIP Compression Test', 'cloudfront-page-cache'),
            'href' => ' http://checkgzipcompression.com/?url='.urlencode($currenturl).'',
            'meta' => array( 'title' => __('GZIP Compression Test', 'cloudfront-page-cache'), 'target' => '_blank' )
        ));



        $admin_bar->add_node(array(
            'parent' => 'abovethefold-check-technical',
            'id' => 'abovethefold-check-technical-more',
            'title' => __('More tests', 'cloudfront-page-cache'),
            'href' => 'https://pagespeed.pro/tests#url='.urlencode($currenturl),
            'meta' => array( 'title' => __('More tests', 'cloudfront-page-cache'), 'target' => '_blank' )
        ));
    }
}
