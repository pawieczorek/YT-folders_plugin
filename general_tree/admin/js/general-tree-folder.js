

jQuery(document).ready(function ($) {

	"use strict";
	/**
		 * The file is enqueued from class-general-tree-admin.php.
		 *
	 */


	// ========on load=========================================

	show_actions_for_root_folder();

	$(".folder-name").mouseover(function () {
		$(".folder-name").css("cursor", "pointer");
	});

	$(".spinner-add-folder").hide();
	$(".spinner-delete-folder").hide();
	$(".spinner-rename-folder").hide();
	$(".spinner-add-folder-thumbnail").hide();

	$("input.folder-id-to-delete").val(my_params.root_folder_id);
	$(".folder-name.button")[0].innerHTML = "home";
	$("p.parent-term-name").text("home");
	$(".fake-input").text("home's subfolders");

	if (!my_params.root_folder_has_subfolders) {
		$(".form-delete-folder").hide();
	}

	$("#submit-add-folder-thumbnail").prop('disabled', true);


	// ========activates spinners on inputs' submits========================================

	$("#submit-add-folder").click((e) => {
		let value = $(".new-folder-name").val();
		if (value != null && value != "") {
			$(".spinner-add-folder").show();
		}
	});

	$("#submit-delete-folder").click((e) => {
		let value = $(".folder-id-to-delete").val();
		if (value != null && value != "") {
			$(".spinner-delete-folder").show();
		}
	});

	$("#submit-rename-folder").click((e) => {
		let value = $(".folder-to-rename").val();
		if (value != null && value != "") {
			if ($("input.folder-id-to-rename").val() != "") {
				$(".spinner-rename-folder").show();
			}
		}
	});

	$("#submit-add-folder-thumbnail").click((e) => {
		let value = $(".folder-id-for-thumbnail").val();
		if (value != null && value != "") {
			$(".spinner-add-folder-thumbnail").show();
		}
	});


	//-----displays folders names on input forms-----------------------------------------

	$(".folder-name").click(function () {
		const x = $(this).parent();
		const elmId = x.attr("id");

		$("input.parent-folder-id:text").val(elmId);
		$("input.folder-id-to-delete:text").val(elmId);
		$("input.folder-id-to-rename:text").val(elmId);
		$("input.folder-id-for-thumbnail:text").val(elmId);

		const text = $(this).text();

		$("p.parent-term-name").text(text);
		$("p.folder-id-to-delete").text(text);
		$("p.folder-id-to-rename").text(text);
		$("p.folder-id-for-thumbnail").text(text);
		$("p.fake-input").text(text);

		$(".parent-folder-id").val(elmId);

		$(".file-name").text('No file yet');

		$(".upload-file-button").prop('disabled', false);
		$("#submit-add-folder-thumbnail").prop('disabled', true);
		$("#folder-thumbnail").attr("src", my_params.spinner_url);


		//loads folder's current thumbnail
		let image = new wp.api.models.Media();

		image.fetch({ data: { "per_page": 100, "_fields": "id, title, source_url" } }).then((response) => {
			response.forEach(myFunction);

			function myFunction(item) {
				if (item.title.rendered === 'thumbnail-' + elmId) {
					$("#folder-thumbnail").attr("src", item.source_url);
				}
			}
		});//then

	});


	// =====displays confirm message on delete action=====================================

	$('#delete-folder-form').submit(function () {
		let agree = confirm("Subfolders will be also deleted.");
		if (agree)
			return true;
		else
			return false;
	})

	//----------dispatches file upload & displays uploaded file name--------------

	const fileInput = document.getElementsByClassName('custom-file-input');

	$(".upload-file-button").click(function () {
		fileInput[0].click();
	});

	$(".custom-file-input").on('change', function () {
		const selectedFiles = fileInput[0].files;
		$(".file-name").text(selectedFiles[0]['name']);

		$(".upload-file-button").prop('disabled', true);
		$("#submit-add-folder-thumbnail").prop('disabled', false);
		$("#folder-thumbnail").attr("src", "");
	});

	// ==========actions for root folder and other folder=======================================

	$(".folder-name").click((e) => {
		// console.log($(".folder-name.button").innerHTML);
		if (e.target.innerHTML.includes("home")) {
			show_actions_for_root_folder();
		}
		else {
			show_actions_for_other_folders();
		}
	});

	function show_actions_for_root_folder() {

		$(".form-add-thumbnail-folder").hide();
		$(".form-rename-folder").hide();

		$("p.folder-id-to-delete").text("home's subfolders");
		$(".form-delete-folder input.button").val("delete subfolders");
	}

	function show_actions_for_other_folders() {

		$(".form-add-thumbnail-folder").show();
		$(".form-rename-folder").show();
		$(".form-delete-folder input.button").val("delete folder");

	}

	// =====================================================================

});


