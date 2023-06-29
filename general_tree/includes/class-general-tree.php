<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    General_Tree
 * @subpackage General_Tree/includes
 * @author     Piotr Wieczorek <piotrwieczorek@wp.eu>
 */
class General_Tree {


	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @access   protected
	 * @var      General_Tree_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @access   protected
	 * @var      string    $general_tree   The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.

	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	public function __construct() {

		if ( defined( 'PUGIN_NAME_VERSION_' ) ) {
			$this->version = PUGIN_NAME_VERSION_;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = PUGIN_NAME_;

		$this->load_dependencies();
		$this->define_admin_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - General_Tree_Loader. Orchestrates the hooks of the plugin.
	 * - General_Tree_Admin. Defines all hooks for the admin area.
	 * - General_Tree_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-general-tree-loader.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */

		//traits
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/admin-includes/trait-my-debug.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/admin-includes/trait-custom-redirect.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/admin-includes/trait-current-user-root-folder.php';
		
		//classes
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-general-tree-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-general-tree-admin-folder.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-general-tree-admin-video.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-general-tree-admin-users.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-general-tree-admin-notices.php';
		
		$this->loader = new General_Tree_Loader();

	}

	

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	
	
	 private function define_admin_hooks() {

		$gt_admin = new General_Tree_Admin( $this->get_plugin_name(), $this->get_version());
		
		$this->loader->add_action( 'init', $gt_admin, 'create_taxonomy' );

		$this->loader->add_action( 'wp_loaded', $gt_admin, 'build_actions' );
		
		$this->loader->add_action( 'admin_enqueue_scripts', $gt_admin, 'enqueue_styles' ); 

		$this->loader->add_action( 'admin_enqueue_scripts', $gt_admin, 'enqueue_scripts' );
		
		$this->loader->add_action( 'admin_menu', $gt_admin, 'add_plugin_admin_menu' );

		$this->loader->add_action( 'admin_menu', $gt_admin, 'remove_menu_items' );

		$this->loader->add_filter('login_redirect', $gt_admin, 'my_login_redirect', 10, 3 );

		$this->loader->add_action( 'wp_before_admin_bar_render', $gt_admin, 'remove_admin_bar_links' );

		//----------------------

		$gt_admin_folder = new General_Tree_Admin_Folder( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_menu', $gt_admin_folder, 'add_plugin_admin_menu' );

		$this->loader->add_action( 'admin_post_gt_form_response',$gt_admin_folder, 'the_form_response');

		//----------------------

		$gt_admin_video = new General_Tree_Admin_Video($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action( 'admin_menu', $gt_admin_video, 'add_plugin_admin_menu' );

		$this->loader->add_action( 'admin_post_make_video_feed', $gt_admin_video, 'make_video_feed');

		$this->loader->add_action( 'wp_ajax_edit_video_content', $gt_admin_video, 'edit_video_content');
		
		$this->loader->add_action( 'admin_post_untrash_videos', $gt_admin_video, 'untrash_videos');
		
		$this->loader->add_action( 'admin_post_trash_videos', $gt_admin_video, 'trash_videos');

		//----------------------

		$gt_admin_notices = new General_Tree_Admin_Notices();

		$this->loader->add_action( 'admin_notices', $gt_admin_notices,'print_plugin_admin_notices');

		//----------------------

		$gt_add_user = new General_Tree_Admin_Add_User($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action( 'admin_menu', $gt_add_user, 'add_plugin_admin_menu' );

		$this->loader->add_action( 'admin_post_add_new_user', $gt_add_user, 'new_user');

		$this->loader->add_action( 'admin_post_delete_user_data', $gt_add_user, 'delete_user_data');

		$this->loader->add_action( 'admin_post_delete_user_and_his_data', $gt_add_user, 'delete_user_and_his_data');

		$this->loader->add_action( 'delete_user', $gt_add_user, 'delete_user_root_folder' );

	}

	
	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress 
	 *
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    General_Tree_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
