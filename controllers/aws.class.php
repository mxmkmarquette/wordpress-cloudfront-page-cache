<?php
namespace CloudFrontPageCache;

/**
 * AWS API Controller
 *
 * @package    cloudfront-page-cache
 * @subpackage cloudfront-page-cache/controllers
 * @author     PageSpeed.pro <info@pagespeed.pro>
 */
if (!defined('ABSPATH')) {
    exit;
}


class Aws extends Controller implements Controller_Interface
{

    // access credentials
    private $access_key;
    private $access_secret;
    private $distribution_id;
    private $region;

    // cloudfront API controller
    private $cloudfront;

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
        $this->access_key = $this->options->get('api_key');
        $this->access_secret = $this->options->get('api_secret');
        $this->distribution_id = $this->options->get('distribution_id');
        $this->region = $this->options->get('aws_region');
        if (!$this->region) {
            $this->region = 'us-east-1';
        }
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
                throw new Exception('Failed to load AWS API. AWS Access Credential <code>'.$key.'</code> missing.', false, array('persist' => 'expire','max-views' => 2));
            }
        }
 
        // include AWS autoloader
        require_once CF_FPC_PATH . 'lib/aws-sdk/aws-autoloader.php';

        // load cloudfront
        try {
            $this->cloudfront = new \Aws\CloudFront\CloudFrontClient(array(
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
            $result = $this->cloudfront->listInvalidations(array(
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
        // load API connection
        $this->load_api();

        try {
            $invalidation_result = $this->cloudfront->getInvalidation(array(
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
            $invalidation_result = $this->cloudfront->createInvalidation(array(
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
            $invalidations_in_progress = get_option('cf-page-cache-invalidations-inprogress', array());

            // add new invalidation to progress list
            $invalidations_in_progress[] = $result;

            update_option('cf-page-cache-invalidations-inprogress', $invalidations_in_progress, false);
        }

        // update invalidation count
        $active_month = date('Ym', current_time('timestamp'));
        $count = get_option('cf-page-cache-invalidation-count', array());
        if (!isset($count[$active_month])) {
            $count = array();
            $count[$active_month] = 0;
        }
        $count[$active_month] += $quantity;

        // update settings
        update_option('cf-page-cache-invalidation-count', $count, false);

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
