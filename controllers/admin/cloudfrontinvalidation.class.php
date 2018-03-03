<?php
namespace O10n;

/**
 * CloudFront Invalidation Admin Controller
 *
 * @package    optimization
 * @subpackage optimization/controllers/admin
 * @author     Optimization.Team <info@optimization.team>
 */
if (!defined('ABSPATH')) {
    exit;
}

class AdminCloudfrontinvalidation extends ModuleAdminController implements Module_Admin_Controller_Interface
{

    // invalidation status update interval
    private $invalidation_status_update_interval = 60;

    private $budget = 1000; // 1000 free invalidations per month
    private $overusage_price = 0.005; // price per invalidation for overusage

    /**
     * Load controller
     *
     * @param  Core       $Core Core controller instance.
     * @return Controller Controller instance.
     */
    public static function &load(Core $Core)
    {
        // instantiate controller
        return parent::construct($Core, array(
            'admin',
            'options',
            'shutdown',
            'cloudfront',
            'AdminCloudfront'
        ));
    }

    /**
     * Setup controller
     */
    protected function setup()
    {
        // check if invalidation is enabled
        if (!$this->options->bool('cloudfront.invalidation.enabled', true)) {
            return;
        }

        // add admin bar menu
        add_action('edit_form_top', array( $this, 'post_invalidation_button'), $this->first_priority);

        // mark post edit form for script queue
        add_action('add_meta_boxes', array( $this, 'mark_edit_form'), $this->first_priority);

        // add admin bar menu
        add_action('admin_enqueue_scripts', array( $this, 'post_invalidation_button_style'), $this->first_priority);

        // handle purge requests when saving posts
        add_action('save_post', array( $this, 'save_post'), $this->first_priority, 1);

        $invalidations_in_progress = get_option('o10n_cloudfront_invalidations_in_progress', array());
        if (!empty($invalidations_in_progress)) {
            foreach ($invalidations_in_progress as $invalidation) {
                $this->admin->add_notice('<span class="spinner" style="float:none;margin:0px;display:inline-block;visibility:visible;"></span> Invalidation request <a href="https://console.aws.amazon.com/cloudfront/home#distribution-settings:'.esc_attr($this->options->get('distribution_id')).'" target="_blank" rel="noopener" title="'.esc_attr(implode(PHP_EOL, $invalidation['paths'])).'">'.esc_html($invalidation['id']).'</a> <code>processing</code>.', 'cloudfront', 'NOTICE');

                if (!isset($invalidation['last-update'])) {
                    $invalidation['last-update'] = $invalidation['date'];
                }

                // check once per minute
                if ($invalidation['last-update'] < (time() - $this->update_interval)) {
                    $this->shutdown->add(array($this,'verify_inprogress'));
                }
            }
        }

        // purge cache
        if (isset($_GET['purge'])) {
            add_action('admin_init', array($this, 'process_purge_request'), $this->first_priority);
        }
    }

    /**
     * Process purge request
     */
    final public function process_purge_request()
    {

        // verify admin permissions
        if (!$this->admin->is_admin()) {
            return;
        }

        $purge = (isset($_GET['purge'])) ? $_GET['purge'] : false;
        if (!$purge) {
            return;
        }

        // return URL
        $return = (isset($_GET['return']) && preg_match('|^http(s)?://|Ui', $_GET['return'])) ? $_GET['return'] : '';

        switch ($purge) {
            case "page":

                $path = (isset($_GET['path'])) ? trim($_GET['path']) : '';
                if ($path === '') {
                    $this->admin->add_notice('Purge request contained no path for invalidation.', 'cloudfront');

                    return;
                }

                // verify path
                if (substr($path, 0, 1) !== '/') {
                    $this->admin->add_notice('Purge request contained invalid path.', 'cloudfront');

                    return;
                }

                // create invalidation for all pages
                try {
                    $this->cloudfront->create_invalidations($path);
                } catch (Exception $err) {
                }
            break;
            case "cf":

                try {
                    // create invalidation for all pages
                    $this->cloudfront->create_invalidations('/*');
                } catch (Exception $err) {
                }
            break;
            case "plugins":

                // clear cache of page cache related plugins such as Autoptimize, WP Super Cache and others
                try {
                    $this->purge_plugin_caches();
                } catch (Exception $err) {
                }
            break;
            case "all":

                try {
                    // clear cache of page cache related plugins such as Autoptimize, WP Super Cache and others
                    $this->purge_plugin_caches();

                    // create invalidation for all pages
                    $this->cloudfront->create_invalidations('/*');
                } catch (Exception $err) {
                }
            break;
        }

        // redirect back to origin url
        if ($return) {
            wp_redirect($return);
            exit;
        } else {
            wp_redirect(add_query_arg(array( 'page' => 'o10n-cloudfront', 'tab' => 'invalidation', 't' => time() ), admin_url($this->AdminCloudfront->admin_base())));
            exit;
        }
    }

