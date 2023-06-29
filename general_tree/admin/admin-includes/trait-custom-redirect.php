<?php

trait my__redirect {

	use my__debug;

	private function custom_redirect( array $redirect ) {

		wp_redirect(
			esc_url_raw(
				add_query_arg(
					array(

						'notice'         => $redirect['notice'],
						'notice-type'    => $redirect['notice-type'],
						'post-data'      => $redirect['post-data'],
						'wp-error'       => $redirect['wp-error'],
						'notice-on-page' => $redirect['notice-on-page'],
					),
					admin_url( 'admin.php?page=' . $redirect['destination-page'] )
				)
			)
		);
	}

}//trait
