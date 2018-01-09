<?php
namespace CloudFrontPageCache;

/**
 * Install Controller
 *
 * @package    cloudfront-page-cache
 * @subpackage cloudfront-page-cache/controllers
 * @author     PageSpeed.pro <info@pagespeed.pro>
 */
if (!defined('ABSPATH')) {
    exit;
}

class Install extends Controller implements Controller_Interface
{
    private $current_version; // current plugin version

    /**
     * Load controller
     *
     * @param  Core       $Core Core controller instance.
     * @return Controller Controller instance.
     */
    public static function &load(Core &$Core)
    {
        return parent::construct($Core, array(
            'options'
        ));
    }

    /**
     * Setup controller
     */
    protected function setup()
    {
        if (!defined('CF_FPC_VERSION')) {
            throw new Exception('Installation error. Constant CF_FPC_VERSION missing.', 'core');
        }

        // admin panel
        if (!is_admin()) {
            return;
        }

        // set current version
        $this->current_version = get_option('cf-page-cache-version', false);

        // register activate / deactivate hooks.
        register_activation_hook(CF_FPC_SELF, array( $this, 'activate' ));
        register_deactivation_hook(CF_FPC_SELF, array( $this, 'deactivate' ));

        // upgrade/install hooks
        add_action('plugins_loaded', array($this, 'upgrade'), 10);
    }

    /**
     * Activate plugin hook
     */
    final public function activate()
    {
    }

    /**
     * Deactivate plugin hook
     */
    final public function deactivate()
    {
    }

    /**
     * Upgrade plugin
     */
    final public function upgrade()
    {

        // admin panel
        if (!is_admin()) {
            return;
        }

        // new installation
        if (!$this->current_version) {
            return $this->install();
        }

        // upgrade
        if (CF_FPC_VERSION !== $this->current_version) {

            // @todo for future versions
        }
    }

    /**
     * Install plugin
     */
    final protected function install()
    {
        // set version option
        update_option('cf-page-cache-version', CF_FPC_VERSION, false);
        $this->current_version = CF_FPC_VERSION;
    }
}
