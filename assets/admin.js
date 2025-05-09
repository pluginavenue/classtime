jQuery(document).ready(function ($) {
  var mediaUploader;

  $("#upload_instructor_image_button").click(function (e) {
    e.preventDefault();
    if (mediaUploader) {
      mediaUploader.open();
      return;
    }
    mediaUploader = wp.media.frames.file_frame = wp.media({
      title: "Select or Upload Instructor Image",
      button: {
        text: "Use this image",
      },
      multiple: false,
    });
    mediaUploader.on("select", function () {
      var attachment = mediaUploader.state().get("selection").first().toJSON();
      $("#classtime_instructor_image").val(attachment.id);
      $("#instructor-image-preview").attr("src", attachment.url).show();
    });
    mediaUploader.open();
  });

  $("#remove_instructor_image_button").click(function (e) {
    e.preventDefault();
    $("#classtime_instructor_image").val("");
    $("#instructor-image-preview").hide();
  });
});
