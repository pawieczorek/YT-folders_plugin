<?php
/*
 * folder tree (access to videos panel) admin page
 */

?>

<div>	
	<h1 class="wp-heading-inline">Choose folder <br> to add/edit/trash its videos</h1>
</div>
<div>
	<?php 
	require_once('class-general-tree-DOMdocument.php');
	$dom1 = new DOMtree('1.0');
	$dom1 ->get_ordered_tree('page-video');
	?>
</div>

 
	
	

	
