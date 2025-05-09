document.addEventListener("DOMContentLoaded", function () {
  const classtimeBox = document.getElementById("classtime_details");
  const titleWrap = document.getElementById("titlediv"); // title wrapper

  if (classtimeBox && titleWrap) {
    // Insert Class Details box right after the title field
    titleWrap.parentNode.insertBefore(classtimeBox, titleWrap.nextSibling);
  }
});
