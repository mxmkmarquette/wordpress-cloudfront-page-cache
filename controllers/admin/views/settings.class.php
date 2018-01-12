<?php
namespace CloudFrontPageCache;

/**
 * Settings Admin View Controller
 *
 * @package    cloudfront-page-cache
 * @subpackage cloudfront-page-cache/controllers/admin
 * @author     PageSpeed.pro <info@pagespeed.pro>
 */
if (!defined('ABSPATH')) {
    exit;
}

class AdminViewSettings extends AdminViewBase
{
    protected static $view_key = 'settings'; // reference key for view

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
            'error',
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
        add_action('admin_post_cloudfront_page_cache', array($this, 'update_settings'), $this->first_priority);
    }

    /**
     * Update settings
     */
    public function update_settings()
    {
        // verify admin permissions
        if (!$this->admin->is_admin()) {
            return;
        }
        
        check_admin_referer('cloudfront-page-cache');

        // @link https://codex.wordpress.org/Function_Reference/stripslashes_deep
        $_POST = array_map('stripslashes_deep', $_POST);

        // get options
        $options = $this->options->getAll();
        if (!is_array($options)) {
            $options = array();
        }

        // input
        $input = (isset($_POST['cfpc']) && is_array($_POST['cfpc'])) ? $_POST['cfpc'] : array();

        // store config
        $options['enabled'] = (isset($input['enabled']) && intval($input['enabled']) === 1) ? true : false;

        $options['host'] = (isset($input['host'])) ? trim($input['host']) : '';
        $options['origin'] = (isset($input['origin'])) ? trim($input['origin']) : '';
        $options['domain'] = (isset($input['domain'])) ? trim($input['domain']) : '';
        $options['cache_age'] = (isset($input['cache_age'])) ? trim($input['cache_age']) : '';

        $options['invalidation'] = (isset($input['invalidation']) && intval($input['invalidation']) === 1) ? true : false;
        $options['distribution_id'] = (isset($input['distribution_id'])) ? trim($input['distribution_id']) : '';
        $options['api_key'] = (isset($input['api_key'])) ? trim($input['api_key']) : '';
        $options['api_secret'] = (isset($input['api_secret'])) ? trim($input['api_secret']) : '';
        $options['aws_region'] = (isset($input['aws_region'])) ? trim($input['aws_region']) : '';

        $options['adminbar'] = (isset($input['adminbar']) && intval($input['adminbar']) === 1) ? true : false;
        $options['api_test'] = (isset($input['api_test']) && intval($input['api_test']) === 1) ? true : false;

        if ($options['enabled']) {
            if ($options['host'] === '' || $options['origin'] === '') {
                $this->error->add_notice('To enable the page cache you need to configure the public and origin host.');
            }
        }

        // verify cache age
        if ($options['cache_age'] && !is_numeric($options['cache_age'])) {
            $options['cache_age'] = '';
            $this->error->add_notice('Cache age is not numeric.');
        }

        if ($options['invalidation'] && $options['api_test']) {
            if (empty($options['distribution_id']) || empty($options['api_key']) || empty($options['api_secret'])) {
                $this->error->add_notice('To enable invalidation, the Amazon AWS IAM Access Creditials are required.');
            } else {
                try {
                    $api_OK = $this->aws->test_connection($options['distribution_id'], $options['api_key'], $options['api_secret'], $options['aws_region']);
                } catch (Exception $err) {
                    $api_OK = -1;
                    $this->error->add_notice('Amazon AWS API connection failed: ' . $err->getMessage());
                }
                if ($api_OK !== -1) {
                    if (!$api_OK) {
                        $this->error->add_notice('Amazon AWS API connection failed: unknown error');
                    } else {
                        $this->error->add_notice('API connection verified.', 'SUCCESS');
                    }
                }
            }
        }

        // update settings
        update_option('cf-page-cache', $options, true);

        $this->error->add_notice('Settings saved.', 'SUCCESS');

        wp_redirect(add_query_arg(array( 'page' => 'cloudfront-page-cache', 'view' => 'settings', 't' => time() ), admin_url('options-general.php')));
        exit;
    }
}
