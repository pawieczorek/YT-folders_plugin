<?php

class General_Tree_Activator {

	public static function activate() {

			// gets the author role
			$role = get_role( 'author' );
			// This only works, because it accesses the class instance.
			// would allow the author to edit others' posts for current theme only
			$role->add_cap( 'manage_categories' ); 		
	}

}
