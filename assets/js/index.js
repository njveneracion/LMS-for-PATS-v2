AOS.init({
  offset: 120,
  delay: 0,
  duration: 1200,
  easing: "ease",
  once: false,
  mirror: false,
  anchorPlacement: "top-bottom",
});

document.addEventListener("DOMContentLoaded", function () {
  const sections = document.querySelectorAll("section[id]");
  const navItems = document.querySelectorAll(".navbar-nav .nav-link");

  function highlightNavigation() {
    let scrollY = window.pageYOffset;
    let pageBottom = scrollY + window.innerHeight;
    let documentHeight = document.documentElement.scrollHeight;

    sections.forEach((current, index) => {
      const sectionHeight = current.offsetHeight;
      const sectionTop = current.offsetTop - 100; // Adjust for header height
      const sectionId = current.getAttribute("id");

      if (
        (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) ||
        (index === sections.length - 1 && pageBottom >= documentHeight - 50) // Check if it's the last section and we're near the bottom
      ) {
        navItems.forEach((item) => {
          item.classList.remove("active");
          if (item.getAttribute("href").substring(1) === sectionId) {
            item.classList.add("active");
          }
        });
      }
    });
  }

  window.addEventListener("scroll", highlightNavigation);
  highlightNavigation(); // Call once to set initial state

  // Add click event listeners to smooth scroll to sections
  navItems.forEach((item) => {
    item.addEventListener("click", function (e) {
      e.preventDefault();
      const targetId = this.getAttribute("href").substring(1);
      const targetSection = document.getElementById(targetId);
      if (targetSection) {
        targetSection.scrollIntoView({ behavior: "smooth" });
      }
    });
  });
});
