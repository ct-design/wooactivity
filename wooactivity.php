<?php
/*
Plugin Name: WooActivity
Plugin URI:  
Description: Monitoring users activity at woocormmerce (no can do baby_doll)
Version:     1.0 beta
Author:      Ct-design
Author URI:  ******
License:     GPL3
License URI: hhttps://www.gnu.org/licenses/gpl.html
*/

//basic check for correct wordpress scope invocation
defined( 'ABSPATH' ) or die( 'No script kiddies please!');

if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  No script kiddies please!';
	exit;
}

//ensure only admin can access
if ( ! is_admin() ) {
     //echo "no can do baby_doll";
     exit;
}

define( 'WOOACTIVITY_VERSION', '1.0' );

//it starts!
register_activation_hook( __FILE__, 'wooactivity_activate' );
function wooactivity_activate() {
    global $wpdb;

    $wooactivity_pref_table_name = wooactivity_get_prefs_table_name();
    if($wpdb->get_var("SHOW TABLES LIKE '$wooactivity_pref_table_name'") != $wooactivity_pref_table_name) {
        wooactivity_initdb($wooactivity_pref_table_name);
    } //else table already exists! consider future updates
}

//it ends!
register_uninstall_hook(__FILE__, 'wooactivity_uninstall');
function wooactivity_uninstall() {//TODO:
}
/*
* logic
*/

function wooactivity_admin_page()
{
    add_menu_page(
        'WooActivity',
        'Woo Activity',
        'manage_options',
        'wooactivity',
        'wooactivity_admin_page_html',
        ''
    );
}
function wooactivity_admin_page_html()
{
    // check user capabilities
    //if (!current_user_can('manage_options')) {
    //    return;
    //}
    ?>
    <div class="wrap">
        <h1><?= esc_html(get_admin_page_title()); ?></h1>
            <?php
            echo 'Administrating WooActivities.......';
            ?>
    </div>
    <?php
}
add_action('admin_menu', 'wooactivity_admin_page');

function wooactivity_initdb($wooactivity_pref_table_name = '') {

    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $wooactivity_pref_table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
    tag tinytext NOT NULL,
    value text NOT NULL,
    PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

/*
* utils
*/
function wooactivity_get_prefs_table_name() {
   global $wpdb;

   $prefs_table_name = $wpdb->prefix . "wooactivity_prefs";
   return $prefs_table_name;
}
?>
