jQuery(document).ready(function ($) {

  "use strict";
  /**
       * The file is enqueued from inc/admin/class-admin.php.
   */

  // spinners

  let spiner = document.getElementById("spinner-add-new-video");
  spiner.style.visibility = 'hidden';

  let spiner1 = document.getElementById("spinner-trash-checked-videos");
  spiner1.style.visibility = 'hidden';

  const add_new_video_button = document.getElementById("add-video");
  add_new_video_button.addEventListener("click", (e) => {
    spiner.style.visibility = 'visible';
  });

  const trash_checked_videos_button = document.getElementById("video-input");
  trash_checked_videos_button.addEventListener("click", (e) => {
    spiner1.style.visibility = 'visible';
  });

  // bulk trash button disabled/enabled

  const videosTable = document.getElementById("videos-table");

  videosTable.addEventListener('input', (event) => {
    
    if (event.target.checked == true) {
      trash_checked_videos_button.disabled = false;
    }
    else {
      let checked_elements = document.querySelectorAll(".info-for-js:checked");
      console.log(checked_elements.length);

      if (checked_elements.length == 0) {
        trash_checked_videos_button.disabled = true;
      }
      else {
        trash_checked_videos_button.disabled = false;
      }
    }
  });//if

 // add new video button disabled/enabled

  const add_new_video_form = document.getElementById("add-new-video-form");

  add_new_video_form.addEventListener('input', (event) => {

    if (event.target.value != '') {
      add_new_video_button.disabled = false;
    }
    else {
       add_new_video_button.disabled = true;
     }
  });

//red colored while deleting video

  document.querySelectorAll('a.video-delete').forEach(link => {
    link.addEventListener('click', (e) => {

      let id = "title-" + e.target.id;
      $("#" + id).css("background-color", "red");
    
    });
  });

});//jQuery