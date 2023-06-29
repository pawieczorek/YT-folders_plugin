<?php



class General_Tree_Admin {

	use my__debug;
	use current_user_root_folder;

	private $plugin_name;
	private $version;


	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/general-tree-admin.css', array(), $this->version, 'all' );

		wp_enqueue_style( 'YTfolders-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css' );
	}

	public function enqueue_scripts( $hook_suffix ) {

		// Backbone JavaScript Client
		wp_enqueue_script( 'wp-api' );

		if ( $hook_suffix == 'toplevel_page_general-tree-folder' ) {

			$root_folder_id = $this->current_user_root_folder_id();

			if ( count( get_term_children( $root_folder_id, TAXONOMY_ ) ) === 0 ) {

				$root_folder_has_subfolders = false;
			} else {
				$root_folder_has_subfolders = true;
			}

			$my_params = array(
				'root_folder_id'             => $root_folder_id,
				'root_folder_has_subfolders' => $root_folder_has_subfolders,
				'spinner_url'                => get_admin_url() . 'images/spinner.gif',
			);

			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/general-tree-folder.js', array( 'jquery' ), $this->version, false );
			wp_add_inline_script( $this->plugin_name, 'var my_params = ' . wp_json_encode( $my_params ), 'before' );

			wp_enqueue_script( 'wp-api' );

		}

		if ( $hook_suffix == 'yt-folders_page_general-tree-video' ) {

			$my_params = array(
				'admin_url' => admin_url( 'admin.php' ),
			);

			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/general-tree-video.js', array( 'jquery' ), $this->version, false );
			wp_add_inline_script( $this->plugin_name, 'var my_params = ' . wp_json_encode( $my_params ), 'before' );
		}

		if ( $hook_suffix == 'admin_page_general-tree-video-edit-content' ) {

			$params = array(
				'adminurl' => admin_url( 'admin.php' )
			);

			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/general-tree-video-edit-content.js', array( 'jquery' ), $this->version, false );
			wp_add_inline_script( $this->plugin_name, 'let  params = ' . wp_json_encode( $params ), 'before' );
		}

		if ( $hook_suffix == 'admin_page_general-tree-videos' ) {

			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/general-tree-videos.js', array( 'jquery' ), $this->version, false );
			
		}

	}

	public function create_taxonomy() {

		//creates taxonomy if it does not exists
		 if ( !taxonomy_exists( TAXONOMY_ ) ) {
		
			$labels = array(
				'name'              => _x( 'Folders ', 'taxonomy general name' ),
				'singular_name'     => _x( 'Folder', 'taxonomy singular name' ),
				'search_items'      => __( 'Search Folders ' ),
				'all_items'         => __( 'All Folders ' ),
				'parent_item'       => __( 'Parent Folder' ),
				'parent_item_colon' => __( 'Parent Folder:' ),
				'edit_item'         => __( 'Edit Folder' ),
				'update_item'       => __( 'Update Folder' ),
				'add_new_item'      => __( 'Add New Folder' ),
				'new_item_name'     => __( 'New Folder Name' ),
				'menu_name'         => __( 'Folders ' ),
			);

			$args   = array(
				'hierarchical'      => true, 
				'labels'            => $labels,
				'show_ui'           => true,
				'show_in_nav_menus' => false,
				'query_var'         => true,
				'rewrite'           => array( 'hierarchical' => true )
			);
			$result = register_taxonomy( TAXONOMY_, array( 'post' ), $args );

		 }//if taxonomy

		// adds root folder for current user 
		$user_login = wp_get_current_user()->user_login;
		$term_name = 'root_folder_' . $user_login;

		if ( get_term_by( 'name', $term_name, TAXONOMY_ ) === false ) {

			$wp_term = wp_insert_term(
				$term_name,
				TAXONOMY_,
				array(
					'description' => 'root folder',
					'parent'      => 0,
					'slug'        => 'home',
				)
			);

			// folder path to display in layout
			add_term_meta( $wp_term['term_id'], 'folder_path', 'home', true );

		}//if root folder
	}

	public function build_actions(){
			
			$this->add_contact_page();
			$this->add_about_page();
	}

	public function add_plugin_admin_menu() {

		add_menu_page(
			( 'YT-folders' ), // page title
			( 'YT-folders' ), // menu title
			'publish_posts', // capability 
			$this->plugin_name . '-folder', // menu_slug
		);
	}

	public function remove_menu_items() {

		// for other users than admin
		if ( current_user_can( 'publish_posts' ) && ! current_user_can( 'manage_options' ) ) {
			
			remove_menu_page( 'edit.php' );
			remove_menu_page( 'tools.php' );
			remove_menu_page( 'index.php' );
			remove_menu_page( 'edit-comments.php' );
			remove_menu_page( 'upload.php' );
			remove_menu_page( 'profile.php' );
		}
	}

	public function remove_admin_bar_links() {

		if ( current_user_can( 'publish_posts' ) && ! current_user_can( 'manage_options' ) ) {
					
					global $wp_admin_bar;
					
					$wp_admin_bar->remove_menu( 'new-content' );      // Remove the content link
					$wp_admin_bar->remove_menu( 'comments' );         // Remove the comments link
					$wp_admin_bar->remove_menu( 'view-site' );
			
		}
			
	}
	
	private function add_contact_page() {

		$query = new WP_Query( array( 'pagename' => 'contact' ) );
		
		if ( $query->found_posts == 0 ) {

			$my_post = array(
				'post_title'     => 'Contact',
				'post_type'      => 'page',
				'post_name'      => 'contact',
				'post_content'   => 'This is my page',
				'post_status'    => 'publish',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_author'    => 1,
				'menu_order'     => 0,

			);

			$PageID = wp_insert_post( $my_post, false ); 
			
			if ($PageID==0){
				var_dump( $PageID );
				die;
			}
		}
	}

	private function add_about_page() {

		$query = new WP_Query( array( 'pagename' => 'about' ) );
		
		if ( $query->found_posts == 0 ) {

			$my_post = array(
				'post_title'     => 'About',
				'post_type'      => 'page',
				'post_name'      => 'about',
				'post_content'   => 'This is my page',
				'post_status'    => 'publish',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_author'    => 1,
				'menu_order'     => 0,
			);

			$PageID = wp_insert_post( $my_post, false ); 
		}
	}

	public function my_login_redirect( $redirect_to, $request, $user ) {
		
		$redirect_to = admin_url( 'admin.php?page=' . $this->plugin_name . '-folder' );

		return $redirect_to;
	}

}//end class
