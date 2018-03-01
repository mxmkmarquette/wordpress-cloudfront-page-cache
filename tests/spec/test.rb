#!/usr/bin/env ruby

###
# This file hands over integration tests for rspec.
# It needs wp-cli for integrating with wordpress
###

require 'capybara/poltergeist'
require 'rspec'
require 'rspec/retry'
require 'capybara/rspec'
require 'uri' # parse the url from wp-cli

# Load our default RSPEC MATCHERS
require_relative 'lib/matchers.rb'

RSpec.configure do |config|
  config.include Capybara::DSL
  config.verbose_retry = true
  config.default_retry_count = 1
end

Capybara.configure do |config|
  config.javascript_driver = :poltergeist
  config.default_driver = :poltergeist # Tests can be more faster with rack::test.
end
 
Capybara.register_driver :poltergeist do |app|
  Capybara::Poltergeist::Driver.new(app, 
    debug: false,
    js_errors: false, # Use true if you are really careful about your site
    phantomjs_logger: '/dev/null', 
    timeout: 60,
    :phantomjs_options => [
       '--webdriver-logfile=/dev/null',
       '--load-images=no',
       '--debug=no', 
       '--ignore-ssl-errors=yes', 
       '--ssl-protocol=TLSv1'
    ],
    window_size: [1920,1080] 
   )
end

target_url = ENV['WP_TEST_URL']

uri = URI(target_url)

username = ENV['WP_TEST_USER']
password = ENV['WP_TEST_USER_PASS']

puts "testing #{target_url}..."
### Begin tests ###
describe "wordpress: #{uri.scheme}://#{uri.host}:#{uri.port}#{uri.path}/ - ", :type => :request, :js => true do 

  subject { page }

  describe "frontpage" do

    before do
      visit "#{uri.scheme}://#{uri.host}:#{uri.port}#{uri.path}/"
    end

    it "Healthy status code 200" do
      expect(page).to have_status_of [200]
    end

    it "Page includes stylesheets" do
      expect(page).to have_css
    end

    ### Add customised business critical frontend tests here #####
    
  end

  describe "admin-panel" do

    before do
      #Our sites always have https on
      visit "#{uri.scheme}://#{uri.host}:#{uri.port}#{uri.path}/wp-login.php"
    end

    it "There's a login form" do
      expect(page).to have_id "wp-submit"
    end

    it "Logged in to WordPress Dashboard" do
      within("#loginform") do
        fill_in 'log', :with => username
        fill_in 'pwd', :with => password
      end
      click_button 'wp-submit'
      # Should obtain cookies and be able to visit /wp-admin
      expect(page).to have_id "wpadminbar"
    end
  end

  describe "cloudfront-settings" do

    before do
      #Our sites always have https on
      visit "#{uri.scheme}://#{uri.host}:#{uri.port}#{uri.path}/wp-admin/options-general.php?page=o10n-cloudfront&tab=settings"
    end

    it "There's a CloudFront settings form" do
      expect(page).to have_selector("input[name='o10n[cloudfront.enabled]']")
    end

    it "Saved CloudFront settings" do
      within(".json-form") do

        # enable plugin
        find("input[name='o10n[cloudfront.enabled]']").click

        fill_in "input[name='o10n[cloudfront.host]']", with: "localhost"
        fill_in "input[name='o10n[cloudfront.origin]']", with: "origin.localhost"
        fill_in "input[name='o10n[cloudfront.domain]']", with: "xxx.cloudfront.net"
        fill_in "input[name='o10n[cloudfront.max_age]']", with: "7200"
      end
      click_button 'is_submit'

      # Should obtain cookies and be able to visit /wp-admin
      expect(page).to have_content("Settings saved.")
    end

  end

  describe "cloudfront-cache-headers" do
    before do
      #Our sites always have https on
      visit "#{uri.scheme}://#{uri.host}:#{uri.port}#{uri.path}/"
    end

    it "CloudFront sends 7200 second cache headers on frontend" do
      
      # Should obtain cookies and be able to visit /wp-admin
      expect(page.driver.browser.last_response["Cache-Control"]).to eq "public, must-revalidate, max-age=7200"

    end

  end
 
end

# Check if command exists
def command?(name)
  `which #{name}`
  $?.success?
end