    /**
     * Return budget stats
     */
    final public function budget_stats()
    {
        $stats = array();

        // get invalidation count
        $count = get_option('o10n_cloudfront_invalidation_count', array());
        $active_month = date('Ym', current_time('timestamp'));
        if (isset($count[$active_month]) && intval($count[$active_month]) > 0) {
            $stats['usage'] = intval($count[$active_month]);
        } else {
            $stats['usage'] = 0;
        }

        $stats['percentage'] = ($stats['usage'] / ($this->budget / 100));

        if ($stats['usage'] > $this->budget) {
            $stats['costs'] = ($stats['usage'] - $this->budget) * $this->overusage_price;
        } else {
            $stats['costs'] = 0;
        }

        return $stats;
    }

    /**
     * Verify invalidation status
     */
    final public function verify_inprogress()
    {

        // verify admin permissions
        if (!$this->admin->is_admin()) {
            return;
        }

        $invalidations_in_progress = get_option('o10n_cloudfront_invalidations_in_progress', array());
        $updated_list = array();
        foreach ($invalidations_in_progress as $invalidation) {
            if ($invalidation['date'] < (time() - $this->invalidation_status_update_interval)) {
                try {
                    $invalidation_result = $this->cloudfront->get_invalidation($invalidation['id']);
                } catch (Exception $err) {
                    sleep(1);
                    continue;
                }

                // verify status
                if ($invalidation_result['status'] !== 'InProgress') {
                    if ($invalidation_result['status'] === 'Completed') {
                        $this->admin->add_notice('Invalidation request <a href="https://console.aws.amazon.com/cloudfront/home#distribution-settings:'.esc_attr($this->options->get('distribution_id')).'" target="_blank" rel="noopener" title="'.esc_attr(implode(PHP_EOL, $invalidation['paths'])).'">'.esc_html($invalidation['id']).'</a> <code>completed</code>.', 'cloudfront', 'SUCCESS', array('persist' => 'expire', 'max-views' => 3, 'max-age' => 60));
                    } else {
                        $this->admin->add_notice('Invalidation request <a href="https://console.aws.amazon.com/cloudfront/home#distribution-settings:'.esc_attr($this->options->get('distribution_id')).'" target="_blank" rel="noopener" title="'.esc_attr(implode(PHP_EOL, $invalidation['paths'])).'">'.esc_html($invalidation['id']).'</a> <code>'.$invalidation_result['status'].'</code>.', 'cloudfront', 'ERROR', array('persist' => 'expire', 'max-views' => 3, 'max-age' => 60));
                    }
                } else {
                    $invalidation['last-update'] = time();
                    $updated_list[] = $invalidation;
                }
            } else {
                $updated_list[] = $invalidation;
            }
        }

        // update InProgress list
        update_option('o10n_cloudfront_invalidations_in_progress', $updated_list, false);
    }
    
    /**
     * Mark post edit form
     */
    final public function mark_edit_form()
    {
        $this->edit_form = true;
    }
    
    /**
     * Admin bar option
     *
     * @param  object       Admin bar object
     */
    final public function post_invalidation_button_style()
    {

        // post edit form and admin permissions
        if (!$this->edit_form || !$this->admin->is_admin()) {
            return;
        }

        // invalidation button CSS
        wp_enqueue_style('o10n_cloudfront_post_invalidation', $this->core->modules('cloudfront')->dir_url() . 'admin/css/view-cloudfront-admin.css');
    }

