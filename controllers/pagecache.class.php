<?php
namespace CloudFrontPageCache;

/**
 * CloudFront Page Cache Controller
 *
 * @package    cloudfront-page-cache
 * @subpackage cloudfront-page-cache/controllers
 * @author     PageSpeed.pro <info@pagespeed.pro>
 */
if (!defined('ABSPATH')) {
    exit;
}

class Pagecache extends Controller implements Controller_Interface
{
    private $http_host;
    private $public_host;
    private $origin_host;

    private $cloudfront_pull = false;

    private $default_cache_age = 604800; // maintain cache for 7 days by default
    private $expire_date; // CloudFront cache expire date (HTTP headers)

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
        // verify enabled state
        if (!$this->options->bool('enabled')) {
            return;
        }

        // configure CloudFront hosts
        $this->public_host = $this->options->get('host');
        $this->origin_host = $this->options->get('origin');

        if (empty($this->public_host) || empty($this->origin_host)) {
            return;
        }

        $is_ssl = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443);
        $protocol = ($is_ssl) ? 'https' : 'http';

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

        // verify cloudfront domain
        $cf_domain = $this->options->get('domain');
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
    }

    /**
     * WordPress init hook
     */
    final public function wp_init()
    {
        $this->public_host = apply_filters('cfpc-public-host', $this->public_host);
        $this->origin_host = apply_filters('cfpc-origin-host', $this->origin_host);
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
        $origin_paths = apply_filters('cfpc-origin-hosts-filter', $origin_paths);

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
    final public static function set_age($age)
    {
    }

    /**
     * Set CloudFront cache expire date
     */
    final public static function set_expire($timestamp)
    {
    }
}
