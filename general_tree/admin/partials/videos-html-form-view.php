<?php

class Current_User_Root_Folder22 {
	
	use current_user_root_folder;
}


class Gt_Videos_Page {

	public $folder_id;
	public $folder_name;

	public function __construct( $folder_id ) {

		$this->folder_id   = $folder_id;
		$this->folder_name = get_term( $folder_id )->name;
	}

	//gets videos for particular folder and author 
	
	public function query_videos() {

		if ( is_user_logged_in() ) {
			$author = strval( get_current_user_id() );
		} else {
			$author = ADMIN_ID;
		}

		$the_query = new WP_Query(
			array(
				'post_type' => 'post',
				'author'    => $author,
				'post_status' => array( 'publish' ),
				'tax_query' => array(
					array(
						'taxonomy'         => TAXONOMY_,
						'field'            => 'id',
						'terms'            => array( $this->folder_id ),
						'include_children' => false,

					),
				),
			)
		);

		return $the_query;
	}
	
	// ----------------PARTIAL VIEWs----------------------------------

	public function page_title_partial() {

		$back_to_folders = admin_url( 'admin.php?page=' . 'general-tree-video' );
		//gets folder path
		$url = get_term_meta($this->folder_id , 'folder_path', true);
		?>
		<div>
			<h1 class="wp-heading-inline">Videos --><?php echo $url; ?></h1>
			</br>
			<a href="<?php echo $back_to_folders; ?>" class="button button-primary">back to folders page</a>
		</div>
		</br>
		<?php
	}

	public function add_new_video_partial() {

		?>
		<p>Set the cursor over YouTube video. Press the right button on the mouse. Choose: copy video URL
		</p>

		<form id="add-new-video-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<input type="hidden" name="action" value="make_video_feed">
			<input type="hidden" name="folder_term_id" value="<?php echo $this->folder_id; ?>">
			<input required type="text" size="60" maxlength="256" name="video_url" id="video_url" value="" placeholder='Enter youTube video URL...' />
			<div class="add-video">
				<input id="add-video" disabled class="button button-primary" type="submit" name="grab_feed" value="Add new video" /><img id="spinner-add-new-video" src="<?php echo esc_url( get_admin_url() . 'images/spinner.gif' ); ?>" />
			</div>
		</form>
		</br>
		<?php
	}

	public function table_head_partial() {

		?>
		<thead>
					<tr>
						<td id="cb" class="manage-column column-cb check-column">
							<label class="screen-reader-text" for="cb-select-all-1">Select All</label>
							<input id="cb-select-all-1" type="checkbox">
						</td>

						<th scope="col" id="title" class="manage-column column-title column-primary sortable desc">
							<span>Title</span>
						</th>

						<th scope="col" id="author" class="manage-column column-author">Author</th>

						<th scope="col" id="categories" class="manage-column column-categories">Folder</th>

						<th scope="col" id="date" class="manage-column column-date sortable asc">
							<a href="javascript:void(0)">
								<span>Date</span><span class="sorting-indicator"></span>
							</a>
						</th>
					</tr>
				</thead>
			<?php
	}

	public function table_foot_partial() {

		?>
		<tfoot>
				<tr>
					<td class="manage-column column-cb check-column">
						<label class="screen-reader-text" for="cb-select-all-2">Select All</label>
						<input id="cb-select-all-2" type="checkbox">
					</td>

					<th scope="col" class="manage-column column-title column-primary sortable desc">
						<span>Title</span>
					</th>

					<th scope="col" class="manage-column column-author">Author</th>

					<th scope="col" class="manage-column column-categories">Folder</th>
					
					<th scope="col" class="manage-column column-date sortable asc">
						<a href="javascript:void(0)">
							<span>Date</span>
							<span class="sorting-indicator"></span>
						</a>
					</th>
				</tr>
			</tfoot>
		<?php
	}

