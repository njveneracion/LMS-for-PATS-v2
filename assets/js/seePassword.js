document.addEventListener("DOMContentLoaded", function () {
  const passwordInput = document.getElementById("floatingPassword");
  const passwordToggle = document.getElementById("passwordToggle");

  passwordToggle.addEventListener("click", function () {
    if (passwordInput.type === "password") {
      passwordInput.type = "text";
      passwordToggle.querySelector(".fa-eye").classList.add("d-none");
      passwordToggle.querySelector(".fa-eye-slash").classList.remove("d-none");
    } else {
      passwordInput.type = "password";
      passwordToggle.querySelector(".fa-eye").classList.remove("d-none");
      passwordToggle.querySelector(".fa-eye-slash").classList.add("d-none");
    }
  });
});
