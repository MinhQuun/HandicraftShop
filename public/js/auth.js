document.getElementById("signUp").addEventListener("click", function () {
  document.getElementById("container").classList.add("right-panel-active");
});

document.getElementById("signIn").addEventListener("click", function () {
  document.getElementById("container").classList.remove("right-panel-active");
});

document.addEventListener("DOMContentLoaded", function () {
  var errorFields = document.querySelectorAll(
    ".field-validation-error input, .field-validation-error textarea"
  );
  if (errorFields.length > 0) {
    errorFields[0].focus();
  }
});
