<?php

class General_Tree_Admin_Video {

	use my__debug;
	use my__redirect;




	private $plugin_name;

	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	public function add_plugin_admin_menu() {
		$video_html_form_page_hook = add_submenu_page(
			$this->plugin_name . '-folder', // parent slug
			( 'Videos' ), // page title
			( 'Videos' ), // menu title
			'publish_posts', // capability
			$this->plugin_name . '-video', // menu_slug
			array( $this, 'html_add_video_page_content' ) // callback for page content
		);

		add_action( 'load-' . $video_html_form_page_hook, array( $this, 'loaded_video_html_form_submenu_page' ) );

		// hidden subpages

		$video_edit_content_html_form_page_hook = add_submenu_page(
			$this->plugin_name . '-video', // parent slug
			( 'Add Video' ), // page title
			( 'Edit Video Content' ), // menu title
			'publish_posts', // capability
			$this->plugin_name . '-video-edit-content', // menu_slug
			array( $this, 'html_edit_video_content_page_content' ) // callback for page content
		);

		add_action( 'load-' . $video_html_form_page_hook, array( $this, 'loaded_edit_video_content_html_form_submenu_page' ) );

		$videos_html_form_page_hook = add_submenu_page(
			$this->plugin_name . '-video', // parent slug
			( 'Add Video' ), // page title
			( 'Videos' ), // menu title
			'publish_posts', // capability
			$this->plugin_name . '-videos', // menu_slug
			array( $this, 'html_videos_page_content' ) // callback for page content
		);

		add_action( 'load-' . $videos_html_form_page_hook, array( $this, 'loaded_videos_html_form_submenu_page' ) );
	}


	/*
	 * Callback for the load-($video_html_form_submenu_page)
	 * Called when the plugin's submenu HTML form page is loaded
	 */

	public function loaded_video_html_form_submenu_page() {
		// called when the particular page is loaded.
	}

	public function html_add_video_page_content() {
		// show the form
		include_once 'partials/video-html-form-view.php';
	}



	// hidden subpages

	public function loaded_edit_video_content_html_form_submenu_page() {
		// called when the particular page is loaded.
	}

	public function html_edit_video_content_page_content() {
		// show the form
		include_once 'partials/edit_video_content-html-form-view.php';
	}

	public function loaded_videos_html_form_submenu_page() {
		// called when the particular page is loaded.
	}

	public function html_videos_page_content() {
		// show the form
		include_once 'partials/videos-html-form-view.php';
	}




	// YouTube API------

	public function make_video_feed() {
		if ( isset( $_POST['grab_feed'] ) ) {

			$this->args       = $this->get_video_feed_form();
			$youtube_video_id = substr( $this->args['youtube_url'], 17 );
			$data_from_yt_api = $this->queryApi( $youtube_video_id );
			$this->save_video( $data_from_yt_api->items[0] );
		}
	}

	private function get_video_feed_form() {
			$args = array(
				'youtube_url'    => isset( $_POST['video_url'] ) ? trim( stripslashes( $_POST['video_url'] ) ) : '',
				'folder_term_id' => isset( $_POST['folder_term_id'] ) ? $_POST['folder_term_id'] : '',
			);
			return $args;
	}

	/*
	 * Create the post with the video info using the video feed settings
	 */
	public function create_the_post( array $args, array $videoInfo ) {

		$vidpost = array(
			'post_name'    => sanitize_title( $videoInfo['title'] ),

			'post_title'   => $videoInfo['title'],
			'post_content' => $videoInfo['desc'],
			'post_status'  => 'publish',
			'post_author'  => get_current_user_id(),

		);

		$vidpost['post_status'] = 'publish';

		kses_remove_filters();
		$postID = wp_insert_post( $vidpost, true );
		kses_init_filters();

		if ( is_wp_error( $postID ) || $postID == 0 ) {

			$notice_type = 'error';
			$notice      = 'something went wrong. wp_insert_post';

			$this->send_notice( $notice, $notice_type, array( 'folder_id' => $this->args['folder_term_id'] ) );

		} else {

			$result = wp_set_post_terms( $postID, $_POST['folder_term_id'], TAXONOMY_ );

			add_post_meta( $postID, 'VideoID', $videoInfo['videoID'] );
			add_post_meta( $postID, 'VideoDuration', $videoInfo['duration'] );
			add_post_meta( $postID, 'video_url', $videoInfo['url'] );

		}
		return $postID;
	}

