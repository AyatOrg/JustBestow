(function (blocks, element, blockEditor) {
  const el = element.createElement;
  const RichText = blockEditor.RichText;

  blocks.registerBlockType('t2pw/example-block', {
    title: 'Tap2Pay',
    icon: 'block-default',
    category: 'widgets',
    attributes: {
      title: {
        type: 'string',
        default: 'Tap2Pay',
      },
    },
    edit: function (props) {
      return el(
        'div',
        { className: 't2pw-widget' },
        el(RichText, {
          tagName: 'h3',
          value: props.attributes.title,
          onChange: function (value) {
            props.setAttributes({ title: value });
          },
          placeholder: 'Enter title...',
        }),
        el('p', {}, 'Tap2Pay Widget'),
      );
    },
    save: function (props) {
      return el(
        'div',
        { className: 't2pw-widget' },

        el('script', {
          dangerouslySetInnerHTML: {
            __html: `
(function () {
  var container = document.querySelector('.t2pw-widget');

  if (!container) {
    console.error('Container element not found.');
    showWidgetError('Something went wrong. Please refresh or try again later.');
    return;
  }

  if (!window.t2pw_settings || !t2pw_settings.fetchUrl) {
    console.error('Missing fetchUrl in t2pw_settings.');
    showWidgetError('Configuration error: fetch URL is missing.');
    return;
  }

  var url = t2pw_settings.fetchUrl;

  fetch(url)
    .then(function (res) {
      if (!res.ok) {
        throw new Error('HTTP error! Status: ' + res.status);
      }
      return res.text();
    })
    .then(function (html) {
      container.innerHTML = html;
    })
    .then(function () {
      executeScriptElements(container);
    })
    .catch(function (err) {
      console.error('Fetch error:', err);
      if (err.name === 'TypeError' && err.message.indexOf('Failed to fetch') !== -1) {
        showWidgetError('You are not allowed to use this feature. Please contact support.');
      } else {
        showWidgetError('You are not allowed to use this feature. Please contact support.');
      }
    });

  function showWidgetError(message) {
    var errorDiv = document.createElement('div');
    errorDiv.textContent = message;
    
    container.style.backgroundColor = '#fdecea';
    container.style.justifyContent = 'center';
    container.style.boxSizing = 'border-box';
    container.style.position = 'relative';
    container.style.alignItems = 'center';
    container.style.minHeight = '150px';
    container.style.borderRadius = '9px';
    container.style.minWidth = '150px';
    container.style.minHeight = '150px';
    container.style.minWidth = '150px';
    container.style.display = 'flex';
    
    container.appendChild(errorDiv);
  }

  function executeScriptElements(element) {
    var scripts = element.querySelectorAll('script');
    scripts.forEach(function (script) {
      var newScript = document.createElement('script');
      if (script.src) {
        newScript.src = script.src;
        newScript.async = false;
      } else {
        newScript.textContent = script.textContent;
      }
      document.head.appendChild(newScript).parentNode.removeChild(newScript);
    });
  }
})();
`,
          },
        }),
      );
    },
  });
})(window.wp.blocks, window.wp.element, window.wp.blockEditor || window.wp.editor);
