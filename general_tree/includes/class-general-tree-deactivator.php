<?php


class General_Tree_Deactivator {

	public static function deactivate() {

		//removes terms (folders) for all users
		$taxonomy_name = TAXONOMY_;
		$terms         = get_terms(
			array(
				'taxonomy'   => $taxonomy_name,
				'hide_empty' => false,
			)
		);
		
		foreach ( $terms as $term ) {
			if ( ! is_wp_error( $term ) && ! empty( $term ) ) {
				wp_delete_term( $term->term_id, $taxonomy_name );
			}
		}

		//removes taxonomy
		unregister_taxonomy_for_object_type( TAXONOMY_, array( 'post' ) );

		// removes menu page
		remove_menu_page( PUGIN_NAME_ . '-folder' );
		
		//removes posts (videos) for all users
		$args  = array(
			'post_type' => array( 'post', 'page', 'revision', 'attachment'),
			'post_status' => array( 'publish', 'inherit', 'trash' ),
			'author'    => get_users(),
			
		);
		$query = new WP_Query( $args );

		if ( $query->have_posts() ) { 
			
			while ( $query->have_posts() ) { 
				$query->the_post(); 		
					$result= wp_delete_post( $query->post->ID, true ); 
			}
			wp_reset_postdata();
		}


	}


}