	protected function get_value( $args, $key, $default = '', $noZero = 0 ) {
		if ( ! isset( $args[ $key ] ) ) {
			return $default;
		} elseif ( $args[ $key ] == 0 && $noZero ) {
			return $default;
		}
		return str_replace( '"', "'", $args[ $key ] );
	}

	private function queryApi( $youtube_video_id ) {

		$url = 'https://www.googleapis.com/youtube/v3/videos?id=' . $youtube_video_id . '&key='.GOOGLE_API_KEY.'&part=snippet,contentDetails,statistics,status'; 


		$response = wp_remote_get( $url, array( 'sslverify' => false ) );
		if ( is_wp_error( $response ) ) {

			$notice_type = 'error';
			$notice      = 'something went wrong. wp_remote_get: ' . $response->get_error_message();

			$this->send_notice( $notice, $notice_type, array( 'folder_id' => $this->args['folder_term_id'] ) );
		}
		$data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( empty( $data->items ) ) {

			$notice_type = 'error';
			$notice      = 'wrong url';

			$this->send_notice( $notice, $notice_type, array( 'folder_id' => $this->args['folder_term_id'] ) );

		}

		return $data;
	}

	private function save_video( $data_from_yt_api ) {

		if ( ! $data_from_yt_api ) {
			return;
		}

		$videoInfo = $this->get_video_info( $data_from_yt_api );

		$postID = $this->create_the_post( $this->args, $videoInfo );

		$this->grab_thumbnail( $postID, $videoInfo );

		$notice_type = 'success';
		$notice      = 'new video has been inserted';

		$this->send_notice( $notice, $notice_type, array( 'folder_id' => $this->args['folder_term_id'] ) );
	}

	private function get_video_info( $video ) {

		$videoInfo = array();

		// snippet info:
		$videoInfo['title']       = isset( $video->snippet->title ) ? $video->snippet->title : '';
		$videoInfo['desc']        = isset( $video->snippet->description ) ? $video->snippet->description : '';
		$videoInfo['channel']     = isset( $video->snippet->channelTitle ) ? $video->snippet->channelTitle : '';
		$videoInfo['association'] = $videoInfo['channel'];
		$videoInfo['categoryID']  = isset( $video->snippet->categoryId ) ? $video->snippet->categoryId : 0;
		$videoInfo['videoID']     = isset( $video->id ) ? $video->id : 0;
		$videoInfo['url']         = 'https://www.youtube.com/watch?v=' . $videoInfo['videoID'];

		$videoInfo['img'] = 0;
		// grab best standard thumbnail
		/*
		if ( isset( $video->snippet->thumbnails->default ) ) {              // ratio 4:3
			$videoInfo['img'] = $video->snippet->thumbnails->default->url;      // 120x90
		} */

		/*
		if ( isset( $video->snippet->thumbnails->medium ) ) {               // ratio 16:9
			$videoInfo['img'] = $video->snippet->thumbnails->medium->url;       // 320x180
		} */
		if ( isset( $video->snippet->thumbnails->standard ) ) {             // ratio 4:3
			$videoInfo['img'] = $video->snippet->thumbnails->standard->url;     // 640x480
		}
		/*
		// grab best high def img if available
		if ( isset( $video->snippet->thumbnails->high ) ) {             // ratio 4:3
			$videoInfo['img'] = $video->snippet->thumbnails->high->url;     // 480x360
		} */
		/*
		if ( isset( $video->snippet->thumbnails->maxres ) ) {               // ratio 16:9
			$videoInfo['img'] = $video->snippet->thumbnails->maxres->url;       // 1280x720
		} */

		// contentDetails info:
		$videoInfo['duration'] = '';
		if ( isset( $video->contentDetails->duration ) ) {
			$duration = new DateInterval( $video->contentDetails->duration );
			if ( $duration->h > 0 ) {
				$videoInfo['duration'] .= $duration->h . ':';
			}
			$videoInfo['duration'] .= sprintf( '%02s:%02s', $duration->i, $duration->s );
		}

		// statistics info:
		// not available on playlists :(
		$videoInfo['viewCount']     = isset( $video->statistics->viewCount ) ? $video->statistics->viewCount : 0;
		$videoInfo['likeCount']     = isset( $video->statistics->likeCount ) ? $video->statistics->likeCount : 0;
		$videoInfo['dislikeCount']  = isset( $video->statistics->dislikeCount ) ? $video->statistics->dislikeCount : 0;
		$videoInfo['favoriteCount'] = isset( $video->statistics->favoriteCount ) ? $video->statistics->favoriteCount : 0;
		$videoInfo['commentCount']  = isset( $video->statistics->commentCount ) ? $video->statistics->commentCount : 0;

		$videoInfo['videoSource'] = 'YouTube';
		return $videoInfo;
	}

