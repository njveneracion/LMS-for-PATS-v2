let customFont = null;
const qrCodeImageUrl =
  "https://tse1.mm.bing.net/th?id=OIP.NDKNbQ-I9ApLLVp-E6HSPwHaHa&pid=Api&P=0&h=180";
let qrCodeImage = new Image();
qrCodeImage.src = qrCodeImageUrl;

let draggingElement = null;
let offsetX, offsetY;
let isDragging = false;

const offScreenCanvas = document.createElement("canvas");
offScreenCanvas.width = 800;
offScreenCanvas.height = 600;
const offScreenCtx = offScreenCanvas.getContext("2d");

function updatePreview() {
  const canvas = document.getElementById("certificate_preview");
  const ctx = canvas.getContext("2d");
  const templateImageInput = document.getElementById("template_image");
  const templateImagePath = document.getElementById(
    "template_image_path"
  ).value;
  const fontFileInput = document.getElementById("font_file");
  const textColorInput = document.getElementById("text_color");
  const studentNameFontSizeInput = document.getElementById(
    "student_name_font_size"
  );
  const studentNameXInput = document.getElementById("student_name_x");
  const studentNameYInput = document.getElementById("student_name_y");
  const courseNameFontSizeInput = document.getElementById(
    "course_name_font_size"
  );
  const courseNameXInput = document.getElementById("course_name_x");
  const courseNameYInput = document.getElementById("course_name_y");
  const completionDateFontSizeInput = document.getElementById(
    "completion_date_font_size"
  );
  const completionDateXInput = document.getElementById("completion_date_x");
  const completionDateYInput = document.getElementById("completion_date_y");
  const qrCodeXInput = document.getElementById("qr_code_x");
  const qrCodeYInput = document.getElementById("qr_code_y");

  // Clear the off-screen canvas
  offScreenCtx.clearRect(0, 0, offScreenCanvas.width, offScreenCanvas.height);

  // Draw the template image on the off-screen canvas
  if (templateImageInput.files && templateImageInput.files[0]) {
    const img = new Image();
    img.onload = function () {
      offScreenCtx.drawImage(
        img,
        0,
        0,
        offScreenCanvas.width,
        offScreenCanvas.height
      );
      drawElements();
    };
    img.src = URL.createObjectURL(templateImageInput.files[0]);
  } else if (templateImagePath) {
    const img = new Image();
    img.onload = function () {
      offScreenCtx.drawImage(
        img,
        0,
        0,
        offScreenCanvas.width,
        offScreenCanvas.height
      );
      drawElements();
    };
    img.src = templateImagePath;
  } else {
    drawElements();
  }

  function drawElements() {
    const textColor = textColorInput.value;
    const studentNameFontSize = parseFloat(studentNameFontSizeInput.value);
    const studentNameX = parseFloat(studentNameXInput.value);
    const studentNameY = parseFloat(studentNameYInput.value);
    const courseNameFontSize = parseFloat(courseNameFontSizeInput.value);
    const courseNameX = parseFloat(courseNameXInput.value);
    const courseNameY = parseFloat(courseNameYInput.value);
    const completionDateFontSize = parseFloat(
      completionDateFontSizeInput.value
    );
    const completionDateX = parseFloat(completionDateXInput.value);
    const completionDateY = parseFloat(completionDateYInput.value);
    const qrCodeX = parseFloat(qrCodeXInput.value);
    const qrCodeY = parseFloat(qrCodeYInput.value);

    offScreenCtx.fillStyle = textColor;
    offScreenCtx.textAlign = "center";
    offScreenCtx.textBaseline = "middle";

    // Use the custom font if available
    if (customFont) {
      offScreenCtx.font = `${studentNameFontSize}pt ${customFont}`;
      offScreenCtx.fillText("Juan Dela Cruz", studentNameX, studentNameY);
      offScreenCtx.font = `${courseNameFontSize}pt ${customFont}`;
      offScreenCtx.fillText("TypeScript 101", courseNameX, courseNameY);
      offScreenCtx.font = `${completionDateFontSize}pt ${customFont}`;
      offScreenCtx.fillText(
        "August 20, 2024",
        completionDateX,
        completionDateY
      );
    } else {
      offScreenCtx.font = `${studentNameFontSize}pt Arial`;
      offScreenCtx.fillText("Juan Dela Cruz", studentNameX, studentNameY);
      offScreenCtx.font = `${courseNameFontSize}pt Arial`;
      offScreenCtx.fillText("TypeScript 101", courseNameX, courseNameY);
      offScreenCtx.font = `${completionDateFontSize}pt Arial`;
      offScreenCtx.fillText(
        "August 20, 2024",
        completionDateX,
        completionDateY
      );
    }

    // Draw the QR code image
    offScreenCtx.drawImage(qrCodeImage, qrCodeX, qrCodeY, 100, 100); // Adjust the size as needed

    // Copy the off-screen canvas to the main canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.drawImage(offScreenCanvas, 0, 0);
  }
}

