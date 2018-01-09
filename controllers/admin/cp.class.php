<?php
namespace CloudFrontPageCache;

/**
 * Admin Control-Panel Controller
 *
 * @package    cloudfront-page-cache
 * @subpackage cloudfront-page-cache/controllers/admin
 * @author     PageSpeed.pro <info@pagespeed.pro>
 */
if (!defined('ABSPATH')) {
    exit;
}

class AdminCP extends Controller implements Controller_Interface
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
            'error',
            'aws'
        ));
    }

    /**
     * Setup controller
     */
    protected function setup()
    {

        // set admin flag
        define('CF_FPC_ADMIN', true);

        // action links
        add_action('admin_post_optimization_update', array($this,  'update_settings'));
        add_action('admin_post_optimization_clear_store', array($this,  'clear_store'));

        // error notices
        add_action('cf-page-cache-notices', array($this, 'show_notices'));
    }

    /**
     * Show admin notices
     */
    public function show_notices()
    {
        // verify admin permissions
        if (!current_user_can('manage_options')) {
            return;
        }

        // get notices
        $notices = $this->error->get_notices();

        $persisted_notices = array();
        $noticerows = array();
        foreach ($notices as $notice) {
            switch ($notice['type']) {
                case "ERROR":
                    $notice_class = 'error';
                break;
                case "SUCCESS":
                    $notice_class = 'updated';
                break;
                default:
                    $notice_class = 'notice';
                break;
            }

            $notice['flap-title'] = 'Test';
            if (date('Y/m/d', time()) === date('Y/m/d', $notice['date'])) {
                $datetext = date('H:i', $notice['date']);
            } else {
                $datetext = date_i18n(get_option('date_format'), $notice['date']);
            }

            $count = false;
            $loglink = '#';
            if (isset($notice['count']) && $notice['count'] > 1) {
                $count = ' (<a href="'.$loglink.'" class="log">'.$notice['count'].' errors</a>)';
            } else {
                $count = ' (<a href="'.$loglink.'" class="log">log</a>)';
            }

            $noticetext = '<div class="inline notice '.$notice_class.' is-dismissible" rel="' . esc_attr($notice['hash']) . '">';

            $noticetext .= '<div class="notice-text">
                <p>
                    '.__($notice['text'], 'cloudfront-page-cache').'
                </p>';

            $noticetext .= '</div>
            </div>';

            $noticerows[] = $noticetext;
                
            // register notice views
            if (!isset($notice['views'])) {
                $notice['views'] = 0;
            }
            $notice['views']++;

            // persist notice
            if (isset($notice['persist'])) {
                $expired = false;

                switch ($notice['persist']) {
                    case "views":
                        $viewcount = (isset($notice['max-views']) && is_numeric($notice['max-views'])) ? $notice['max-views'] : 3;
                        if ($notice['views'] > $viewcount) {
                            $expired = true;
                        }
                    break;
                    case "expire":

                        // specific expire date
                        if (isset($notice['expire_date'])) {
                            if (time() > $notice['expire_date']) {

                                // expired
                                $expired = true;
                            }
                        } else {
                            $maxage = (isset($notice['max-age']) && is_numeric($notice['max-age'])) ? $notice['max-age'] : 5 * 60;
                            $viewcount = (isset($notice['max-views']) && is_numeric($notice['max-views'])) ? $notice['max-views'] : 3;

                            // expire when viewed more than 5 times and older than 5 minutes
                            if ((isset($notice['date']) && $notice['date'] < ((time() - $maxage))) || $notice['views'] > $viewcount) {

                                // expired
                                $expired = true;
                            }
                        }
                    break;
                }

                // notice has expired
                if ($expired) {
                    continue 1;
                }

                $persisted_notices[] = $notice;
            }
        } ?>
<div><?php print implode('', $noticerows); ?></div>
<?php

        update_option('cf-page-cache-notices', $persisted_notices, false);
    }
}
