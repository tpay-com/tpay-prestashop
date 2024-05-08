var superCheckoutConfirm = document.getElementById(
  "supercheckout_confirm_order"
);

function isInSupercheckout() {
  return superCheckoutConfirm === null ? false : true;
}

function onClickDisabled(e: Event) {
  e.preventDefault();
  e.stopPropagation();
}

const enableSupercheckoutButtonOverride = () => {
  superCheckoutConfirm.addEventListener("click", onClickDisabled, {
    capture: true,
  });
};

const disableSupercheckoutButtonOverride = () => {
  superCheckoutConfirm.removeEventListener("click", onClickDisabled, {
    capture: true,
  });
};

function disableSupercheckoutConfirmButton() {
  enableSupercheckoutButtonOverride();
  superCheckoutConfirm.setAttribute("disabled", "true");
}

function enableSupercheckoutConfirmButton() {
  disableSupercheckoutButtonOverride();
  superCheckoutConfirm.removeAttribute("disabled");
}

export {
  isInSupercheckout,
  enableSupercheckoutConfirmButton,
  disableSupercheckoutConfirmButton,
};
