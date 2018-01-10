<?php
namespace CloudFrontPageCache;

/**
 * Error Controller
 *
 * @package    cloudfront-page-cache
 * @subpackage cloudfront-page-cache/controllers
 * @author     PageSpeed.pro <info@pagespeed.pro>
 */
if (!defined('ABSPATH')) {
    exit;
}


/**
 * Exception Controller
 */
final class Exception extends \Exception
{
    protected $category = 'unknown';    // error category
    protected $admin_notice = -1;       // display notice in admin
    protected $fatal = false;           // fatal error
    
    /**
     * Construct the exception
     * @param string  $message  The error message.
     * @param string  $category The errpr category.
     * @param boolean $fatal    Is fatal error.
     * @param integer $admin    Display admin panel notice.
     */
    public function __construct($message, $fatal = false, $admin = -1)
    {
        // process exception
        parent::__construct($message, 1);

        $this->admin = $admin; // display admin notice
        $this->fatal = $fatal; // error is fatal
        
        if (!class_exists('CloudFrontPageCache\\Core')) {
            wp_die(__('Failed to load CloudFront Page Cache. Please contact the administrator of this website.', 'cloudfront-page-cache'));
        }

        // forward exception to error handler
        Core::forward_exception($this);
    }

    // display admin notice
    public function isAdminNotice()
    {
        return ($this->admin_notice === false) ? false : $this->admin_notice;
    }

    // fatal error
    public function isFatal()
    {
        return ($this->fatal) ? true : false;
    }
}

/**
 * Error Controller
 */
class Error extends Controller implements Controller_Interface
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
        return parent::construct($Core, array());
    }

    /**
     * Setup controller
     */
    protected function setup()
    {
    }

    /**
     * Handle error exception
     */
    final public function handle(Exception $error)
    {
        // display admin notice?
        $admin_notice = $error->isAdminNotice();

        // admin notice
        if ($admin_notice) {
            if (!is_array($admin_notice)) {
                $admin_notice = array();
            }
            $this->add_notice($error->getMessage(), 'ERROR', $admin_notice);
        }

        if ($error->isFatal()) {
            $this->fatal($error);
        }
    }

    /**
     * Get admin error notices
     */
    final public function get_notices()
    {
        return get_option('cf-page-cache-notices', array());
    }

    /**
     * Add admin error notice
     */
    final public function add_notice($message, $type = 'ERROR', $options = array())
    {
        // get notices
        $notices = $this->get_notices();

        // notice data
        $notice = array();
        $notice['hash'] = md5($message);
        $notice['text'] = $message;
        $notice['type'] = $type;
        $notice['date'] = time();

        $notice = array_merge($notice, $options);

        // verify if notice exists
        $updated_notices = array();
        foreach ($notices as $key => $item) {

            // notice exist, merge and push to front
            if (isset($item['hash']) && $item['hash'] === $notice['hash']) {
                $notice = array_merge($item, $notice);
                continue 1;
            }
            $updated_notices[] = $item;
        }

        // add stack trace for plugin development
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $notice['trace'] = json_encode(debug_backtrace(), JSON_PRETTY_PRINT);
        }

        // push to front
        array_unshift($updated_notices, $notice);

        // sort by date
        usort($updated_notices, function ($a1, $a2) {
            return $a2['date'] - $a1['date'];
        });

        // limit amount of stored notices
        if (count($updated_notices) > 10) {
            $updated_notices = array_slice($updated_notices, -10, 10);
        }

        // save notices
        update_option('cf-page-cache-notices', $updated_notices, false);
    }

    /**
     * Print fatal error
     *
     * @todo
     *
     * @param mixed $error Error to display.
     */
    final public function fatal($error)
    {
        // clear output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }

        // output SEO friendly header (temporary error)
        if (!headers_sent()) {
            header(($_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.1' ? 'HTTP/1.1' : 'HTTP/1.0') . ' 503 Service Temporarily Unavailable', true, 503);
            header('Retry-After: 60');
        }

        // Exception
        if (is_a($error, 'CloudFrontPageCache\\Exception')) {
            $error = $error->getMessage();
        } elseif (!is_string($error)) {
            $error = substr(var_export($error, true), 0, 200);
        }

        wp_die('<h1>Fatal Error in CloudFront Page Cache plugin</h1>' . $error);
    }
}
