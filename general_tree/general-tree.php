<?php
/**
 
 * @since             1.0.0
 * @package           General_Tree
 *
 * @wordpress-plugin
 * Plugin Name:       YT-folders plugin
 * Version:           1.0.0
 * Author:            Piotr Wieczorek
 * Text Domain:       general-tree
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


define('PUGIN_NAME_VERSION_', '1.0.0' );
define('PUGIN_NAME_', 'general-tree' );
define("PLUGIN_DIR", plugin_dir_path( __FILE__ ));

define("TAXONOMY_", "folders");
define("DEFAULT_THUMBNAIL", "black.jpg");
define("GOOGLE_API_KEY", "AIzaSyBL3uXweBVteL5UlLKipIegw8VPavkW_Q0");


/**
 * The code that runs during plugin activation.
 */
function activate_general_tree() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-general-tree-activator.php';
	add_action( 'admin_init', General_Tree_Activator::activate());
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_general_tree() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-general-tree-deactivator.php';
	General_Tree_Deactivator::deactivate();
}

//register_activation_hook( __FILE__, 'activate_general_tree' );
register_deactivation_hook( __FILE__, 'deactivate_general_tree' );

/**
 * The core plugin class that is used to admin-specific hooks
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-general-tree.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 */
function run_general_tree() {

	$plugin = new General_Tree();
	$plugin->run();

}

run_general_tree();


