/**
 * Once we have are successfully connected the stripe this methods
 * is used to removed the given stripe account details from the url.
 */
(function () {
  const url = new URL(window.location.href);
  ['_stripe_account_id', '_client_email', '_client_name', '_wpnonce', '_client_types', '_client_type'].forEach(
    param => {
      if (url.searchParams.has(param)) {
        url.searchParams.delete(param);
      }
    },
  );
  window.history.replaceState({}, document.title, url.pathname + url.search);
})();
