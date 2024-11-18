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

document.getElementById("loginInput").addEventListener("input", function () {
  const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
  const helperText = document.getElementById("loginInputHelp");

  if (this.value.trim() !== "") {
    helperText.style.display = "block";
    if (emailRegex.test(this.value)) {
      helperText.style.color = "green";
      helperText.textContent = "Valid email address";
    } else if (usernameRegex.test(this.value)) {
      helperText.style.color = "green";
      helperText.textContent = "Valid username";
    } else {
      helperText.style.color = "red";
      helperText.textContent = "Invalid email or username";
    }
  } else {
    helperText.style.display = "none";
  }
});

document
  .getElementById("floatingPassword")
  .addEventListener("input", function () {
    const passwordRegex =
      /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    if (this.value.trim() !== "") {
      validateInput(this, passwordRegex, "passwordHelp");
      const helperText = document.getElementById("passwordHelp");
      if (!passwordRegex.test(this.value)) {
        helperText.textContent =
          "Password must contain at least 8 characters, including uppercase, lowercase, number, and special character";
      }
    } else {
      document.getElementById("passwordHelp").style.display = "none";
    }
  });

document.getElementById("loginForm").addEventListener("submit", function (e) {
  const loginInput = document.getElementById("loginInput");
  const password = document.getElementById("floatingPassword");
  const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
  const passwordRegex =
    /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
  const formErrors = document.getElementById("formErrors");
  let errors = [];

  if (
    loginInput.value.trim() === "" ||
    !(emailRegex.test(loginInput.value) || usernameRegex.test(loginInput.value))
  ) {
    errors.push("Please enter a valid email or username.");
  }

  if (password.value.trim() === "" || !passwordRegex.test(password.value)) {
    errors.push("Please enter a valid password.");
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