    /**
     * Admin bar option
     *
     * @param  object       Admin bar object
     */
    final public function post_invalidation_button($admin_bar)
    {
        // verify admin permissions
        if (!$this->admin->is_admin() || !$user = wp_get_current_user()) {
            return;
        }

        // get budget stats
        $budget_stats = $this->budget_stats();

        $budget_color = '';
        if ($budget_stats['percentage'] >= 90) {
            $budget_color = 'color:red;font-weight:bold;';
        } elseif ($budget_stats['percentage'] >= 80) {
            $budget_color = 'color:maroon;';
        }

        $default_invalidation = get_user_meta($user->ID, 'cloudfront_page_cache_default_invalidation', true); ?>
<div id="cloudfront_invalidate_container" style="display:none;"><hr />
    <div class="lnk"><img src="<?php print $this->core->modules('cloudfront')->dir_url(); ?>admin/images/aws-block.svg" class="logo" align="absmiddle" alt="CF"> CloudFront: <a href="javascript:void(0);" class="action" title="Click to Purge Cache"<?php if ($default_invalidation) {
            print ' style="display:none;"';
        } ?>>no action</a></div>

    <div id="cloudfront-select"<?php if (!$default_invalidation) {
            print ' style="display:none;"';
        } ?>>
        <select name="o10n_cloudfront_purge">
            <option value="">---</option>
            <optgroup label="CloudFront">
                <option value="cf-page"<?php if ($default_invalidation === 'cf-page') {
            print ' selected';
        } ?>>Purge Post (/slug/)</option>
                <option value="cf-all"<?php if ($default_invalidation === 'cf-all') {
            print ' selected';
        } ?>>Purge All (/*)</option>
            </optgroup>
            <option value="plugins"<?php if ($default_invalidation === 'plugins') {
            print ' selected';
        } ?>>Purge Cache Plugins</option>
            <option value="all"<?php if ($default_invalidation === 'all') {
            print ' selected';
        } ?>>Purge All (CloudFront + plugins)</option>
        </select>
        <p class="budget">CloudFront invalidation budget: <span style="<?php print $budget_color; ?>"><?php print (string)$budget_stats['usage']; ?></span>/<?php print (string)$this->budget; ?><?php if ($budget_stats['costs'] > 0) {
            print ' (+$' . number_format_i18n($budget_stats['costs'], 2) . ')';
        } ?></p>
        <p id="cloudfront-save-default" style="display:none;"><label><input type="checkbox" name="o10n_cloudfront_purge_default" value="1"> Save default purge setting</label></p>
    </div>
</div>
        <?php
    }

    /**
     * Process purge request in save post action
     */
    final public function save_post($post_id)
    {
        // get user
        if (!$user = wp_get_current_user()) {
            return;
        }

        // purge request
        $purge = (isset($_REQUEST['o10n_cloudfront_purge']) && $_REQUEST['o10n_cloudfront_purge']) ? $_REQUEST['o10n_cloudfront_purge'] : '';

        $paths = array();

        if ($purge) {
            switch ($purge) {
                case "cf-page":
                    $url = get_permalink($post_id);
                    $paths[] = preg_replace('#^http(s)?://[^/]+(/|$)#Ui', '/', $url);
                break;
                case "cf-all":
                    $paths[] = '/*';
                break;
                case "plugins":
                    $this->purge_plugin_caches();
                break;
                case "all":

                    // clear cache of page cache related plugins
                    $this->purge_plugin_caches();
                    
                    // invalidate all pages on CloudFront
                    $paths[] = '/*';
                break;
            }
        }

        if (!empty($paths)) {

            // create invalidation request
            $this->cloudfront->create_invalidations($paths);
        }

        // update default setting
        if (isset($_REQUEST['o10n_cloudfront_purge_default']) && $_REQUEST['o10n_cloudfront_purge_default']) {
            update_user_meta($user->ID, 'cloudfront_page_cache_default_invalidation', $purge);
        }
    }

    /**
     * Return budget
     */
    final public function budget()
    {
        return $this->budget;
    }

    /**
     * Return overusage price
     */
    final public function overusage_price()
    {
        return $this->overusage_price;
    }

    /**
     * Clear cache of page cache related plugins
     */
    final public function purge_plugin_caches()
    {

        // verify admin permissions
        if (!$this->admin->is_admin()) {
            return;
        }

        if (class_exists('autoptimizeCache')) {
            \autoptimizeCache::clearall();
        }

        if (function_exists('rocket_clean_domain')) {
            rocket_clean_domain();
        } elseif (function_exists('wp_cache_clear_cache')) {
            if (is_multisite()) {
                $blog_id = get_current_blog_id();
                wp_cache_clear_cache($blog_id);
            } else {
                wp_cache_clear_cache();
            }
        } elseif (has_action('cachify_flush_cache')) {
            do_action('cachify_flush_cache');
        } elseif (function_exists('w3tc_pgcache_flush')) {
            w3tc_pgcache_flush();
        } elseif (function_exists('wp_fast_cache_bulk_delete_all')) {
            wp_fast_cache_bulk_delete_all(); // still to retest
        } elseif (class_exists("WpFastestCache")) {
            $wpfc = new WpFastestCache();
            $wpfc->deleteCache();
        } elseif (class_exists("c_ws_plugin__qcache_purging_routines")) {
            c_ws_plugin__qcache_purging_routines::purge_cache_dir(); // quick cache, still to retest
        } elseif (class_exists("zencache")) {
            zencache::clear();
        } elseif (class_exists("comet_cache")) {
            comet_cache::clear();
        } elseif (class_exists("WpeCommon")) {
            if (apply_filters('autoptimize_flush_wpengine_aggressive', false)) {
                if (method_exists("WpeCommon", "purge_memcached")) {
                    WpeCommon::purge_memcached();
                }
                if (method_exists("WpeCommon", "clear_maxcdn_cache")) {
                    WpeCommon::clear_maxcdn_cache();
                }
            }
            if (method_exists("WpeCommon", "purge_varnish_cache")) {
                WpeCommon::purge_varnish_cache();
            }
        } elseif (function_exists('sg_cachepress_purge_cache')) {
            sg_cachepress_purge_cache();
        } elseif (file_exists(WP_CONTENT_DIR.'/wp-cache-config.php') && function_exists('prune_super_cache')) {
            // fallback for WP-Super-Cache
            global $cache_path;
            if (is_multisite()) {
                $blog_id = get_current_blog_id();
                prune_super_cache(get_supercache_dir($blog_id), true);
                prune_super_cache($cache_path . 'blogs/', true);
            } else {
                prune_super_cache($cache_path.'supercache/', true);
                prune_super_cache($cache_path, true);
            }
        }

        $this->admin->add_notice('Cache of page cache related plugins cleared.', 'cloudfront', 'SUCCESS', array('persist' => 'expire','max-views' => 1));
    }
}
