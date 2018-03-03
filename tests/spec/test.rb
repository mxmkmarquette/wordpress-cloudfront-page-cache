#!/usr/bin/env ruby

###
# This file hands over integration tests for rspec.
# It needs wp-cli for integrating with wordpress
###

require 'capybara/poltergeist'
require 'rspec'
require 'rspec/retry'
require 'capybara/rspec'
#require 'capybara-screenshot/rspec'

 #
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

cf_domain = ENV['CLOUDFRONT_DOMAIN']
cf_dist = ENV['CLOUDFRONT_DIST']
cf_access_key = ENV['CLOUDFRONT_ACCESS_KEY']
cf_secret = ENV['CLOUDFRONT_SECRET']

#session = Capybara::Session.new(:poltergeist)


puts "testing #{target_url}..."
### Begin tests ###
describe "wordpress: #{uri}/ - ", :type => :request, :js => true do 

  subject { page }

  describe "frontpage" do

    before do
      visit "#{uri}/"
    end

    it "Healthy status code 200" do
      expect(page).to have_status_of [200]
    end

    it "Page includes stylesheets" do
      expect(page).to have_css
    end
    
  end

  describe "admin-panel" do

    before do
      visit "#{uri}/wp-login.php"
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

      expect(page).to have_id "wpadminbar"
    end
  end

  describe "cloudfront-settings" do

    before do
      visit "#{uri}/wp-admin/options-general.php?page=o10n-cloudfront&tab=settings"
    end

    it "Logged in to WordPress Dashboard" do
      within("#loginform") do
        fill_in 'log', :with => username
        fill_in 'pwd', :with => password
      end
      click_button 'wp-submit'
      
      expect(page).to have_selector("input[name='o10n[cloudfront.enabled]']")
    
      within("#poststuff") do 

        # enable cloudfront page cache
        find("input[name='o10n[cloudfront.enabled]']").set(true)

        fill_in "o10n[cloudfront.host]", with: "www.e-scooter.co"
        fill_in "o10n[cloudfront.origin]", with: "e-scooter.co"
        fill_in "o10n[cloudfront.domain]", :with => cf_domain
        fill_in "o10n[cloudfront.max_age]", with: "7200"

        # enable cloudfront invalidation
        find("input[name='o10n[cloudfront.invalidation.enabled]']").set(true)

        fill_in "o10n[cloudfront.invalidation.distribution_id]",:with => cf_dist
        fill_in "o10n[cloudfront.invalidation.api_key]", :with => cf_access_key
        fill_in "o10n[cloudfront.invalidation.api_secret]", :with => cf_secret

      end
      
      click_button 'is_submit'

      expect(page).to have_content("Settings saved.")

      within("#poststuff") do 

        # enable cloudfront page cache
        find("input[name='o10n[cloudfront.invalidation.api_test]']").set(true)

      end
      
      click_button 'is_submit'

      expect(page).to have_content("AWS API connection verified.")
    end

  end

  describe "cloudfront-invalidation" do

    before do
      visit "#{uri}/wp-admin/options-general.php?page=o10n-cloudfront&tab=invalidation"
    end

    it "Logged in to WordPress Dashboard" do
      within("#loginform") do
        fill_in 'log', :with => username
        fill_in 'pwd', :with => password
      end
      click_button 'wp-submit'
      
      expect(page).to have_selector("textarea[name='o10n[invalidations]']")
    
      within("#poststuff") do 

        # enable cloudfront page 
        fill_in "o10n[invalidations]", with: "/xxx/"

      end
      
      click_button 'is_submit'

      expect(page).to have_content(/Invalidation request.*submitted/i)

    end

  end
 
end

# Check if command exists
def command?(name)
  `which #{name}`
  $?.success?
end