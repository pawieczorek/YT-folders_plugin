<?php
/*
 * users admin page
 */
	
		
if( current_user_can( 'manage_options' ) ) {	
					
		$GT_add_nonce = wp_create_nonce( 'GT_form_nonce' ); 
				
		?>	
		</br>
		<h1 class="wp-heading-inline">users</h1>			
		</br>
		<!-- add new user -->
		<div>
			<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" id="" >
				<input type="hidden" name="action" value="add_new_user">
				<input type="hidden" name="GT_form_nonce" value="<?php echo $GT_add_nonce ?>" />			
				<input required type="text" name="GT[user_name]" value="" placeholder="type user name..."/>
				<input required type="text" name="GT[user_password]" value="" placeholder="type user password..."/>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="add user"></p>
			</form>
		</div>
		<!-- delete user data -->
		<div>
			<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" id="" >
				<input type="hidden" name="action" value="delete_user_data">
				<input type="hidden" name="GT_form_nonce" value="<?php echo $GT_add_nonce ?>" />			
				<input required type="text" name="GT[user_name]" value="" placeholder="type user name..."/>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="delete user data"></p>
			</form>
		</div>
		<!-- delete user with his/her data -->
		<div>
			<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" id="" >
				<input type="hidden" name="action" value="delete_user_and_his_data">
				<input type="hidden" name="GT_form_nonce" value="<?php echo $GT_add_nonce ?>" />			
				<input required type="text" name="GT[user_name]" value="" placeholder="type user name..."/>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="delete user with his/her data"></p>
			</form>
		</div>
		<?php    
		}
		
		else {  
		?>
		<p> <?php echo "You are not authorized to perform this operation." ?> </p>
		<?php   
}