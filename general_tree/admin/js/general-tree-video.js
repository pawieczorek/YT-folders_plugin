jQuery(document).ready(function ($) {

  "use strict";
  /**
     * The file is enqueued from class-general-tree-admin.php.
     *
   */

  
  //all for folders's tree actions

  $(".folder-name").click(function () {

    let parent = $(this).parent();
    let folder_id = parent.attr("id");

    let url = my_params.admin_url + '?page=general-tree-videos' + '&folderid=' + folder_id;
    window.open(url, "_self");

  });

  $(".folder-name").mouseover(function () {

    $(".folder-name").css("cursor", "pointer");

  });

  $(".folder-name.button")[0].innerHTML = "home";
  

  //-----------------------------------------------------------------------------




});