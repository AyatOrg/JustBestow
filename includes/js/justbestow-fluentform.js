/**
 * Wires the Just Bestow widget into a FluentForm form when the
 * "Just Bestow Donation" field is present:
 *  - keeps the widget's donation amount/email in sync with FluentForm's
 *    own payment-amount and email fields
 *  - disables the form's submit button + shows a loader until the widget
 *    finishes loading
 *  - intercepts the form's submit so one click charges the card via the
 *    widget first, then lets the normal FluentForm submission continue
 */
(function () {
  'use strict';

  function waitFor(check, cb, timeoutMs) {
    var waited = 0;
    var interval = 150;
    var timer = setInterval(function () {
      waited += interval;
      if (check()) {
        clearInterval(timer);
        cb();
      } else if (waited >= (timeoutMs || 20000)) {
        clearInterval(timer);
      }
    }, interval);
  }

  function setSubmitState(submitBtn, disabled, label) {
    if (!submitBtn) return;
    submitBtn.disabled = disabled;
    if (label) {
      submitBtn.dataset.jbOriginalText = submitBtn.dataset.jbOriginalText || submitBtn.textContent;
      submitBtn.textContent = label;
    } else if (submitBtn.dataset.jbOriginalText) {
      submitBtn.textContent = submitBtn.dataset.jbOriginalText;
    }
  }

  function initWidgetField(wrapper) {
    var form = wrapper.closest('form');
    if (!form) return;

    var submitBtn = form.querySelector('.ff-btn-submit');
    var amountField = form.querySelector('.ff_payment_item');
    var emailField = form.querySelector('input[type="email"]');
    // FluentForm's "Name" field is composite - sub-inputs are named
    // "<root>[first_name]" / "<root>[last_name]".
    var firstNameField = form.querySelector('input[name$="[first_name]"]');
    var lastNameField = form.querySelector('input[name$="[last_name]"]');

    setSubmitState(submitBtn, true);

    function syncAmount() {
      if (amountField && window.JustBestowWidget) {
        window.JustBestowWidget.setAmount(amountField.value);
      }
    }

    function syncEmail() {
      if (emailField && window.JustBestowWidget) {
        window.JustBestowWidget.setEmail(emailField.value);
      }
    }

    function syncName() {
      if (!window.JustBestowWidget) return;
      if (firstNameField) window.JustBestowWidget.setFirstName(firstNameField.value);
      if (lastNameField) window.JustBestowWidget.setLastName(lastNameField.value);
    }

    waitFor(
      function () {
        return window.JustBestowWidget && typeof window.JustBestowWidget.onReady === 'function';
      },
      function () {
        // Force the widget straight to its payment step immediately (this also
        // triggers the Stripe card element to mount) - don't wait for onReady
        // first, since onReady only resolves once that mount finishes.
        window.JustBestowWidget.setEmbeddedMode();

        window.JustBestowWidget.onReady(function () {
          syncAmount();
          syncEmail();
          syncName();

          if (amountField) amountField.addEventListener('input', syncAmount);
          if (emailField) emailField.addEventListener('input', syncEmail);
          if (firstNameField) firstNameField.addEventListener('input', syncName);
          if (lastNameField) lastNameField.addEventListener('input', syncName);

          wrapper.classList.add('jb-ff-ready');
          setSubmitState(submitBtn, false);
        });
      }
    );

    form.addEventListener(
      'submit',
      function (e) {
        if (form.dataset.jbCharged === 'true') {
          return; // already charged, let FluentForm's own submission through
        }

        e.preventDefault();
        e.stopPropagation();

        if (!window.JustBestowWidget || typeof window.JustBestowWidget.charge !== 'function') {
          return; // widget never loaded; nothing to charge, keep submission blocked
        }

        setSubmitState(submitBtn, true, 'Processing donation…');

        window.JustBestowWidget.charge().then(
          function (result) {
            if (result && result.success) {
              var hidden = document.createElement('input');
              hidden.type = 'hidden';
              hidden.name = 'justbestow_transaction_id';
              hidden.value = result.transactionId || '';
              form.appendChild(hidden);

              form.dataset.jbCharged = 'true';
              if (form.requestSubmit) {
                form.requestSubmit();
              } else {
                form.submit();
              }
            } else {
              setSubmitState(submitBtn, false);
            }
          },
          function () {
            setSubmitState(submitBtn, false);
          }
        );
      },
      true
    );
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.jb-ff-widget').forEach(initWidgetField);
  });
})();
