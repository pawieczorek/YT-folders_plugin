
<?php

/*
 * edit video content admin page
 */
	if( current_user_can( 'publish_posts' ) ) {	
		
			$video_id=$_GET["video-id"];
			$post_content=get_post($video_id)->post_content;
			$post_title=get_post($video_id)->post_title;
			$count_lines = substr_count($post_content, "\n")+10;
			?>				
	
			<div>
				<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" id="ajax-form" >
					
					</br>
					<h1 class="wp-heading-inline">EDIT</h1>

					<!-- values to send by ajax  -->
					<input type="hidden" name="action" value="edit_video_content">
					<input type="hidden" name="post-id" value="<?php echo $video_id ?>">
					<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( "edit-video-content" ) ?>">
					
					<!-- save changes button -->
					<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save changes" ><img  id="spinner-video-edit-content" src="<?php echo esc_url( get_admin_url() . 'images/spinner.gif' ); ?>" /></p>

					<!-- cancel button -->
					<button id="cancel-edit" class="button button-primary">back to videos panel</button>

					<!-- video title texarea -->
					</br>
					<h1 class="wp-heading-inline video-title">Video title</h1>
					</br>
					<textarea name="post-title" rows="5" cols="100" id="text-area"><?php echo $post_title ?></textarea>

					<!-- video description texarea -->
					<h1 class="wp-heading-inline">Video description</h1>
					</br>
					<textarea name="post-content" rows="<?php echo $count_lines ?>" cols="100" id="text-area"><?php echo $post_content ?></textarea>
						
				</form>	
			</div>
			<?php    
	}
	else {  
	?>
		<p> <?php echo "You are not authorized to perform this operation." ?> </p>
	<?php   
	}


