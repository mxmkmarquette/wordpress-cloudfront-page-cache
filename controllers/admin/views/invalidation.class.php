<?php
namespace CloudFrontPageCache;

/**
 * Invalidation Admin View Controller
 *
 * @package    cloudfront-page-cache
 * @subpackage cloudfront-page-cache/controllers/admin
 * @author     PageSpeed.pro <info@pagespeed.pro>
 */
if (!defined('ABSPATH')) {
    exit;
}

class AdminViewInvalidation extends AdminViewBase
{
    protected static $view_key = 'invalidation'; // reference key for view

    /**
     * Load controller
     *
     * @param  Core       $Core Core controller instance.
     * @param  string     $View View key.
     * @return Controller Controller instance.
     */
    public static function &load(Core &$Core)
    {
        // instantiate controller
        return parent::construct($Core, array(
            'admin',
            'options',
            'aws'
        ));
    }

    /**
     * Setup controller
     */
    protected function setup()
    {
        // set view etc
        parent::setup();

        // include no-cache headers
        nocache_headers();
    }

    /**
     * Setup view
     */
    public function setup_view()
    {
            
        // handle form submissions
        add_action('admin_post_cloudfront_invalidation', array($this, 'send_invalidation'), $this->first_priority);
    }

    /**
     * Send invalidation request
     */
    final public function send_invalidation()
    {

        // verify admin permissions
        if (!$this->admin->is_admin()) {
            return;
        }
        
        check_admin_referer('cloudfront-invalidation');

        // @link https://codex.wordpress.org/Function_Reference/stripslashes_deep
        $_POST = array_map('stripslashes_deep', $_POST);

        $invalidations = (isset($_POST['cfpc_invalidations'])) ? trim($_POST['cfpc_invalidations']) : '';

        if ($invalidations === '') {
            $this->error->add_notice('You did not enter paths to invalidate.');
        } else {
            $invalidations_raw = explode(PHP_EOL, $invalidations);
            $invalidations = array();
            foreach ($invalidations_raw as $invalidation) {
                $invalidation = trim($invalidation);
                if ($invalidation === '') {
                    continue;
                }
                $invalidations[] = preg_replace('#http(s)?:\/\/[^\/]+(/|$)#Ui', '/', $invalidation);
            }

            if (empty($invalidations)) {
                $this->error->add_notice('You did not enter paths to invalidate.');
            } else {

                // send invalidation request
                try {
                    $result = $this->aws->create_invalidations($invalidations);
                } catch (Exception $err) {
                    $result = false;
                    $this->error->add_notice('Failed to add notification: ' . $err->getMessage(), 'ERROR');
                }

                if ($result) {
                    $this->error->add_notice('Invalidation request <a href="https://console.aws.amazon.com/cloudfront/home#distribution-settings:'.esc_attr($this->options->get('distribution_id')).'" target="_blank" rel="noopener" title="'.esc_attr(implode(PHP_EOL, $result['paths'])).'">'.esc_html($result['id']).'</a> submitted.', 'SUCCESS');
                }
            }
        }

        if ($invalidations) {
            update_option('cf-page-cache-last-invalidation', $invalidations, false);
        }

        wp_redirect(add_query_arg(array( 'page' => 'cloudfront-page-cache', 'view' => 'invalidation', 't' => time() ), admin_url('options-general.php')));
        exit;
    }
}
