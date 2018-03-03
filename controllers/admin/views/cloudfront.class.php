<?php
namespace O10n;

/**
 * CloudFront Page Cache Admin View Controller
 *
 * @package    optimization
 * @subpackage optimization/controllers/admin
 * @author     Optimization.Team <info@optimization.team>
 */
if (!defined('ABSPATH')) {
    exit;
}

class AdminViewCloudfront extends AdminViewBase
{
    protected static $view_key = 'cloudfront'; // reference key for view
    protected $module_key = 'cloudfront';

    // default tab view
    private $default_tab_view = 'intro';

    /**
     * Load controller
     *
     * @param  Core       $Core   Core controller instance.
     * @param  false      $module Module parameter not used for core view controllers
     * @return Controller Controller instance.
     */
    public static function &load(Core $Core)
    {
        // instantiate controller
        return parent::construct($Core, array(
            'admin',
            'options',
            'json',
            'file',
            'AdminClient',
            'cloudfront',
            'AdminCloudfront',
            'AdminCloudfrontinvalidation'
        ));
    }
    
    /**
     * Setup controller
     */
    protected function setup()
    {
        // WPO plugin
        if (defined('O10N_WPO_VERSION')) {
            $this->default_tab_view = 'settings';
        }

        // set view etc
        parent::setup();
    }

    /**
     * Setup view
     */
    public function setup_view()
    {
        // process form submissions
        add_action('o10n_save_settings_verify_input', array( $this, 'verify_input' ), 10, 1);

        // enqueue scripts
        add_action('admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), $this->first_priority);

        // purge cache
        if ((isset($_REQUEST['tab']) && $_REQUEST['tab'] === 'invalidation') && isset($_GET['purge'])) {
            add_action('admin_init', array($this, 'process_purge_request'), $this->first_priority);
        }
    }

    /**
     * Return help tab data
     */
    final public function help_tab()
    {
        $data = array(
            'name' => __('CloudFront Page Cache', 'o10n'),
            'github' => 'https://github.com/o10n-x/wordpress-cloudfront-page-cache',
            'wordpress' => 'https://wordpress.org/support/plugin/cf-page-cache',
            'docs' => 'https://github.com/o10n-x/wordpress-cloudfront-page-cache/tree/master/docs'
        );

        return $data;
    }

    /**
     * Enqueue scripts and styles
     */
    final public function enqueue_scripts()
    {
        // skip if user is not logged in
        if (!is_admin() || !is_user_logged_in()) {
            return;
        }

        // set module path
        $this->AdminClient->set_config('module_url', $this->module->dir_url());
    }

    /**
     * Return view template
     */
    public function template($view_key = false)
    {
        // template view key
        $view_key = false;

        $tab = (isset($_REQUEST['tab'])) ? trim($_REQUEST['tab']) : $this->default_tab_view;
        switch ($tab) {
            case "intro":
            case "settings":
            case "invalidation":
                $view_key = 'cloudfront-' . $tab;
            break;
            default:
                throw new Exception('Invalid view ' . esc_html($view_key), 'core');
            break;
        }

        return parent::template($view_key);
    }
    
    /**
     * Verify settings input
     *
     * @param  object   Form input controller object
     */
    final public function verify_input($forminput)
    {
        // HTTP/2 Optimization

        $tab = (isset($_REQUEST['tab'])) ? trim($_REQUEST['tab']) : 'o10n';

        switch ($tab) {
            case "settings":

                $forminput->type_verify(array(
                    'cloudfront.enabled' => 'bool'
                ));

                // cloudfront enabled
                if ($forminput->bool('cloudfront.enabled')) {
                    $forminput->type_verify(array(
                        'cloudfront.host' => 'string',
                        'cloudfront.origin' => 'string',
                        'cloudfront.domain' => 'string',
                        'cloudfront.max_age' => 'int-empty',
                        'cloudfront.invalidation.enabled' => 'bool'
                    ));

                    // invalidation
                    if ($forminput->bool('cloudfront.invalidation.enabled')) {
                        $forminput->type_verify(array(
                            'cloudfront.invalidation.distribution_id' => 'string',
                            'cloudfront.invalidation.api_key' => 'string',
                            'cloudfront.invalidation.api_secret' => 'string',
                            'cloudfront.invalidation.aws_region' => 'string'
                        ));

                        // test API
                        if ($forminput->bool('cloudfront.invalidation.api_test')) {
                            try {
                                $api_OK = $this->cloudfront->test_connection(
                                    $forminput->get('cloudfront.invalidation.distribution_id'),
                                    $forminput->get('cloudfront.invalidation.api_key'),
                                    $forminput->get('cloudfront.invalidation.api_secret'),
                                    $forminput->get('cloudfront.invalidation.aws_region')
                                );
                            } catch (Exception $err) {
                                $api_OK = -1;
                                $forminput->error('cloudfront.invalidation.api_test', 'AWS API connection failed: ' . $err->getMessage());
                            }
                            if ($api_OK !== -1) {
                                if (!$api_OK) {
                                    $forminput->error('cloudfront.invalidation.api_test', 'AWS API connection failed: unknown error');
                                } else {
                                    $this->admin->add_notice('AWS API connection verified.', 'cloudfront', 'SUCCESS');
                                }
                            }
                        }
                    }
                }
            break;
            case "invalidation":

                // verify admin permissions
                if (!$this->admin->is_admin()) {
                    return;
                }

                $invalidations = $forminput->get('invalidations');

                if ($invalidations === '') {
                    $this->admin->add_notice('You did not enter paths to invalidate.', 'cloudfront');
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
                        $this->admin->add_notice('You did not enter paths to invalidate.', 'cloudfront');
                    } else {

                        // send invalidation request
                        try {
                            $result = $this->cloudfront->create_invalidations($invalidations);
                        } catch (Exception $err) {
                            $result = false;
                        }

                        if ($result) {
                            $this->admin->add_notice('Invalidation request <a href="https://console.aws.amazon.com/cloudfront/home#distribution-settings:'.esc_attr($this->options->get('cloudfront.invalidation.distribution_id')).'" target="_blank" rel="noopener" title="'.esc_attr(implode(PHP_EOL, $result['paths'])).'">'.esc_html($result['id']).'</a> submitted.', 'cloudfront');
                        }
                    }
                }

                if ($invalidations) {
                    update_option('o10n_cloudfront_last_invalidation', $invalidations, false);
                }
            break;
        }
    }
}
