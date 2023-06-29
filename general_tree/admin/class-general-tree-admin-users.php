<?php
class General_Tree_Admin_Add_User {

	use my__debug;
	use my__redirect;

	private $plugin_name;
	private $version;
 
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	public function add_plugin_admin_menu() {

		$add_user_html_form_page_hook = add_submenu_page(
			$this->plugin_name . '-folder', // parent slug
			( 'Users' ), // page title
			( 'Users' ), // menu title
			'manage_options', // capability 
			$this->plugin_name . '-users', // menu_slug
			array( $this, 'html_add_user_page_content' ) // callback for page content
		);

		add_action( 'load-' . $add_user_html_form_page_hook, array( $this, 'loaded_add_user_html_form_submenu_page' ) );
	}

	/*
	 * Callback for the load-($video_html_form_submenu_page)
	 * Called when the plugin's submenu HTML form page is loaded
	 */

	public function loaded_add_user_html_form_submenu_page() {
		// called when the particular page is loaded.
	}

	public function html_add_user_page_content() {
		// show the form
		include_once 'partials/users-html-form-view.php';
	}

	public function new_user() {

		if ( isset( $_POST['GT_form_nonce'] ) && wp_verify_nonce( $_POST['GT_form_nonce'], 'GT_form_nonce' ) ) {

			if ( isset( $_POST['GT']['user_name'] ) ) {
				$user_name = sanitize_text_field( $_POST['GT']['user_name'] );
			}
			if ( isset( $_POST['GT']['user_password'] ) ) {
				$user_password = sanitize_text_field( $_POST['GT']['user_password'] );
			}

			if ( isset( $user_name ) && isset( $user_password ) ) {
				$this->insert_new_user( $user_name, $user_password );
			}
		}//if
	}

	//deletes user's data (terms/folders, posts/videos, media/thumbnails )
	public function delete_user_data() {

		if ( isset( $_POST['GT_form_nonce'] ) && wp_verify_nonce( $_POST['GT_form_nonce'], 'GT_form_nonce' ) ) {

			if ( isset( $_POST['GT']['user_name'] ) ) {
				$user_name = sanitize_text_field( $_POST['GT']['user_name'] );
				//false argument ==> does not delete user, ONLY user's data
				$this->delete_user_execute( $user_name, false );
			}
		}
	}

	//deletes user's data (terms/folders, posts/videos, media/thumbnails ) and user and user's term/root_folder_user
	public function delete_user_and_his_data(){

		if ( isset( $_POST['GT_form_nonce'] ) && wp_verify_nonce( $_POST['GT_form_nonce'], 'GT_form_nonce' ) ) {

			if ( isset( $_POST['GT']['user_name'] ) ) {
				$user_name = sanitize_text_field( $_POST['GT']['user_name'] );
				//true argument ==> deletes user's data AND user and user's term/root_folder_user 
				$this->delete_user_execute( $user_name, true);
			}
		}


	}

	public function delete_user_root_folder( $user_id ) {

		$author_obj = get_user_by('id', $user_id);
		$term_name = "root_folder_".$author_obj->user_nicename;

		$term = get_term_by('name', $term_name, TAXONOMY_);

		wp_delete_term($term->term_id, TAXONOMY_);
	}
	

// private functions============================================================================

	private function insert_new_user( string $user_name, string $user_password ) {

		$user = new WP_User( $user_name );

		$user->user_pass = $user_password;
		$user->user_login    = $user_name;
		$user->user_nicename = $user_name;

		$result = wp_insert_user( $user );

		if ( is_wp_error( $result ) ) {

			$notice_type = 'error';
			$notice      = 'something went wrong. user not created';
			$this->send_notice( $notice, $notice_type );

		} else {
			$author_obj = get_user_by( 'login', $user_name );
			$author_obj->add_role( 'author' );

			$notice_type = 'success';
			$notice      = ' user created';
			$this->send_notice( $notice, $notice_type );

		}
	}

	private function delete_user_execute( string $user_name, bool $with_user ) {

		$author_obj = get_user_by( 'login', $user_name );

		//user does not exist
		if ( $author_obj == false ) {

			$notice_type = 'error';
			$notice      = 'no such a user';
			$this->send_notice( $notice, $notice_type );

		//user exists
		} else {

			//deletes user's data
			$this->delete_user_folders($author_obj );
			$this->delete_user_videos($author_obj );

			//deletes user
			if ($with_user){

				//deletes user's term/root_folder_user
				$term_name = "root_folder_".$author_obj->user_nicename;
				$term = get_term_by('name', $term_name, TAXONOMY_);
				wp_delete_term($term->term_id, TAXONOMY_);
				
				//deletes user
				$result = wp_delete_user( $author_obj->ID);
				
				if ($result){
					$notice_type = 'success';
					$notice      = 'user and his/her data deleted';
					$this->send_notice( $notice, $notice_type );
					}
					else{
						$notice_type = 'error';
						$notice      = 'something went wrong';
						$this->send_notice( $notice, $notice_type );						 
					}
				}

			$notice_type = 'success';
			$notice      = 'user data deleted';
			$this->send_notice( $notice, $notice_type );
			}
			
		
	}

	private function delete_user_folders( object $author_obj ){
			
			// user's folders
			$term_name_root_folder = 'root_folder_' . $author_obj->user_login;
			$term                  = get_term_by( 'name', $term_name_root_folder, TAXONOMY_ );
			
			if ( $term == false ) {
				$notice_type = 'error';
				$notice      = 'no user root folder in DB';
				$this->send_notice( $notice, $notice_type );

			}
			
			$term_children = get_term_children( $term->term_id, TAXONOMY_ );

			// deletes user's root folder children (subfolders)
			if ( count( $term_children ) > 0 ) {
				foreach ( $term_children as $term_id ) {
					
					wp_delete_term( $term_id, TAXONOMY_ );
				}
			}
			// deletes user's root folder
			wp_delete_term( $term_id, TAXONOMY_ );

	}

	private function delete_user_videos( object $author_obj  ){
			
			// removes users posts (videos)
			$args  = array(
				'post_type' => array( 'post', 'revision', 'attachment'),
				'post_status' => array( 'publish', 'inherit', 'trash' ),
				'author_name' => $author_obj->user_nicename
			);
			$query = new WP_Query( $args );

			if ( $query->have_posts() ) { // if the current user has posts under his/her name

			while ( $query->have_posts() ) { 
			$query->the_post(); 
				$result= wp_delete_post( $query->post->ID, true ); // delete the post
			}
			wp_reset_postdata();
			}
	}

	private function send_notice( string $notice, string $notice_type, array $additional_args = null ) {

		wp_redirect(
			esc_url_raw(
				add_query_arg(
					array(
						// notice args
						'notice-on-page' => 'true',
						'notice'         => $notice,
						'notice-type'    => $notice_type,
					),
					admin_url( 'admin.php?page=' . $this->plugin_name . '-users' )
				)
			)
		);

		die();
	}





}//end class
