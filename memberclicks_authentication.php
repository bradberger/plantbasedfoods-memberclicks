<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Plant Based Foods MemberClicks Member Registry
 * Description:       Provides member directory functionality via MemberClicks
 * Version:           1.0.1
 * Author:            Brad Berger
 * Author URI:        https://bradb.net
 * License:
 * License URI:
 * Text Domain:       plantbasedfoods-memberclicks
*/

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

include_once __DIR__ . '/memberclicks.php';
include_once __DIR__ . '/memberclicks-cache.php';

// memberclicks_check_cached is run on each page load to ensure member data is up-to-date
function memberclicks_check_cache() {
    memberclicks_refresh_data();
}

// memberclicks_refresh_data forces a refresh of member data at least every day
function memberclicks_refresh_data($force = false) {

    $key = 'memberclicks_data_timestamp';
    $lastRefreshed = get_option($key);
    if ($force || !$lastRefreshed || (int) $lastRefreshed < strtotime('today', time())) {

        // Update the value now to ensure that parallel requests don't take too long.
        update_option($key, (string) time());

        $mc = new MemberClicks(
            get_option('memberclicks_org_id'),
            get_option('memberclicks_client_id'),
            get_option('memberclicks_client_secret')
        );
        $mc->auth();

        // Cache
        $cache = new MemberClicksCache();
        $cache->clear();

        // Get/write profiles
        list($code, $profiles) = $mc->profiles();
        if ($code === 200) {
            file_put_contents(plugin_dir_path(__FILE__)."members.js", sprintf("window.members = JSON.parse('%s')", str_replace("'", "\'", json_encode($profiles))));
        }
    }
}

/**
 * top level menu
 */
function memberclicks_auth_options_page() {
    // add top level menu page
    add_menu_page(
        'Memberclicks API Authentication',
        'Memberclicks',
        'manage_options',
        'plantbasedfoods-memberclicks',
        'memberclicks_auth_options_html',
        '',
        null
    );
}

function memberclicks_auth_options_html() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if (isset( $_GET['settings-updated'])) {
        add_settings_error( 'wporg_messages', 'wporg_message', __( 'Settings Saved', 'wporg' ), 'updated' );
    }

	if (isset($_POST['orgid'])) {
		update_option('memberclicks_org_id', $_POST['orgid']);
	}

    if (isset($_POST['clientid'])) {
        update_option('memberclicks_client_id', $_POST['clientid']);
    }

    if (isset($_POST['clientsecret'])) {
        update_option('memberclicks_client_secret', $_POST['clientsecret']);
    }

    if (!empty($_POST['refreshcache'])) {
        memberclicks_refresh_data(true);
    }

    ?>
    <div class="wrap">
    <h1>MemberClicks Directory and Settings</h1>
    <div class="layout-row">
        <section ng-app="MemberDirectory" ng-cloak flex>
			<select ng-model="view">
				<option value="">Full Member Directory</option>
				<option value="company">Company Directory</option>
				<option value="company-affiliate">Company Affiliate Directory</option>
				<option value="individual-affiliate">Individual Affiliate Directory</option>
			</select>
			<div ng-switch="view">
				<div ng-switch-when="company">
					<h3>Company Members</h3>
	    			<company-directory searchable="true" member-type="'Full Member'"></company-directory>
				</div>
				<div ng-switch-when="company-affiliate">
					<h3>Company Affiliates</h3>
	    			<company-affiliate-directory searchable="true" member-type="'Organization Affiliate'"></company-affiliate-directory>
				</div>
				<div ng-switch-when="individual-affiliate">
					<h3>Individual Affiliates</h3>
					<individual-affiliate-directory searchable="true" member-type="'Individual Affiliate'"></individual-affiliate-directory>
				</div>
				<div ng-switch-default>
	                <h3>Member Directory</h3>
	                <member-directory searchable="true" member-type="" organization=""></member-directory>
				</div>
			</div>
        </section>
        <div flex>
            <h3>Settings</h3>
            <form action="<?php echo $_SERVER['SCRIPT_URI']; ?>" method="POST" class="memberclicks-auth">
                <ul>
                    <li>
        				<label for="orgid">Org ID</label>
        				<input type="text" name="orgid" value="<?php echo get_option('memberclicks_org_id'); ?>">
        			</li>
                    <li>
                        <label for="clientid">Client ID</label>
                        <input type="text" name="clientid" value="<?php echo get_option('memberclicks_client_id'); ?>">
                    </li>
                    <li>
                        <label for="clientsecret">Client Secret</label>
                        <input type="text" name="clientsecret" value="<?php echo get_option('memberclicks_client_secret'); ?>">
                    </li>
                    <li>
                        <label for="refreshcache">Refresh Cache</label>
                        <input type="checkbox" name="refreshcache" value="1">
                    </li>
                </ul>
                <div>
                    <?php submit_button(); ?>
                </div>
            </form>
        </div>
    </div>
    </div>
    <?php
}

function company_directory_shortcode() {
    return '<section ng-app="MemberDirectory"><company-directory searchable="true" member-type="\'Full Member\'"></company-directory></section>';
}

function affiliate_directory_shortcode() {
    return '<section ng-app="MemberDirectory">
        <h2>Company Affiliates</h2>
        <company-affiliate-directory searchable="true" member-type="\'Organization Affiliate\'"></company-affiliate-directory>
        <h2>Individual Affiliates</h2>
        <individual-affiliate-directory searchable="true" member-type="\'Individual Affiliate\'"></individual-affiliate-directory>
    </section>';
}

function memberclicks_enqueue_scripts() {
	wp_enqueue_script('angular', 'https://ajax.googleapis.com/ajax/libs/angularjs/1.5.7/angular.min.js', null, '1.5.7', true);
	wp_enqueue_script('member_directory', plugin_dir_url( __FILE__ ).'members.js', array('angular'), time(), true);
	wp_enqueue_script('member_data', plugin_dir_url( __FILE__ ).'app.js', array(), time(), true);
	wp_enqueue_style('member_directory', plugin_dir_url(__FILE__).'app.css', array(), time(), 'all');
}

add_action('admin_menu', 'memberclicks_auth_options_page');
add_action('admin_enqueue_scripts', 'memberclicks_enqueue_scripts');
add_action('wp_enqueue_scripts', 'memberclicks_enqueue_scripts');
add_action('wp', 'memberclicks_check_cache');

add_shortcode('company_directory', 'company_directory_shortcode');
add_shortcode('affiliate_directory', 'affiliate_directory_shortcode');
