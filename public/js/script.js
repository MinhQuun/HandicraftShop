//--------------------------------------Login / Register--------------------------------------------
// Hiển thị Login
function showLogin() {
  document.getElementById("login").style.display = "flex";
}

// Ẩn Login
function closeLogin() {
  const loginContainer = document.getElementById("login");
  loginContainer.classList.add("ani_out");

  loginContainer.addEventListener(
    "animationend",
    function handler() {
      loginContainer.style.display = "none";
      loginContainer.classList.remove("ani_out");
      loginContainer.removeEventListener("animationend", handler); // Xóa listener để tránh lặp
    },
    { once: true }
  ); // { once: true } đảm bảo listener chỉ chạy một lần
}

function signup() {
  var x = document.getElementById("log_form");
  var y = document.getElementById("reg_form");

  x.style.left = "-500px";
  y.style.left = "0px";
}

function signin() {
  var x = document.getElementById("log_form");
  var y = document.getElementById("reg_form");

  x.style.left = "0px";
  y.style.left = "500px";
}

// Dropdown hover mượt cho 2 cấp
// $(function () {
//   // Chỉ áp cho cấp 2
//   $(".dropdown-menu > li > ul.sub-menu > li")
//     .on("mouseenter", function () {
//       $(this).children("ul.sub-menu").stop(true, true).fadeIn(120);
//     })
//     .on("mouseleave", function () {
//       $(this).children("ul.sub-menu").stop(true, true).fadeOut(120);
//     });
// });
