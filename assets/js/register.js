// JavaScript to toggle the icon rotation for each collapsible section
document
  .querySelectorAll('[data-bs-toggle="collapse"]')
  .forEach(function (element) {
    var targetId = element.getAttribute("data-bs-target");
    var icon = element.querySelector(".collapse-icon");
    var collapseElement = document.querySelector(targetId);

    // Set initial state based on default collapse visibility
    if (collapseElement.classList.contains("show")) {
      icon.classList.add("rotate");
    } else {
      icon.classList.remove("rotate");
    }

    // Add event listeners to handle rotation on expand/collapse
    collapseElement.addEventListener("show.bs.collapse", function () {
      icon.classList.add("rotate"); // Add rotation when expanding
    });

    collapseElement.addEventListener("hide.bs.collapse", function () {
      icon.classList.remove("rotate"); // Remove rotation when collapsing
    });
  });

document.getElementById("roleSelect").addEventListener("change", function () {
  const selectedRole = this.value;
  const roleInput = document.getElementById("role");
  const studentForm = document.getElementById("studentForm");
  const instructorForm = document.getElementById("instructorForm");

  // Update the hidden role input field
  roleInput.value = selectedRole;

  // Show or hide forms based on the selected role
  if (selectedRole === "student") {
    studentForm.style.display = "block";
    instructorForm.style.display = "none";
  } else {
    studentForm.style.display = "none";
    instructorForm.style.display = "block";
  }
});

function validateInput(input, regex, helperId) {
  const helperText = document.getElementById(helperId);
  if (input.value.trim() !== "") {
    helperText.style.display = "block";
    if (regex.test(input.value)) {
      helperText.style.color = "green";
      helperText.textContent = "Valid input";
      return true;
    } else {
      helperText.style.color = "red";
      helperText.textContent = "Invalid input";
      return false;
    }
  } else {
    helperText.style.display = "none";
    return false;
  }
}

document.getElementById("username").addEventListener("input", function () {
  const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
  if (this.value.trim() !== "") {
    if (!validateInput(this, usernameRegex, "usernameHelp")) {
      document.getElementById("usernameHelp").textContent =
        "Username must be 3-20 characters long and can only contain letters, numbers, and underscores.";
    }
  } else {
    document.getElementById("usernameHelp").style.display = "none";
  }
});

document.getElementById("password").addEventListener("input", function () {
  const passwordRegex =
    /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
  if (this.value.trim() !== "") {
    if (!validateInput(this, passwordRegex, "passwordHelp")) {
      document.getElementById("passwordHelp").textContent =
        "Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.";
    }
  } else {
    document.getElementById("passwordHelp").style.display = "none";
  }
});

document.getElementById("fullname").addEventListener("input", function () {
  const fullnameRegex = /^[a-zA-Z ]{2,50}$/;
  if (this.value.trim() !== "") {
    if (!validateInput(this, fullnameRegex, "fullnameHelp")) {
      document.getElementById("fullnameHelp").textContent =
        "Full name must be 2-50 characters long and can only contain letters and spaces.";
    }
  } else {
    document.getElementById("fullnameHelp").style.display = "none";
  }
});

document.getElementById("email").addEventListener("input", function () {
  const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  if (this.value.trim() !== "") {
    if (!validateInput(this, emailRegex, "emailHelp")) {
      document.getElementById("emailHelp").textContent =
        "Please enter a valid email address.";
    }
  } else {
    document.getElementById("emailHelp").style.display = "none";
  }
});

// Form submission validation
document
  .getElementById("registrationForm")
  .addEventListener("submit", function (e) {
    const username = document.getElementById("username");
    const password = document.getElementById("password");
    const fullname = document.getElementById("fullname");
    const email = document.getElementById("email");
    const formErrors = document.getElementById("formErrors");

    const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
    const passwordRegex =
      /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    const fullnameRegex = /^[a-zA-Z ]{2,50}$/;
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

    let errors = [];

    if (!usernameRegex.test(username.value)) {
      errors.push("Please enter a valid username.");
    }
    if (!passwordRegex.test(password.value)) {
      errors.push("Please enter a valid password.");
    }
    if (!fullnameRegex.test(fullname.value)) {
      errors.push("Please enter a valid full name.");
    }
    if (!emailRegex.test(email.value)) {
      errors.push("Please enter a valid email address.");
    }

    if (errors.length > 0) {
      e.preventDefault();
      formErrors.innerHTML = errors
        .map((error) => `<div>${error}</div>`)
        .join("");
      formErrors.style.display = "block";
    } else {
      formErrors.style.display = "none";
    }
  });
