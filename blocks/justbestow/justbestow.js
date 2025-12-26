(function (blocks, element) {
  const el = element.createElement;

  blocks.registerBlockType("justbestow/example-block", {
    title: "Just Bestow",
    icon: "block-default",
    category: "widgets",

    edit: function () {
      return el(
        "div",
        {
          className: "t2pw-widget-placeholder",
          style: {
            padding: "16px",
            border: "1px dashed #ccc",
            borderRadius: "6px",
            textAlign: "center",
          },
        },
        "Just Bestow widget will render on the frontend."
      );
    },

    save: function () {
      return null; 
    },
  });
})(window.wp.blocks, window.wp.element);
