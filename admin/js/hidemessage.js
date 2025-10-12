//yo file signup ma pani index(loginform) ma same nai use hunxa

// this is code of jQuery which automatically hides the success and error messages after some time

// html document purai load nahunjel samma wait grney
$(document).ready(function () {
  //successMessage id ko element cha ki chaina bhanera check grney
  if ($("#successMessage").length) {
    //yedi xa baney time set grney 3 seconds=3000 millisecondsnhide grna
    //time set grnu taaki user le padna sakunn k raixa k vayo
    setTimeout(() => $("#successMessage").fadeOut("slow"), 2000);
  }
  if ($("#errorMessage").length) {
    setTimeout(() => $("#errorMessage").fadeOut("slow"), 2000);
  }
});
