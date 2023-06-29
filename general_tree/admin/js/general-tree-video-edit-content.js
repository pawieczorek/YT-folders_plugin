jQuery(document).ready(function ($) {

    "use strict";

    /**
		 * The file is enqueued from class-general-tree-admin.php.
		 *
	 */


    //hides spinner
    document.getElementById("spinner-video-edit-content").style.visibility = 'hidden';

    //get folderid from url page=general-tree-video-edit-content
    //folderid needed for redirect back to Videos panel
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    const folderid = urlParams.get('folderid');

    // button "save changes" pressed
    $('#ajax-form').submit(function (event) {

        event.preventDefault();

        //activates spinner
        document.getElementById("spinner-video-edit-content").style.visibility = 'visible';

        //serializing form values
        let ajax_form_data = $("#ajax-form").serialize();
        ajax_form_data = ajax_form_data + '&ajaxrequest=true&submit=Submit+Form';


        // sent to class-general-tree-admin-video.php ==> edit_video_content()
        //ajax used, because too big data (video description) for admin.php (for URL as container)
        $.ajax({
            url: ajaxurl,
            type: 'post',
            data: ajax_form_data
        })
            .done(function (response) {
                const result = response.data.result;

                let notice;
                switch (result) {
                    case 'success':
                        notice = '&notice-on-page=true&notice-type=success&notice=edycja+zakończona+pomyślnie&post-data=930';
                        break;
                    case 'failure':
                        notice = '&notice-on-page=true&notice-type=error&notice=coś+poszło+nie+tak&post-data=930';
                        break;
                }

                const url = params.adminurl + '?page=general-tree-videos' + '&folderid=' + folderid + notice;
                window.open(url, "_self");

            })

            .fail(function (response) {

                let status_code = encodeURIComponent('The HTTP 403 Forbidden response status code.');
                let notice = '&notice-on-page=true&notice-type=error&notice=' + status_code + '&post-data=930';

                const url = params.adminurl + '?page=general-tree-videos' + '&folderid=' + folderid + notice;
                window.open(url, "_self");


            });

    });


    // ===============================================================================

    // button "cancel" pressed

    const cancel_button = document.getElementById("cancel-edit");
    cancel_button.addEventListener("click", (e) => {

        const folderid = urlParams.get('folderid');
        let url = params.adminurl + '?page=general-tree-videos' + '&folderid=' + folderid;

        window.open(url, "_self");


    });


});