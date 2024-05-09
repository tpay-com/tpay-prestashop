function isInSupercheckout() {
  const superCheckoutConfirm = document.getElementById(
    "supercheckout_confirm_order"
  );
  return superCheckoutConfirm === null ? false : true;
}

export { isInSupercheckout };
