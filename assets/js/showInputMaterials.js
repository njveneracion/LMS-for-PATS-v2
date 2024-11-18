function showInputMaterial() {
  var selectedFileType = document.getElementById("fileType").value;
  var fileInput = document.getElementById("fileInput");

  if (selectedFileType === "none") {
    fileInput.style.display = "none";
  } else {
    fileInput.style.display = "block";
    if (selectedFileType === "video") {
      fileInput.accept = "video/*";
    } else if (selectedFileType === "file") {
      fileInput.accept = ".pdf, .docx, .txt, .pptx"; // Add file extensions you want to accept
    } else if (selectedFileType === "image") {
      fileInput.accept = "image/*";
    }
  }
}
