// invester js
// form email valicdation using pristine js
document.addEventListener("DOMContentLoaded", function () {
  var emailForm = document.querySelector(".verification_form");
  var pristine = new Pristine(emailForm, {
    errorTextTag: "p",
    errorTextClass: "text-danger text-left px-4 p-2",
  });
  emailForm.addEventListener("submit", function (e) {
    e.preventDefault();
    var valid = pristine.validate();
    if (valid) {
      // post data to server or do something
      emailForm.submit();
    }
  });

  // add change event on .selectYearInput
  function filterCards(val) {
    var selectedYear = val;
    var getAllCards = Array.from(document.querySelectorAll(".pdfs-content .card"));
    // filter all cards data attribute year matching the selectYear
    var filteredCards = getAllCards.filter(function (card) {
      if (card.dataset.year == selectedYear) {
        card.style.display = "flex";
      } else {
        card.style.display = "none";
      }
    });
  }
  var selectYearInput = document.querySelector(".selectYearInput");
  selectYearInput.addEventListener("change", function (e) {
    filterCards(e.target.value);
  });
  filterCards(selectYearInput.value);
});
