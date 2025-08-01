/**
 * MCQHome Theme Customizer JS
 *
 * @package MCQHome
 * @since 1.0.0
 */

(function ($) {
  "use strict";

  // Site title and description
  wp.customize("blogname", function (value) {
    value.bind(function (to) {
      $(".site-title a").text(to);
    });
  });

  wp.customize("blogdescription", function (value) {
    value.bind(function (to) {
      $(".site-description").text(to);
    });
  });

  // Header text color
  wp.customize("header_textcolor", function (value) {
    value.bind(function (to) {
      if ("blank" === to) {
        $(".site-title, .site-description").css({
          clip: "rect(1px, 1px, 1px, 1px)",
          position: "absolute",
        });
      } else {
        $(".site-title, .site-description").css({
          clip: "auto",
          position: "relative",
        });
        $(".site-title a, .site-description").css({
          color: to,
        });
      }
    });
  });

  // Primary color
  wp.customize("mcqhome_primary_color", function (value) {
    value.bind(function (to) {
      $("head").find("#mcqhome-primary-color-css").remove();
      $("head").append(
        '<style id="mcqhome-primary-color-css">:root { --mcqhome-primary-color: ' +
          to +
          "; }</style>"
      );
    });
  });

  // Secondary color
  wp.customize("mcqhome_secondary_color", function (value) {
    value.bind(function (to) {
      $("head").find("#mcqhome-secondary-color-css").remove();
      $("head").append(
        '<style id="mcqhome-secondary-color-css">:root { --mcqhome-secondary-color: ' +
          to +
          "; }</style>"
      );
    });
  });

  // Body font
  wp.customize("mcqhome_body_font", function (value) {
    value.bind(function (to) {
      $("head").find("#mcqhome-body-font-css").remove();
      $("head").append(
        '<style id="mcqhome-body-font-css">:root { --mcqhome-body-font: "' +
          to +
          '", sans-serif; }</style>'
      );
    });
  });

  // Heading font
  wp.customize("mcqhome_heading_font", function (value) {
    value.bind(function (to) {
      $("head").find("#mcqhome-heading-font-css").remove();
      $("head").append(
        '<style id="mcqhome-heading-font-css">:root { --mcqhome-heading-font: "' +
          to +
          '", sans-serif; }</style>'
      );
    });
  });

  // Container width
  wp.customize("mcqhome_container_width", function (value) {
    value.bind(function (to) {
      $("head").find("#mcqhome-container-width-css").remove();
      $("head").append(
        '<style id="mcqhome-container-width-css">:root { --mcqhome-container-width: ' +
          to +
          "px; }</style>"
      );
    });
  });
})(jQuery);
