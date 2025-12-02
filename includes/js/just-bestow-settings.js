/* The script is used to generate the OAuth link for Stripe */
(function () {
  function base64Encode(str) {
    return btoa(unescape(encodeURIComponent(str)))
      .replace(/\+/g, '-')
      .replace(/\//g, '_')
      .replace(/=+$/, '');
  }

  function redirectToStripe(type) {
    const baseUrl = window.JB_STRIPE_VARS.stripe_url;
    const mode = window.JB_STRIPE_VARS.mode;
    const nonce = window.JB_STRIPE_VARS.nonce;

    if (!baseUrl) {
      alert('Stripe OAuth URL missing');
      return;
    }

    const redirectUrl = new URL(window.location.href);
    redirectUrl.searchParams.set('_wpnonce', encodeURIComponent(nonce));
    const ru = base64Encode(redirectUrl);
    const m = base64Encode(mode);
    const t = base64Encode(type);

    const sep = baseUrl.includes('?') ? '&' : '?';
    const finalUrl = `${baseUrl}${sep}_ru=${ru}&_m=${m}&_t=${t}`;

    window.location.href = finalUrl;
  }

  document.addEventListener('DOMContentLoaded', function () {
    const nonProfitBtn = document.getElementById('connect-nonprofit-btn');
    const profitBtn = document.getElementById('connect-profit-btn');

    if (nonProfitBtn) nonProfitBtn.addEventListener('click', () => redirectToStripe('non-profit'));

    if (profitBtn) profitBtn.addEventListener('click', () => redirectToStripe('profit'));
  });
})();