	public function table_row_partial() {

		?>		
			<tr id="title-<?php echo get_the_ID(); ?>" class="iedit author-self level-0 post-1 type-post status-publish format-standard hentry category-Dummy category">

				<th scope="row" class="check-column">
					<label class="screen-reader-text" for="cb-select-1">Select Post #1</label>
					<input class="info-for-js" id="cb-select-1" type="checkbox" name="video-ids[]" value="<?php echo get_the_ID(); ?>">
					<div class="locked-indicator">
						<span class="locked-indicator-icon" aria-hidden="true"></span>
						<span class="screen-reader-text">
						“Post #1” is locked
						</span>
					</div>
				</th>

				<td class="title column-title has-row-actions column-primary page-title" data-colname="Title">
					<div class="locked-info">
						<span class="locked-avatar"></span>
						<span class="locked-text"></span>
					</div>
					<strong>
					<p class="row-title" href="javascript:void(0)" aria-label="“Post #1” (Edit)">
					<?php echo the_title(); ?>
					</p>
					</strong>
					<div class="row-actions">
						<span class="edit"><a id="<?php echo get_the_ID(); ?>" class="video-delete" href="<?php echo esc_url( admin_url( 'admin-post.php' ) ) . '?action=trash_videos&folderid=' . $this->folder_id . '&video-id=' . get_the_ID() . '&_wpnonce=' . wp_create_nonce( 'trash-nonce' ); ?>" aria-label="Edit “Post #1”">Trash</a> | </span>
						<span class="view"><a id="<?php echo get_the_ID(); ?>" href="<?php echo esc_url( admin_url( 'admin.php' ) ) . '?page=general-tree-video-edit-content&folderid=' . $this->folder_id . '&video-id=' . get_the_ID(); ?>" aria-label="Edit “Post #1”">Edit</a> | </span>
					</div>
					<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
				</td>

				<td class="author column-author" data-colname="Author">
					<p href="javascript:void(0)"><?php echo get_the_author_meta( 'nickname', get_current_user_id() ); ?></p>
				</td>

				<td class="categories column-categories" data-colname="Categories">
					<p href="javascript:void(0)"><?php echo get_term( $this->folder_id, TAXONOMY_ )->name; ?></p>
				</td>

				<td class="date column-date" data-colname="Date">Published<br>
					<abbr title="2019/08/22 9:00:46 am"><?php echo get_the_date(); ?></abbr>
				</td>

			</tr>
			<?php
	}

}//end class

// ----------------COMPLETE VIEW----------------------------------


if ( current_user_can( 'publish_posts' ) ) {

		$folder_id = $_GET['folderid'];

		$gt_videos_page = new Gt_Videos_Page( $folder_id );
		$gt_videos_page->page_title_partial();
		$gt_videos_page->add_new_video_partial();

		?>
		<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<input type="hidden" name="action" value="trash_videos">
				<table id="videos-table" class="wp-list-table widefat fixed striped posts">
					
					<?php
					//table head
					$gt_videos_page->table_head_partial();
					//table row
					$query = $gt_videos_page->query_videos();
					if ( $query->have_posts() ) :
						?>
					<tbody id="the-list">
						<?php
						while ( $query->have_posts() ) :
							$query->the_post();
							$gt_videos_page->table_row_partial();
							endwhile;
						?>
					</tbody>
						<?php
						else :
						?>
						<tbody id="the-list">
							<tr class="no-items">
								<td class="colspanchange" colspan="7">No videos found.</td>
							</tr>
						</tbody>
						<?php
					endif;
					wp_reset_postdata();
					//table foot
					$gt_videos_page->table_foot_partial();
					?>
				</table>
				</br>
			
			<!-- button trash checked videos	 -->
			<input hidden name="_wpnonce" value="<?php echo wp_create_nonce( 'trash-nonce' ); ?>" />
			<input hidden name="folderid" value="<?php echo $folder_id; ?>" />
			<input id="video-input" disabled class="button button-primary" type="submit" name="grab_feed" value="Trash checked videos" /><img id="spinner-trash-checked-videos" src="<?php echo esc_url( get_admin_url() . 'images/spinner.gif' ); ?>">
		</form>
		<?php

} else {

		?>
		<p> <?php echo 'You are not authorized to perform this operation.'; ?> </p>
		<?php
}