function loadFont() {
  const fontFileInput = document.getElementById("font_file");
  if (fontFileInput.files && fontFileInput.files[0]) {
    const reader = new FileReader();
    reader.onload = function (e) {
      const font = new FontFace("customFont", e.target.result);
      font
        .load()
        .then(function (loadedFont) {
          document.fonts.add(loadedFont);
          customFont = "customFont";
          updatePreview();
        })
        .catch(function (error) {
          console.error("Error loading font:", error);
        });
    };
    reader.readAsArrayBuffer(fontFileInput.files[0]);
  } else {
    customFont = null;
    updatePreview();
  }
}

function startDrag(e) {
  const canvas = document.getElementById("certificate_preview");
  const rect = canvas.getBoundingClientRect();
  const x = e.clientX - rect.left;
  const y = e.clientY - rect.top;

  const studentNameX = parseFloat(
    document.getElementById("student_name_x").value
  );
  const studentNameY = parseFloat(
    document.getElementById("student_name_y").value
  );
  const courseNameX = parseFloat(
    document.getElementById("course_name_x").value
  );
  const courseNameY = parseFloat(
    document.getElementById("course_name_y").value
  );
  const completionDateX = parseFloat(
    document.getElementById("completion_date_x").value
  );
  const completionDateY = parseFloat(
    document.getElementById("completion_date_y").value
  );
  const qrCodeX = parseFloat(document.getElementById("qr_code_x").value);
  const qrCodeY = parseFloat(document.getElementById("qr_code_y").value);

  if (Math.abs(x - studentNameX) < 50 && Math.abs(y - studentNameY) < 20) {
    draggingElement = "studentName";
    offsetX = x - studentNameX;
    offsetY = y - studentNameY;
    canvas.style.cursor = "grabbing";
  } else if (Math.abs(x - courseNameX) < 50 && Math.abs(y - courseNameY) < 20) {
    draggingElement = "courseName";
    offsetX = x - courseNameX;
    offsetY = y - courseNameY;
    canvas.style.cursor = "grabbing";
  } else if (
    Math.abs(x - completionDateX) < 50 &&
    Math.abs(y - completionDateY) < 20
  ) {
    draggingElement = "completionDate";
    offsetX = x - completionDateX;
    offsetY = y - completionDateY;
    canvas.style.cursor = "grabbing";
  } else if (Math.abs(x - qrCodeX) < 50 && Math.abs(y - qrCodeY) < 50) {
    draggingElement = "qrCode";
    offsetX = x - qrCodeX;
    offsetY = y - qrCodeY;
    canvas.style.cursor = "grabbing";
  }
  isDragging = true;
}

function drag(e) {
  if (isDragging && draggingElement) {
    const canvas = document.getElementById("certificate_preview");
    const rect = canvas.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    if (draggingElement === "studentName") {
      document.getElementById("student_name_x").value = x - offsetX;
      document.getElementById("student_name_y").value = y - offsetY;
    } else if (draggingElement === "courseName") {
      document.getElementById("course_name_x").value = x - offsetX;
      document.getElementById("course_name_y").value = y - offsetY;
    } else if (draggingElement === "completionDate") {
      document.getElementById("completion_date_x").value = x - offsetX;
      document.getElementById("completion_date_y").value = y - offsetY;
    } else if (draggingElement === "qrCode") {
      document.getElementById("qr_code_x").value = x - offsetX;
      document.getElementById("qr_code_y").value = y - offsetY;
    }

    // Only update the relevant elements instead of the entire canvas
    requestAnimationFrame(updatePreview);
  }
}

function endDrag() {
  isDragging = false;
  draggingElement = null;
  const canvas = document.getElementById("certificate_preview");
  canvas.style.cursor = "default";
}

// Attach the loadFont function to the font file input change event
document.getElementById("font_file").addEventListener("change", loadFont);

// Attach drag events to the canvas
const canvas = document.getElementById("certificate_preview");
canvas.addEventListener("mousedown", startDrag);
canvas.addEventListener("mousemove", drag);
canvas.addEventListener("mouseup", endDrag);
canvas.addEventListener("mouseleave", endDrag);

// Initial preview update
updatePreview();
