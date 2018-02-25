<?php
namespace O10n;

/**
 * CloudFront Page Cache Controller
 *
 * @package    optimization
 * @subpackage optimization/controllers
 * @author     Optimization.Team <info@optimization.team>
 */
if (!defined('ABSPATH')) {
    exit;
}

class Cloudfront extends Controller implements Controller_Interface
{
    private $http_host;
    private $public_host;
    private $origin_host;

    private $cloudfront_pull = false; // Origin pull request?

    private $default_max_age = 604800; // maintain cache for 7 days by default
    private $expire_date; // CloudFront cache expire date (HTTP headers)
    private $expire_age;

    // AWS access credentials
    private $access_key;
    private $access_secret;
    private $distribution_id;
    private $region = 'us-east-1'; // default region

    private $CloudFrontAPI; // API controller

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
            'options',
            'env'
        ));
    }

    /**
     * Setup controller
     */
    protected function setup()
    {
        // verify if page cache is enabled
        if (!$this->options->bool('cloudfront.enabled')) {
            return;
        }

        // configure CloudFront hosts
        $this->public_host = $this->options->get('cloudfront.host');
        $this->origin_host = $this->options->get('cloudfront.origin');

        if (empty($this->public_host) || empty($this->origin_host)) {
            return;
        }

        // invalidation
        if ($this->options->get('cloudfront.invalidation.enabled')) {
            $this->access_key = $this->options->get('cloudfront.invalidation.api_key');
            $this->access_secret = $this->options->get('cloudfront.invalidation.api_secret');
            $this->distribution_id = $this->options->get('cloudfront.invalidation.distribution_id');
            $region = $this->options->get('cloudfront.invalidation.aws_region');
            if ($region) {
                $this->region = $region;
            }
        }

        $protocol = ($this->env->is_ssl()) ? 'https' : 'http';

        // detect hostname
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            $this->http_host = $_SERVER['HTTP_ORIGIN'];
            $http_host_protocol = strpos($this->http_host, '//');
            if ($http_host_protocol !== false) {
                $this->http_host = substr($this->http_host, ($http_host_protocol + 1));
            }
        } else {
            $this->http_host = $_SERVER['HTTP_HOST'];
        }

        // verify if request is for cloudfront domain
        $cf_domain = $this->options->get('cloudfront.domain');
        if (strpos($this->http_host, 'cloudfront.net') !== false || ($cf_domain && strpos($this->http_host, $cf_domain) !== false)) {
            // redirect to public host
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: " . $protocol.'://' . $this->public_host);
            exit;
        }

        // CloudFront origin pull request
        if (isset($_SERVER['HTTP_X_CF_PAGE_CACHE'])) {
            $this->cloudfront_pull = true;
        }

        // modify home url and site url for CloudFront pull requests
        add_filter('home_url', array($this, 'rewrite_url'), $this->first_priority, 1);
        add_filter('site_url', array($this, 'rewrite_url'), $this->first_priority, 1);

        // modify content urls
        add_filter('includes_url', array($this, 'rewrite_url'), $this->first_priority, 1);
        add_filter('content_url', array($this, 'rewrite_url'), $this->first_priority, 1);
        add_filter('plugins_url', array($this, 'rewrite_url'), $this->first_priority, 1);
        add_filter('theme_url', array($this, 'rewrite_url'), $this->first_priority, 1);
        
        // modify admin url for CloudFront pull requests
        add_filter('admin_url', array($this, 'admin_url'), $this->first_priority, 1);

        // modify redirects for CloudFront pull requests
        add_filter('wp_redirect', array($this, 'redirect'), $this->first_priority, 1);
        add_filter('redirect_canonical', array($this, 'redirect_canonical'), $this->first_priority, 1);

        // filter nav menu urls
        add_filter('wp_nav_menu_objects', array($this, 'filter_nav_menu'), 10, 1);
 
        // enable filter on public / origin host
        add_action('init', array($this, 'wp_init'), $this->first_priority);
        add_action('admin_init', array($this, 'wp_init'), $this->first_priority);

        // add HTTP cache headers
        add_action('send_headers', array($this, 'add_cache_headers'), $this->first_priority);
    }
    
    /**
     * WordPress init hook
     */
    final public function wp_init()
    {
        $this->public_host = apply_filters('o10n_cloudfront_public_host', $this->public_host);
        $this->origin_host = apply_filters('o10n_cloudfront_origin_host', $this->origin_host);
    }

    /**
     * WordPress HTTP headers hook
     */
    final public function add_cache_headers()
    {
        // disable for admin and login page
        if (is_admin() || $GLOBALS['pagenow'] === 'wp-login.php') {
            return;
        }

        $default_max_age = $this->options->get('cloudfront.max_age', false);
        if (!$default_max_age || !is_numeric($default_max_age)) {
            $default_max_age = $this->default_max_age;
        }
        $time = time();

        // set expire date based on default cache age
        if (!$this->expire_date || !is_numeric($this->expire_date)) {
            $this->expire_date = ($time + $default_max_age);
        }

        // max age header
        if ($this->expire_age) {
            $age = $this->expire_age;
            $date = (time() + $age);
        } else {
            $age = ($this->expire_date - $time);
            $date = $this->expire_date;
        }

        // no cache headers
        if ($age < 0) {
            header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
        } else {

            // max age header
            header("Cache-Control: public, must-revalidate, max-age=" . $age);
        }

        // expire header
        header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', $date));
    }

    /**
     * Rewrite URL for CloudFront origin pull request
     *
     * @param  string $url URL to filter
     * @return string Filtered URL
     */
    final public function rewrite_url($url)
    {
        $origin_paths = array(
            '/wp-admin',
            '/wp-login.php'
        );

        // apply filters to enable URLs to be rewritten to the origin host
        $origin_paths = apply_filters('o10n_cloudfront_origin_paths', $origin_paths);

        if ($origin_paths && !empty($origin_paths)) {
            foreach ($origin_paths as $path) {

                // rewrite to origin
                if (strpos($url, $path) !== false) {
                    return str_replace('//'.$this->public_host, '//'.$this->origin_host, $url);
                }
            }
        }


        if ($this->cloudfront_pull) {
            return str_replace('//'.$this->origin_host, '//'.$this->public_host, $url);
        }

        return $url;
    }

    /**
     * Rewrite admin URL for CloudFront origin pull request
     *
     * @param  string $url URL to filter
     * @return string Filtered URL
     */
    final public function admin_url($url)
    {
        if ($this->cloudfront_pull) {
            return str_replace('//'.$this->origin_host, '//'.$this->public_host, $url);
        } else {
            return str_replace('//'.$this->public_host, '//'.$this->origin_host, $url);
        }
    }

    /**
     * Modify redirect URL for Amazon CloudFront pull requests
     *
     * @param  string $url URL to filter
     * @return string Filtered URL
     */
    final public function redirect($url)
    {
        return $this->rewrite_url($url);
    }

    /**
     * Disable canonical redirect URL for Amazon CloudFront pull requests
     *
     * @param  string $url URL to filter
     * @return string Filtered URL
     */
    final public function redirect_canonical($url)
    {
        if ($this->cloudfront_pull) {
            return false;
        }

        return $this->rewrite_url($url);
    }

    /**
     * Filter nav menu URLs
     *
     * @param  array $items Menu items
     * @return array Filtered menu objects
     */
    final public function filter_nav_menu($items)
    {
        foreach ($items as $index => $item) {
            if (isset($items[$index]->url)) {
                $items[$index]->url = $this->rewrite_url($items[$index]->url);
            }
        }

        return $items;
    }

    /**
     * Set CloudFront cache age
     */
    final public function set_max_age($age)
    {
        // verify
        if (!$age || !is_numeric($age)) {
            throw new Exception('Age not numeric in O10n\CloudFront::set_max_age()', false, array('persist' => 'expire','max-views' => 3));
        }

        // set expire age
        $this->expire_age = $age;

        // set expire date
        $this->expire_date = (time() + $age);
    }

    /**
     * Set CloudFront cache expire date
     */
    final public function set_expire($timestamp)
    {

        // try to convert string date
        if ($timestamp && !is_numeric($timestamp)) {
            try {
                $timestamp = strtotime($timestamp);
            } catch (\Exception $err) {
                $timestamp = false;
            }
        }

        // verify
        if (!$timestamp) {
            throw new Exception('Invalid timestamp in O10n\CloudFront::set_expire()', false, array('persist' => 'expire','max-views' => 3));
        }

        // set expire date
        $this->expire_date = $timestamp;
        $this->expire_age = ($timestamp - time());
    }


    /**
     * Load API
     */
    final private function load_api($access_key = false, $access_secret = false, $region = false)
    {
        $keys = array('access_key', 'access_secret', 'region');
        foreach ($keys as $key) {
            if (!$$key) {
                $$key = $this->{$key};
            }
            if (empty($$key)) {
                throw new Exception('Failed to load AWS API. Access Credential <code>'.$key.'</code> missing.', false, array('persist' => 'expire','max-views' => 2));
            }
        }
 
        if (!class_exists('\Aws\CloudFront\CloudFrontClient')) {
            // include AWS autoloader
            require_once $this->core->modules('cloudfront')->dir_path() . 'lib/aws-sdk/aws-autoloader.php';
        }

        // load cloudfront
        try {
            $this->CloudFrontAPI = new \Aws\CloudFront\CloudFrontClient(array(
                'version' => 'latest',
                'region' => $region,
                'credentials' => array(
                    'key' => $access_key,
                    'secret' => $access_secret
                )
            ));
        } catch (\Aws\CloudFront\Exception\CloudFrontException $e) {
            throw new Exception($e->getMessage(), false, array('persist' => 'expire','max-views' => 2));
        } catch (\AwsException $e) {
            throw new Exception($e->getMessage(), false, array('persist' => 'expire','max-views' => 2));
        }
    }

    /**
     * Test API connection by listing invalidations
     */
    final public function test_connection($distribution_id = false, $access_key = false, $access_secret = false, $region = false)
    {

        // load API connection
        try {
            $this->load_api($access_key, $access_secret, $region);
        } catch (Exception $err) {
            return false;
        }

        if (!$distribution_id) {
            $distribution_id = $this->distribution_id;
        }

        if (empty($distribution_id)) {
            throw new Exception('Distribution ID missing.', false, array('persist' => 'expire','max-views' => 2));
        }

        try {
            $result = $this->CloudFrontAPI->listInvalidations(array(
                'DistributionId' => $distribution_id
            ));
        } catch (\Aws\CloudFront\Exception\CloudFrontException $e) {
            throw new Exception($e->getMessage(), false, array('persist' => 'expire','max-views' => 2));
        } catch (\AwsException $e) {
            throw new Exception($e->getMessage(), false, array('persist' => 'expire','max-views' => 2));
        }

        return ($result && $result->get('InvalidationList')) ? true : false;
    }

    /**
     * List invalidations
     *
     * @param  string $id Invalidation ID
     * @return array  Invalidation data
     */
    final public function get_invalidation($id)
    {
        if (!$this->options->get('cloudfront.invalidation.enabled')) {
            throw new Exception('CloudFront invalidation is disabled in the settings.', 'cloudfront');
        }

        // load API connection
        $this->load_api();

        try {
            $invalidation_result = $this->CloudFrontAPI->getInvalidation(array(
                'DistributionId' => $this->distribution_id,
                'Id' => $id
            ));
        } catch (\Aws\CloudFront\Exception\CloudFrontException $e) {
            throw new Exception('Failed to get CloudFront invalidation: '. $e->getMessage(), false, array('persist' => 'expire','max-views' => 2));
        } catch (\AwsException $e) {
            throw new Exception('Failed to get CloudFront invalidation: '. $e->getMessage(), false, array('persist' => 'expire','max-views' => 2));
        }

        $invalidation = $invalidation_result->get('Invalidation');

        $result = array();
        $result['id'] = $invalidation['Id'];
        $result['status'] = $invalidation['Status'];

        return $result;
    }

    /**
     * Create invalidations
     *
     * @param mixed $url URI(s) to invalidate
     */
    final public function create_invalidations($urls)
    {
        if (!$this->options->get('cloudfront.invalidation.enabled')) {
            throw new Exception('CloudFront invalidation is disabled in the settings.', 'cloudfront');
        }
        
        // convert single url to array
        if (is_string($urls)) {
            $urls = array($urls);
        }

        // sanitize URLs
        foreach ($urls as $index => $url) {
            if (strpos($url, '//') !== false) {
                $urls[$index] = preg_replace('|^http(s)?:\/\/[^\/]+/|Ui', '/', $url);
            }
        }

        $quantity = count($urls);

        // load API connection
        $this->load_api();

        try {
            // send invalidation request
            $invalidation_result = $this->CloudFrontAPI->createInvalidation(array(
                'DistributionId' => $this->distribution_id,
                'InvalidationBatch' => array(
                    'CallerReference' => $this->callerReference(16),
                    'Paths' => array(
                        'Items' => $urls,
                        'Quantity' => $quantity
                    )
                )
            ));
        } catch (\Aws\CloudFront\Exception\CloudFrontException $e) {
            throw new Exception('CloudFront invalidation failed: '. $e->getMessage(), false, array('persist' => 'expire','max-views' => 2));
        } catch (\AwsException $e) {
            throw new Exception('CloudFront invalidation failed: '. $e->getMessage(), false, array('persist' => 'expire','max-views' => 2));
        }

        $invalidation = $invalidation_result->get('Invalidation');

        $result = array();
        $result['id'] = $invalidation['Id'];
        $result['status'] = $invalidation['Status'];
        $result['date'] = time();
        $result['paths'] = $urls;

        // register invalidation in progress
        if ($result['status'] === 'InProgress') {

            // get invalidations being processed
            $invalidations_in_progress = get_option('o10n_cloudfront_invalidations_in_progress', array());

            // add new invalidation to progress list
            $invalidations_in_progress[] = $result;

            update_option('o10n_cloudfront_invalidations_in_progress', $invalidations_in_progress, false);
        }

        // update invalidation count
        $active_month = date('Ym', current_time('timestamp'));
        $count = get_option('o10n_cloudfront_invalidation_count', array());
        if (!isset($count[$active_month])) {
            $count = array();
            $count[$active_month] = 0;
        }
        $count[$active_month] += $quantity;

        // update settings
        update_option('o10n_cloudfront_invalidation_count', $count, false);

        return $result;
    }

    /**
     * Return random string for callerReference
     *
     * @param  int    $length String length
     * @return string Random string
     */
    final private function callerReference($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
