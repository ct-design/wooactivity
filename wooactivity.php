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
* admin page
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
    // check user can set options
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // add error/update messages
    // check if the user have submitted the settings
    // wordpress will add the "settings-updated" $_GET parameter to the url
    if (isset($_GET['settings-updated'])) {
    // add settings saved message with the class of "updated"
    add_settings_error('wooactivity_messages', 'wooactivity_message', __('Settings Saved', 'wooactivity'), 'updated');
    }
    
    // show error/update messages
    settings_errors( 'wooactivity_messages' );
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
        <?php
        // output security fields for the registered setting "wooactivity"
        settings_fields('wooactivity');
        // output setting sections and their fields
        // (sections are registered for "wooactivity", each field is registered to a specific section)
        do_settings_sections('wooactivity');
        // output save settings button
        submit_button('Save Settings');
        ?>
        </form>
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
* settings
*/
function wooactivity_settings_init() {
 // register settings
 register_setting( 'wooactivity', 'wooactivity_options' );
 
 // register a new section
 add_settings_section(
 'wooactivity_settings_section',
 __( 'The Matrix has you.', 'wooactivity' ),
 'wooactivity_section_options_cb',
 'wooactivity'
 );
 
 // register a new field in the "wporg_section_developers" section, inside the "wporg" page
 add_settings_field(
 'wooactivity_field_pill', // as of WP 4.6 this value is used only internally
 // use $args' label_for to populate the id inside the callback
 __( 'Pill', 'wooactivity' ),
 'wooactivity_field_pill_cb',
 'wooactivity',
 'wooactivity_settings_section',
 [
 'label_for' => 'wooactivity_field_pill',
 'class' => 'wooactivity_row',
 'wooactivity_custom_data' => 'custom',
 ]
 );
}
add_action( 'admin_init', 'wooactivity_settings_init' );

function wooactivity_section_options_cb( $args ) {
 ?>
 <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Follow the white rabbit.', 'wooactivity' ); ?></p>
 <?php
}
 
// pill field cb
 
// field callbacks can accept an $args parameter, which is an array.
// $args is defined at the add_settings_field() function.
// wordpress has magic interaction with the following keys: label_for, class.
// the "label_for" key value is used for the "for" attribute of the <label>.
// the "class" key value is used for the "class" attribute of the <tr> containing the field.
// you can add custom key value pairs to be used inside your callbacks.
function wooactivity_field_pill_cb( $args ) {
 // get the value of the setting we've registered with register_setting()
 $options = get_option('wooactivity_options');
 // output the field
 ?>
 <select id="<?php echo esc_attr( $args['label_for'] ); ?>"
 data-custom="<?php echo esc_attr( $args['wooactivity_custom_data'] ); ?>"
 name="wooactivity_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
 >
 <option value="red" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'red', false ) ) : ( '' ); ?>>
 <?php esc_html_e( 'red pill', 'wooactivity' ); ?>
 </option>
 <option value="blue" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'blue', false ) ) : ( '' ); ?>>
 <?php esc_html_e( 'blue pill', 'wooactivity' ); ?>
 </option>
 </select>
 <p class="description">
 <?php esc_html_e( 'You take the blue pill and the story ends. You wake in your bed and you believe whatever you want to believe.', 'wooactivity' ); ?>
 </p>
 <p class="description">
 <?php esc_html_e( 'You take the red pill and you stay in Wonderland and I show you how deep the rabbit-hole goes.', 'wooactivity' ); ?>
 </p>
 <?php
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