	/*
	 * Fetches the thumbnail from the video site
	 * Saves thumbnail in the media library
	 * Sets the post_thumbnail
	 */

	public function grab_thumbnail( $postID, $videoInfo ) {

		if ( ! $postID || ! $videoInfo['title'] || ! $videoInfo['img'] ) {
			return 0;
		}

		if ( ! function_exists( 'download_url' ) || ! function_exists( 'media_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin' . '/includes/image.php';
			require_once ABSPATH . 'wp-admin' . '/includes/file.php';
			require_once ABSPATH . 'wp-admin' . '/includes/media.php';
		}
		$ext = pathinfo( $videoInfo['img'], PATHINFO_EXTENSION );
		$tmp = download_url( $videoInfo['img'] );
		if ( is_wp_error( $tmp ) ) {

			$notice_type = 'error';
			$notice      = $tmp->get_error_message();

			$this->send_notice( $notice, $notice_type, array( 'folder_id' => $this->args['folder_term_id'] ) );
		}

		$filename = 'gt-' . $postID . '-' . sanitize_file_name( $videoInfo['title'] ) . '.' . $ext;

		// removes any unsafe characters:
		$filename   = preg_replace( '/[^a-zA-Z0-9_\-.]/', '', $filename );
		$file_array = array(
			'name'     => $filename,
			'tmp_name' => $tmp,
		);
		$thumbID    = media_handle_sideload( $file_array, 0 );
		if ( is_wp_error( $thumbID ) ) {
			@unlink( $tmp );
		}
		$mediaID = set_post_thumbnail( $postID, $thumbID );
		if ( ! $mediaID ) {
			@unlink( $tmp );

			$notice_type = 'error';
			$notice      = 'something went wrong. set_post_thumbnail';

			$this->send_notice( $notice, $notice_type, array( 'folder_id' => $this->args['folder_term_id'] ) );

		}
		if ( file_exists( $tmp ) ) {
			@unlink( $tmp );
		}

		return $thumbID;
	}


	// end YouTube API------


	public function edit_video_content() {

		$check = check_ajax_referer( 'edit-video-content', '_wpnonce' );

		if ( $check == false ) {

			wp_send_json_success(
				array(
					'result' => 'failure',
				),
				200
			);

			die();
		}

		if ( isset( $_POST['ajaxrequest'] ) && $_POST['ajaxrequest'] === 'true' ) {

			// server response
			$post_id          = $_POST['post-id'];
			$post_new_content = urldecode( $_POST['post-content'] );
			$post_post_title  = urldecode( $_POST['post-title'] );

			$post               = get_post( $post_id );
			$post->post_content = $post_new_content;
			$post->post_title   = $post_post_title;

			$result = wp_update_post( $post );

			if ( $result == 0 || is_wp_error( $result ) ) {
				$result = 'failure';
			} else {
				$result = 'success';
			}

			wp_send_json_success(
				array(
					'result' => $result,
				),
				200
			);
		}

		wp_die();
	}

	public function untrash_videos() {

		$nonce = $_REQUEST['_wpnonce'];
		if ( ! wp_verify_nonce( $nonce, 'untrash-nonce' ) ) {

			$notice_type = 'error';
			$notice      = 'The HTTP 403 Forbidden response status code.';

			$this->send_notice( $notice, $notice_type, array( 'folder_id' => $_REQUEST['folderid'] ) );

		} else {
			$this->untrash_videos_execute( $_REQUEST );
		}
	}

	private function untrash_videos_execute( $query_args ) {

		if ( isset( $query_args['ids'] ) && ! empty( $query_args['ids'] ) ) {

			$posts_ids              = explode( ',', $query_args['ids'] );
			$restored_posts_count   = 0;
			$unrestored_posts_count = 0;
			$restored_posts_ids     = '';

			foreach ( $posts_ids as $post_id ) {
				
				$r = wp_publish_post( $post_id );
				++$restored_posts_count;
				$restored_posts_ids = $restored_posts_ids . ',' . $post_id;

			}

			if ( $restored_posts_ids != '' ) {
				$restored_posts_ids = substr( $restored_posts_ids, 1 );
			}
		}

		if ( $restored_posts_count > 0 ) {
			$notice_type = 'success';
			$notice      = $restored_posts_count . ' video(s) restored';
		}

		$this->send_notice( $notice, $notice_type, array( 'folder_id' => $query_args['folderid'] ) );
	}

	public function trash_videos() {
		
		$nonce = $_REQUEST['_wpnonce'];

		if ( ! wp_verify_nonce( $nonce, 'trash-nonce' ) ) {

			$notice_type = 'error';
			$notice      = 'The HTTP 403 Forbidden response status code.';

			$this->send_notice( $notice, $notice_type, array( 'folder_id' => $_REQUEST['folderid'] ) );

		} else {
			
			if ( array_key_exists( 'video-ids', $_REQUEST ) || array_key_exists( 'video-id', $_REQUEST ) ) {

				$this->trash_videos_execute( $_REQUEST );

			} else {

				$notice_type = 'error';
				$notice      = 'there is no videos in this folder';

				$this->send_notice( $notice, $notice_type, array( 'folder_id' => $_REQUEST['folderid'] ) );

			}
		}
	}

	private function trash_videos_execute( $query_args ) {
		
		if ( isset( $query_args['video-id'] ) && ! empty( $query_args['video-id'] ) ) {
			$posts_ids = array( $query_args['video-id'] );
		}

		if ( isset( $query_args['video-ids'] ) && ! empty( $query_args['video-ids'] ) ) {
			$posts_ids = $_REQUEST['video-ids'];
		}

		if ( $posts_ids ) {

			$trashed_posts     = 0;
			$untrashed_posts   = 0;
			$trashed_posts_ids = '';
			foreach ( $posts_ids as $post_id ) {

				$this->debug_file( '', $post_id );
				$r = wp_trash_post( $post_id ); 
			
				if ( $r === false || $r === null ) {
					++$untrashed_posts;
				} else {
					++$trashed_posts;
					$trashed_posts_ids = $trashed_posts_ids . ',' . $post_id;
				}
			}

			if ( $trashed_posts_ids != '' ) {
				$trashed_posts_ids = substr( $trashed_posts_ids, 1 );
			}
		}

		if ( $untrashed_posts > 0 ) {
			$notice_type = 'error';
			$notice      = 'something went wrong. ' . $untrashed_posts . ' invalid operations';
		} elseif ( $trashed_posts > 0 ) {
			$notice_type = 'success';
			$notice      = $trashed_posts . ' video(s) trashed';
		}

		$this->send_notice(
			$notice,
			$notice_type,
			array(
				'folder_id'         => $query_args['folderid'],
				'trashed_posts_ids' => $trashed_posts_ids,
			)
		);
	}

	private function send_notice( string $notice, string $notice_type, array $additional_args = null ) {

		wp_redirect(
			esc_url_raw(
				add_query_arg(
					array(

						// additional_args
						'folderid'          => $additional_args['folder_id'],
						'trashed_posts_ids' => $additional_args['trashed_posts_ids'],
						// notice args
						'notice-on-page'    => 'true',
						'notice'            => $notice,
						'notice-type'       => $notice_type,

					),
					admin_url( 'admin.php?page=' . $this->plugin_name . '-videos' )
				)
			)
		);

		die();
	}


} // END class Video_Blogster_Lite
