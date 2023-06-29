<?php
class General_Tree_Admin_Notices
{
	use my__debug;

	public function print_plugin_admin_notices(){
		
		$notice_on_page = isset($_REQUEST["notice-on-page"])
			? $_REQUEST["notice-on-page"]
			: "";

		$notice_type = isset($_REQUEST["notice-type"])
			? $_REQUEST["notice-type"]
			: "";

		$notices_types = ["success", "info", "warning", "error"];

		if ($notice_on_page === "true") {
			if ($notice_type && in_array($_REQUEST["notice-type"], $notices_types)) {
				$this->html_fragment($_REQUEST["notice-type"]);
			} else {
				return;
			}
		} else {
			return;
		}
	} //function print_plugin_admin_notices

	private function html_fragment($notice_type){

		$notice = isset($_REQUEST["notice"]) ? $_REQUEST["notice"] : "none";
		$trashed_posts_ids = isset($_REQUEST["trashed_posts_ids"])
			? $_REQUEST["trashed_posts_ids"]
			: "none";
		$folderid = isset($_REQUEST["folderid"]) ? $_REQUEST["folderid"] : "none";

		$html = '<div class="notice notice-' . $notice_type . ' is-dismissible">';

		if ($notice != "none") {
			$html .= "<pre class='note'>" . htmlspecialchars(print_r($notice, true)) . "</pre>";
		}

		if ($trashed_posts_ids != "none" && $folderid != "none") {
			$nonce = wp_create_nonce("untrash-nonce");
			$args =
				"?action=untrash_videos&folderid=" .
				$folderid .
				"&ids=" .
				$trashed_posts_ids .
				"&_wpnonce=" .
				$nonce;

			$html .=
				'<a href="' .
				esc_url(admin_url("admin-post.php")) .
				$args .
				'">undo</a>';
		}
		$html .= "</div>";

		echo $html;
	}
} //class General_Tree_Admin_Notices

?>
