<?php

class PluginTest extends WP_UnitTestCase
{

  // Check that that activation doesn't break
    public function test_plugin_activated()
    {
        $this->assertTrue(is_plugin_active(PLUGIN_PATH));
    }

    // Check that public methods are available
    public function test_public_methods()
    {
        $this->assertTrue(
            function_exists('O10n\CloudFront\set_max_age')
            && function_exists('O10n\CloudFront\set_expire')
            && function_exists('O10n\CloudFront\nocache')
        );
    }

    // Check that set_max_age sets correct cache control header
    public function test_max_age()
    {
        // max age = 2 hours
        O10n\CloudFront\set_max_age(7200);

        // activate send_headers hook
        do_action('send_headers');

        // get sent headers
        $headers = headers_list();

        $this->assertTrue(in_array('Cache-Control: public, must-revalidate, max-age=7200', $headers));
    }

    // Check that set_max_age sets correct expire header
    public function test_max_age_expire()
    {
        // max age = 2 hours
        O10n\CloudFront\set_max_age(7200);

        // activate send_headers hook
        do_action('send_headers');

        // get sent headers
        $headers = headers_list();
    }

    // Check that set_max_age sets correct cache control header
    public function test_expire()
    {
        // expire date to verify
        $age = 10800;
        $date = date('r', (time() + $age));

        // max age = 2 hours
        O10n\CloudFront\set_expire(strtotime($date));

        // activate send_headers hook
        do_action('send_headers');

        // get sent headers
        $headers = headers_list();

        $this->assertTrue(in_array('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', (strtotime($date))), $headers));
    }

    // Check that set_max_age sets correct expire header
    public function test_expire_max_age()
    {
        // expire date to verify
        $age = 10800;
        $date = date('r', (time() + $age));
        
        // max age = 2 hours
        O10n\CloudFront\set_expire(strtotime($date));

        // activate send_headers hook
        do_action('send_headers');

        // get sent headers
        $headers = headers_list();

        $this->assertTrue(in_array('Cache-Control: public, must-revalidate, max-age=' . $age, $headers));
    }
}
