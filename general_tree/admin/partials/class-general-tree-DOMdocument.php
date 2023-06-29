<?php


class DOMtree extends DOMdocument {

	use current_user_root_folder;

		private $taxonomy = TAXONOMY_;
		public $tree_code;

	public function get_ordered_tree( $tree_code ) {

			$this->create_tree_container( $tree_code );

			$queue   = array( $this->get_main_folder() );
			$data    = $this->get_folders_from_db();
			$DOMtree = $this;
			$this->level_order( $queue, $data, $DOMtree );

			echo $this->saveHTML();
	}

	private function create_tree_container( $tree_code ) {

		$div = $this->createElement( 'div' );
		$div_node = $this->appendChild( $div );
		$attr = $this->setClass( $div_node, 'gt-tree-container ' . $tree_code );
		$ul = $this->x_append_y( $div_node, 'ul' );
		$li = $this->x_append_y( $ul, 'li' );
		$attr = $this->setId( $li, 0 );
		$p = $this->x_append_y( $li, 'p' );
		$this->x_append_y( $li, 'ul' );
	}

	private function level_order( array $queue_, array $data, &$DOMtree ) {

		if ( count( $queue_ ) === 0 ) {
			return; 
		}

		$node = array_shift( $queue_ );
		$parent_node = $DOMtree->getElementById( $node->parent );
		$children_list = $parent_node->childNodes->item( 1 ); 

		$li = $DOMtree->x_append_y( $children_list, 'li' );
		$DOMtree->setId( $li, $node->term_id );
		$button = $DOMtree->x_append_y( $li, 'button' );
		$DOMtree->x_append_text( $button, $node->name );
		$DOMtree->setClass( $button, 'folder-name button button-primary' );
		$DOMtree->x_append_y( $li, 'ul' );

		$children = array_filter(
			$data,
			function( $term ) use ( &$node ) {

				if ( $term->parent == $node->term_id ) {
					return 1;
				} else {
					return 0;
				}
			}
		);

		foreach ( $children as $child ) {

				$queue_[] = $child; }

		return $this->level_order( $queue_, $data, $DOMtree );
	}

	private function get_main_folder() {

		$main_folder          = new stdClass();
		$main_folder->name    = $this->current_user_root_folder_name();
		$main_folder->term_id = $this->current_user_root_folder_id();
		$main_folder->parent  = 0;

		return $main_folder;
	}

	private function get_folders_from_db() {

		global $wpdb;

		$sql1 = $wpdb->prepare( "SELECT name, term_id, parent FROM $wpdb->term_taxonomy INNER JOIN $wpdb->terms USING (term_id) WHERE taxonomy=%s;", $this->taxonomy );

		$data = $wpdb->get_results( $sql1 );

		return $data;
	}

	private function x_append_y( DOMNode $x, string $name ) {

		$y = $this->createElement( $name );
		$y = $x->appendChild( $y );  // die();
		return $y;
	}

	private function x_append_text( DOMNode $x, string $text ) {

		$text = $this->createTextNode( $text );
		$text = $x->appendChild( $text );
		return $text;
	}

	private function setId( DOMNode $x, string $y ) {

		$attr = $x->setAttributeNode(
			new DOMAttr( 'id', $y )
		);
		$x->setIDAttribute( 'id', true );
	}

	private function setClass( DOMNode $x, string $y ) {

		$attr = $x->setAttributeNode(
			new DOMAttr( 'class', $y )
		);
	}

}
