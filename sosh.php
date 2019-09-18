<?php
/**
 * Plugin Name: Social Share
 * Description: Social share buttons plugin for <strong>share post to social network</strong>.
 * Version: 1.0.1
 * Requires PHP: 5.4.0
 * Author: Arthur AGOSSOU
 *
 */
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

//session_unset();
define('_PLUGIN_DIR', plugin_dir_path(__FILE__) );
define('_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
define('SOSH_OPTIONS_NAME', 'social_share_options_arth' );

if (file_exists(_PLUGIN_DIR . '/inc/inc.php'))
    include_once (_PLUGIN_DIR . '/inc/inc.php');

if (file_exists(_PLUGIN_DIR . 'sosh.class.php'))
    include_once( _PLUGIN_DIR . 'sosh.class.php' );
new Social_share();

register_activation_hook(__FILE__, ['Social_share','social_share_install']);