<?php

class General_Tree_Admin_Folder {

	use my__redirect;
	use my__debug;
	use current_user_root_folder;


	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	// Callback for the admin menu
	public function add_plugin_admin_menu() {

		// Adds a submenu page and save the returned hook suffix.
		$folder_html_form_page_hook = add_submenu_page(
			$this->plugin_name . '-folder', // parent slug
			( 'Folders' ), // page title
			( 'Folders' ), // menu title
			'publish_posts', // capability
			$this->plugin_name . '-folder', // menu_slug
			array( $this, 'html_add_folder_page_content' ) // callback for page content
		);

		add_action( 'load-' . $folder_html_form_page_hook, array( $this, 'loaded_folder_html_form_submenu_page' ) );
	}

	/*
	 * Callback for the add_submenu_page action hook
	 * The plugin's HTML form is loaded from here
	 */
	public function html_add_folder_page_content() {

		include_once 'partials/folder-html-form-view.php';
	}

	/*
	 * Callback for the load-($html_form_page_hook)
	 * Called when the plugin's submenu HTML form page is loaded
	 */
	public function loaded_folder_html_form_submenu_page() {
		// called when the particular page is loaded.
	}


	public function the_form_response() {
		if ( isset( $_POST['GT_form_nonce'] ) && wp_verify_nonce( $_POST['GT_form_nonce'], 'GT_form_nonce' ) ) {

			if ( isset( $_POST['submit'] ) && $_POST['submit'] == 'add subfolder' ) {

				$this->add_subfolder();
			}

			if ( isset( $_POST['submit'] ) && $_POST['submit'] == 'delete folder' ) {

				$this->delete_folder();
			}

			if ( isset( $_POST['submit'] ) && $_POST['submit'] == 'delete subfolders' ) {

				$this->delete_folder();
			}

			if ( isset( $_POST['submit'] ) && $_POST['submit'] == 'rename folder' ) {

				$this->rename_folder();
			}

			if ( isset( $_POST['submit'] ) && $_POST['submit'] == 'upload thumbnail' ) {

				$this->add_folder_thumbnail();
			}
		} else {

			$notice_type = 'error';
			$notice      = 'The HTTP 403 Forbidden response status code.';
			$this->send_notice( $notice, $notice_type );
		}
	}//the_form_response

	private function rename_folder() {

		$term_id       = sanitize_text_field( $_POST['GT']['new-name-term-id'] );
		$term_new_name = sanitize_text_field( $_POST['GT']['new-name'] );

		$user_login     = wp_get_current_user()->user_login;
		$root_term_name = 'root_folder_' . $user_login;

		$term_old_name = get_term( $term_id )->name;

		if ( $term_old_name == $root_term_name ) {

			$notice      = 'root folder name can not be changed';
			$notice_type = 'error';

			$this->send_notice( $notice, $notice_type );

		}

		$update       = wp_update_term(
			$term_id,
			$taxonomy = TAXONOMY_,
			array(
				'name' => $term_new_name,
				'slug' => $term_new_name,
			)
		);

		if ( ( is_wp_error( $update ) ) ) {

			$notice_type = 'error';
			$notice      = $update->get_error_message();

		} else {

			$this->folder_path( $term_id, $term_new_name );

			$notice_type = 'success';
			$notice      = 'folder name has been changed';
		}

		$this->send_notice( $notice, $notice_type );
	}// rename_folder()

	private function add_subfolder() {

		$term_name = sanitize_text_field( $_POST['GT']['folder_name'] );
		$parent    = sanitize_text_field( $_POST['GT']['parent_folder_ID'] );

		$current_user = get_current_user_id();

		$wp_term      = wp_insert_term(
			$term_name,
			$taxonomy = TAXONOMY_,
			array(
				'description' => '',
				'parent'      => $parent,
				'slug'        => '',
				'term_group'  => $current_user,
			)
		);

		if ( ( is_wp_error( $wp_term ) ) ) {

			$notice_type = 'error';
			$notice      = $wp_term->get_error_message();

		} else {

			$this->folder_path( $wp_term['term_id'], $term_name );

			$this->add_default_folder_thumbnail( $wp_term['term_id'] );

			$notice_type = 'success';
			$notice      = 'new subfolder has been added';

		}

		$this->send_notice( $notice, $notice_type );
	} //add folder

	private function delete_folder() {

		$term_to_delete_id = $_POST['GT']['term_id'];

		// only subfolders can be deleted. root folder can not be removed
		if ( $term_to_delete_id != $this->current_user_root_folder_id() ) {

			$this->delete_posts( $term_to_delete_id );
			
			$result = wp_delete_term( $term_to_delete_id, TAXONOMY_ );

			if ( $result != true ) {

				$notice_type = 'error';
				$notice      = 'something went wrong. wp_delete_term ';

				$this->send_notice( $notice, $notice_type );
			}
		}

		$term_children = get_term_children( $term_to_delete_id, TAXONOMY_ ); // : array|WP_Error

		foreach ( $term_children as $child ) {

			$this->delete_posts( $child );

			$result = wp_delete_term( $child, TAXONOMY_ );

			if ( $result != true ) {

				$notice_type = 'error';
				$notice      = 'something went wrong. wp_delete_term ';

				$this->send_notice( $notice, $notice_type );
			}
		}

		$notice_type = 'success';
		$notice      = 'folder (with its subfolders) has been deleted ';

		$this->send_notice( $notice, $notice_type );

	}//delete_folder

