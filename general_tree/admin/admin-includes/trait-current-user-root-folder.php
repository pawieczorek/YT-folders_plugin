<?php

trait current_user_root_folder {

	public static function current_user_root_folder_name() {

		$user_login = wp_get_current_user()->user_login;
		$term_name  = 'root_folder_' . $user_login;

		return $term_name;
	}

	public static function current_user_root_folder_id() {

		$user_login = wp_get_current_user()->user_login;
		$term_name  = 'root_folder_' . $user_login;
		$term_id    = get_term_by( 'name', $term_name, TAXONOMY_ )->term_id;

		return $term_id;
	}

}
