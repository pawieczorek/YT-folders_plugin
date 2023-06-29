<?php

/*
 * folders admin page
 */

class Current_User_Root_Folder1 {

		use current_user_root_folder;
}

class Gt_Folders_Page {

	public function page_title_partial() {

		?>
		<div>
			<h1 class="wp-heading-inline">Folders</h1>
		</div>
		</br>
		<?php
	}

	public function folders_tree() {

		require_once 'class-general-tree-DOMdocument.php';
		$dom = new DOMtree( '1.0' );
		$dom->get_ordered_tree( 'page-folders' );
	}

	public function form_add_subfolder( $gt_add_nonce ) {

		?>
		<form class="form-add-folder" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" id="">
			
			<!-- term (folder) to add subfolder -->
			<?php
			$main_term = get_term( Current_User_Root_Folder1::current_user_root_folder_id() );
			?>

			<input type="hidden" name="action" value="gt_form_response">
			<input type="hidden" name="GT_form_nonce" value="<?php echo $gt_add_nonce; ?>" />
			<input type="hidden" class="parent-folder-id" name="GT[parent_folder_ID]" value="<?php echo $main_term->term_id; ?>" />

			<p class="parent-term-name"><?php echo $main_term->name; ?></p>
			<div>
				<input required  type="text" class="new-folder-name" name="<?php echo 'GT'; ?>[folder_name]" value="" placeholder="Subfolder name" /><br>
			</div>
			<p class="submit"><input type="submit" name="submit" id="submit-add-folder" class="button button-primary" value="add subfolder"><img class="spinner-add-folder" src="<?php echo esc_url( get_admin_url() . 'images/spinner.gif' ); ?>"></p>
		</form>
		<?php
	}

	public function form_delete_folder( $gt_add_nonce ) {
		?>
		<form class="form-delete-folder" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" id="delete-folder-form">

			<input type="hidden" name="action" value="gt_form_response">
			<input type="hidden" name="GT_form_nonce" value="<?php echo $gt_add_nonce; ?>" />
			<input required class="folder-id-to-delete" autocomplete="off" name="GT[term_id]" value=""/>

			<p class="folder-id-to-delete"></p>
			<p class="submit"><input type="submit" name="submit" id="submit-delete-folder" class="button button-primary" value="delete folder"><img class="spinner-delete-folder" src="<?php echo esc_url( get_admin_url() . 'images/spinner.gif' ); ?>"></p>
		</form>
		<?php
	}

	public function form_rename_folder( $gt_add_nonce ) {

		?>
		<form class="form-rename-folder" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" id="rename-folder-form">
			
			<input type="hidden" name="action" value="gt_form_response">
			<input type="hidden" name="GT_form_nonce" value="<?php echo $gt_add_nonce; ?>" />
			<input required class="folder-id-to-rename" autocomplete="off" name="GT[new-name-term-id]" value="" />
			
			<p class="folder-id-to-rename"> choose folder </p>
			<div>
				<input class="folder-to-rename" required type="text" name="<?php echo 'GT'; ?>[new-name]" value="" placeholder="New name" /><br>
			</div>
			<p class="submit"><input type="submit" name="submit" id="submit-rename-folder" class="button button-primary" value="rename folder"><img class="spinner-rename-folder" src="<?php echo esc_url( get_admin_url() . 'images/spinner.gif' ); ?>"></p>
		</form>
		<?php
	}

	public function form_add_thumbnail_folder( $gt_add_nonce ) {

		?>
		
		<form class="form-add-thumbnail-folder"action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" enctype="multipart/form-data">
			
			<!-- folder to attach a thumbnail -->
			<input required class="folder-id-for-thumbnail" autocomplete="off" name="GT[folder-id-for-thumbnail]" value="" />
			<p class="folder-id-for-thumbnail"></p>
			
			<div class="row">
					<!-- current thumbnail -->
					<div class="col-sm-6">
						<img id='folder-thumbnail' class="center"  src="<?php echo esc_url( get_admin_url() . 'images/spinner.gif' ); ?>">
					</div>
					<div class="col-sm-6">
						<!-- choose thumbnail button -->
						<input class="custom-file-input" type="file" accept="image/png, image/jpeg, image/*" name="profile_picture" /><button type="button" class="upload-file-button button button-primary">choose thumbnail</button>
						<!-- file name -->
						<p class="file-name">no file yet</p>

						<input type="hidden" name="action" value="gt_form_response">
						<input type="hidden" name="GT_form_nonce" value="<?php echo $gt_add_nonce; ?>" />
						<!-- upload thumbnail button -->
						<p class="submit-thumbnail">
							<input type="submit" name="submit"  id="submit-add-folder-thumbnail" class="button button-primary" value="upload thumbnail"><img class="spinner-add-folder-thumbnail" src="<?php echo esc_url( get_admin_url() . 'images/spinner.gif' ); ?>">
						</p>
					</div>
			</div>	
		</form>
		<?php
	}

}

// ----------------COMPLETE VIEW---------------------

if ( current_user_can( 'publish_posts' ) ) {

	$gt_folders_page = new Gt_Folders_Page();
	$gt_add_nonce    = wp_create_nonce( 'GT_form_nonce' );

	// view partials
	$gt_folders_page->page_title_partial();
	$gt_folders_page->folders_tree();
	?>
	
	<div class="folders-page-forms">
		<?php
		// view partials
		$gt_folders_page->form_add_subfolder( $gt_add_nonce );
		$gt_folders_page->form_rename_folder( $gt_add_nonce );
		$gt_folders_page->form_delete_folder( $gt_add_nonce );
		$gt_folders_page->form_add_thumbnail_folder( $gt_add_nonce );
		?>	 
	</div>
	<?php
} else {
	?>
	<p> <?php echo 'You are not authorized to perform this operation.'; ?> </p>
	<?php
}