	private function add_folder_thumbnail() {

		$term_id = sanitize_text_field( $_POST['GT']['folder-id-for-thumbnail'] );

		// deletes old image, if one exists
		$image_attachement = get_page_by_title( 'thumbnail-' . $term_id, OBJECT, 'attachment' );

		if ( $image_attachement != null ) {

			wp_delete_attachment( $image_attachement->ID, true );
		}

		require ABSPATH . '/wp-load.php';

		// allows to use wp_handle_upload() function
		require_once ABSPATH . 'wp-admin' . '/includes/file.php';

		if ( empty( $_FILES['profile_picture'] ) ) {

			$notice_type = 'error';
			$notice      = 'No files selected';

			$this->send_notice( $notice, $notice_type );
		}

		$upload = wp_handle_upload(
			$_FILES['profile_picture'],
			array( 'test_form' => false )
		);

		if ( ! empty( $upload['error'] ) ) {

			$notice_type = 'error';
			$notice      = $upload['error'];

			$this->send_notice( $notice, $notice_type );
		}

		$attachment_id = wp_insert_attachment(
			array(
				'guid'           => $upload['url'],
				'post_mime_type' => $upload['type'],
				'post_title'     => 'thumbnail-' . $term_id,
				'post_content'   => '',
				'post_status'    => 'inherit',
			),
			$upload['file']
		);

		if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {

			$notice_type = 'error';
			$notice      = 'something went wrong. thumbnail loading';

			$this->send_notice( $notice, $notice_type );
		}

		// update medatata, regenerate image sizes
		require_once ABSPATH . 'wp-admin/includes/image.php';

		wp_update_attachment_metadata(
			$attachment_id,
			wp_generate_attachment_metadata( $attachment_id, $upload['file'] )
		);

		$notice_type = 'success';
		$notice      = 'thumbnail added';

		$this->send_notice( $notice, $notice_type );
	}//add_folder_thumbnail

	private function delete_posts( int $child ) {

		$the_query = new WP_Query(
			array(
				'post_type'   => array( 'post', 'revision', 'attachment' ),
				'post_status' => array( 'publish', 'inherit', 'trash' ),
				'tax_query'   => array(
					array(
						'taxonomy'         => TAXONOMY_,
						'field'            => 'id',
						'terms'            => array( $child ),
						'include_children' => false,
					),
				),
			)
		);
		$this->debug_file('posts',$the_query->posts());
		if ( $the_query->have_posts() ) :

			while ( $the_query->have_posts() ) :
				$the_query->the_post(); {

					$post_thumbnail_id = get_post_thumbnail_id(get_the_ID());
					wp_delete_post( $post_thumbnail_id, true );

					$result = wp_delete_post( get_the_ID(), true );

				if ( $result == false || $result == null ) {

					$notice_type = 'error';
					$notice      = 'something went wrong. wp_delete_post';

					$this->send_notice( $notice, $notice_type );

				}
				}
			endwhile;
		else :
		endif;
	}//delete_posts

	private function send_notice( string $notice, string $notice_type ) {

		wp_redirect(
			esc_url_raw(
				add_query_arg(
					array(

						// notice args
						'notice-on-page' => 'true',
						'notice'         => $notice,
						'notice-type'    => $notice_type,
						'term_id'        => $_POST['GT']['term_id'],
					),
					admin_url( 'admin.php?page=' . $this->plugin_name . '-folder' )
				)
			)
		);

		die();
	}//send_notice

	private function folder_path( $term_id, $term_name ) {

		$term_parent_id = wp_get_term_taxonomy_parent_id( $term_id, TAXONOMY_ );

		$parent_folder_path = get_term_meta( $term_parent_id, 'folder_path', true );

		$folder_path = $parent_folder_path . '/' . $term_name;

		// folder path to display in layout
		update_term_meta( $term_id, 'folder_path', $folder_path );
	}//folder_path

	private function add_default_folder_thumbnail( $term_id ) {

		$image_path = plugin_dir_url( __FILE__ ) . 'images/' . DEFAULT_THUMBNAIL;

		$attachment_id = wp_insert_attachment(
			array(
				'guid'         => $image_path,
				'post_title'   => 'thumbnail-' . $term_id,
				'post_content' => '',
				'post_status'  => 'inherit',
			)
		);

		if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {

			$notice_type = 'error';
			$notice      = 'something went wrong. default thumbnail loading';

			$this->send_notice( $notice, $notice_type );
		}

		// update medatata, regenerate image sizes
		require_once ABSPATH . 'wp-admin/includes/image.php';

		wp_update_attachment_metadata(
			$attachment_id,
			wp_generate_attachment_metadata( $attachment_id, $image_path )
		);
	}

}//end class
