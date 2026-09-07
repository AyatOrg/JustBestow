/**
 * Wires the Just Bestow widget into a FluentForm form when the
 * "Just Bestow Donation" field is present:
 *  - keeps the widget's donation amount/email/name in sync with FluentForm's
 *    own payment-amount, email, and name fields
 *  - shows a skeleton loader and disables the submit button until the
 *    widget finishes loading, re-enabling it once the card is ready
 *  - gates the donation charge behind FluentForm's OWN field validation,
 *    without touching or duplicating that validation ourselves
 *
 * How the gating works: FluentForm's client-side submit handler only ever
 * sends its "fluentform_submit" AJAX request AFTER its own required-field/
 * email/recaptcha validation has fully passed (see fluentform's bundled
 * assets/js/form-submission.js - the POST only happens inside the success
 * path of its internal validate() call; a validation failure throws and
 * never reaches the AJAX call at all). So instead of re-implementing or
 * racing that validation, we intercept jQuery.post() itself: when we see
 * FluentForm about to send that specific request for one of our forms, we
 * pause it, run the Just Bestow charge, and only forward the real request
 * (with the resulting transaction id attached) if the charge succeeds.
 * FluentForm's own required-field errors, error styling, and "submitting"
 * button state all keep working completely unmodified.
 */
(function () {
  'use strict';

  // formId (string) -> { charge: () => Promise<transactionId> }
  var jbForms = {};

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

  var FIELD_VALUE_MAX_LENGTH = 255;

  function collectFormFields(form) {
    var result = {};
    if (!window.jQuery) return result;

    window
      .jQuery(form)
      .find(':input')
      .not(':button, [type=submit], [type=hidden]')
      .each(function () {
        var el = window.jQuery(this);
        var name = el.attr('name');
        if (!name) return;
        if ((el.is(':checkbox') || el.is(':radio')) && !this.checked) return;

        var value = el.val();
        if (typeof value === 'string' && value.length > FIELD_VALUE_MAX_LENGTH) {
          value = value.slice(0, FIELD_VALUE_MAX_LENGTH);
        }

        if (Object.prototype.hasOwnProperty.call(result, name)) {
          result[name] = [].concat(result[name], value);
        } else {
          result[name] = value;
        }
      });

    return result;
  }

  function showWidgetMessage(wrapper, message) {
    var el = wrapper.querySelector('.jb-widget-notready-error');
    if (!el) {
      el = document.createElement('div');
      el.className = 'jb-widget-notready-error';
      wrapper.appendChild(el);
    }
    el.textContent = message;
  }

  function initWidgetField(wrapper) {
    var form = wrapper.closest('form');
    if (!form) return;

    var formId = form.dataset.form_id;
    var submitBtn = form.querySelector('.ff-btn-submit');
    var amountField = form.querySelector('.ff_payment_item');
    var emailField = form.querySelector('input[type="email"]');
    // FluentForm's "Name" field is composite - sub-inputs are named
    // "<root>[first_name]" / "<root>[last_name]".
    var firstNameField = form.querySelector('input[name$="[first_name]"]');
    var lastNameField = form.querySelector('input[name$="[last_name]"]');
    var isWidgetReady = false;

    // Disabled until the widget/card is ready - re-enabled in onReady() below.
    if (submitBtn) submitBtn.disabled = true;

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
          isWidgetReady = true;
          syncAmount();
          syncEmail();
          syncName();

          if (amountField) amountField.addEventListener('input', syncAmount);
          if (emailField) emailField.addEventListener('input', syncEmail);
          if (firstNameField) firstNameField.addEventListener('input', syncName);
          if (lastNameField) lastNameField.addEventListener('input', syncName);

          wrapper.classList.add('jb-ff-ready');
          if (submitBtn) submitBtn.disabled = false;
        });
      }
    );

    if (!formId) return;

    jbForms[formId] = {
      charge: function () {
        return new Promise(function (resolve, reject) {
          if (window.JustBestowWidget && typeof window.JustBestowWidget.setFormFields === 'function') {
            window.JustBestowWidget.setFormFields(collectFormFields(form));
          }

          if (!isWidgetReady || !window.JustBestowWidget || typeof window.JustBestowWidget.charge !== 'function') {
            var message = 'The payment form is still loading. Please wait a moment and try again.';
            showWidgetMessage(wrapper, message);
            reject(message);
            return;
          }

          if (typeof window.JustBestowWidget.isCardComplete === 'function' && !window.JustBestowWidget.isCardComplete()) {
            var cardMessage = 'Please enter your complete card details before donating.';
            showWidgetMessage(wrapper, cardMessage);
            reject(cardMessage);
            return;
          }

          window.JustBestowWidget.charge().then(
            function (result) {
              if (result && result.success) {
                resolve(result.transactionId);
              } else {
                reject(
                  (result && result.error) ||
                    'Your donation could not be processed. Please check your card details and try again.'
                );
              }
            },
            function () {
              reject('Your donation could not be processed. Please check your card details and try again.');
            }
          );
        });
      },
    };
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.jb-ff-widget').forEach(initWidgetField);
  });

  if (window.jQuery) {
    var $ = window.jQuery;
    var originalPost = $.post;

    $.post = function (url, data) {
      var entry = data && typeof data === 'object' && data.action === 'fluentform_submit' && jbForms[String(data.form_id)];

      if (!entry) {
        return originalPost.apply($, arguments);
      }

      var deferred = $.Deferred();

      entry.charge().then(
        function (transactionId) {
          // Appends payment_id onto the raw submitted field string. FluentForm itself
          // would normally drop any key that isn't one of the form's own declared
          // fields, but the PHP side (just-bestow.php, fluentform/insert_response_data)
          // reads this directly out of $_POST and saves it into the entry's response
          // data regardless - no form setup needed for this to show up as a
          // back-reference to the donation transaction.
          data.data += '&' + $.param({ payment_id: transactionId || '' });
          originalPost.call($, url, data).then(deferred.resolve, deferred.reject);
        },
        function (message) {
          deferred.reject({ responseText: message });
        }
      );

      return deferred.promise();
    };
  }
})();
