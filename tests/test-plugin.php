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
    /*public function test_max_age()
    {
        // navigate to front page
        $this->go_to('/');

        // enable cloudfront
        O10n\Core::get('options')->save(array(
            'cloudfront.enabled' => true,
            'cloudfront.host' => 'cloudfront-test.com',
            'cloudfront.origin' => 'cloudfront-test.com',
            'cloudfront.domain' => 'cloudfront.net'
        ));

        // max age = 2 hours
        O10n\CloudFront\set_max_age(7200);

        // activate send_headers hook
        do_action('send_headers');

        // get sent headers
        $headers = headers_list();
        print_r($headers);

        $this->assertTrue(in_array('Cache-Control: public, must-revalidate, max-age=7200', $headers));
    }

    // Check that set_max_age sets correct expire header
    public function test_max_age_expire()
    {
        // navigate to front page
        $this->go_to('/');

        // enable cloudfront
        O10n\Core::get('options')->save(array(
            'cloudfront.enabled' => true,
            'cloudfront.host' => 'cloudfront-test.com',
            'cloudfront.origin' => 'cloudfront-test.com',
            'cloudfront.domain' => 'cloudfront.net'
        ));

        // max age = 2 hours
        O10n\CloudFront\set_max_age(7200);

        // activate send_headers hook
        do_action('send_headers');

        // get sent headers
        $headers = headers_list();
        print_r($headers);

        $this->assertTrue(in_array('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', (time() + 7200)), $headers));
    }

    // Check that set_expire sets correct expire header
    public function test_expire()
    {
        // navigate to front page
        $this->go_to('/');

        // enable cloudfront
        O10n\Core::get('options')->save(array(
            'cloudfront.enabled' => true,
            'cloudfront.host' => 'cloudfront-test.com',
            'cloudfront.origin' => 'cloudfront-test.com',
            'cloudfront.domain' => 'cloudfront.net'
        ));

        // expire date to verify
        $age = 10800;
        $date = date('r', (time() + $age));

        // expire in 3 hours
        O10n\CloudFront\set_expire(strtotime($date));

        // activate send_headers hook
        do_action('send_headers');

        // get sent headers
        $headers = headers_list();

        $this->assertTrue(in_array('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', (strtotime($date))), $headers));
    }

    // Check that set_expire sets correct cache control header
    public function test_expire_max_age()
    {
        // navigate to front page
        $this->go_to('/');

        // enable cloudfront
        O10n\Core::get('options')->save(array(
            'cloudfront.enabled' => true,
            'cloudfront.host' => 'cloudfront-test.com',
            'cloudfront.origin' => 'cloudfront-test.com',
            'cloudfront.domain' => 'cloudfront.net'
        ));

        // expire date to verify
        $age = 10800;
        $date = date('r', (time() + $age));

        // expire in 3 hours
        O10n\CloudFront\set_expire(strtotime($date));

        // activate send_headers hook
        do_action('send_headers');

        // get sent headers
        $headers = headers_list();

        $this->assertTrue(in_array('Cache-Control: public, must-revalidate, max-age=' . $age, $headers));
    }

    // Check that nocache sets correct nocache
    public function test_nocache()
    {
        // navigate to front page
        $this->go_to('/');

        // enable cloudfront
        O10n\Core::get('options')->save(array(
            'cloudfront.enabled' => true,
            'cloudfront.host' => 'cloudfront-test.com',
            'cloudfront.origin' => 'cloudfront-test.com',
            'cloudfront.domain' => 'cloudfront.net'
        ));

        // expire in 3 hours
        O10n\CloudFront\nocache();

        // activate send_headers hook
        do_action('send_headers');

        // get sent headers
        $headers = headers_list();
        print_r($headers);

        $this->assertTrue(
            in_array('Cache-Control: no-store, no-cache, must-revalidate, max-age=0', $headers)
            && in_array('Cache-Control: post-check=0, pre-check=0', $headers)
            && in_array('Pragma: no-cache', $headers)
        );
    }*/
}
