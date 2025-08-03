/**
 * MCQHome Theme Customizer Live Preview
 *
 * This file handles live preview functionality for the WordPress Customizer
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

  // Primary Color
  wp.customize("mcqhome_primary_color", function (value) {
    value.bind(function (to) {
      updateCSSVariable("--mcqhome-primary-color", to);
    });
  });

  // Secondary Color
  wp.customize("mcqhome_secondary_color", function (value) {
    value.bind(function (to) {
      updateCSSVariable("--mcqhome-secondary-color", to);
    });
  });

  // Body Font
  wp.customize("mcqhome_body_font", function (value) {
    value.bind(function (to) {
      updateCSSVariable("--mcqhome-body-font", "'" + to + "', sans-serif");
      loadGoogleFont(to);
    });
  });

  // Heading Font
  wp.customize("mcqhome_heading_font", function (value) {
    value.bind(function (to) {
      updateCSSVariable("--mcqhome-heading-font", "'" + to + "', sans-serif");
      loadGoogleFont(to);
    });
  });

  // Container Width
  wp.customize("mcqhome_container_width", function (value) {
    value.bind(function (to) {
      updateCSSVariable("--mcqhome-container-width", to + "px");
    });
  });

  // Show Question Numbers
  wp.customize("mcqhome_show_question_numbers", function (value) {
    value.bind(function (to) {
      if (to) {
        $(".mcq-question-number").show();
      } else {
        $(".mcq-question-number").hide();
      }
    });
  });

  // Show Welcome Message
  wp.customize("mcqhome_show_welcome_message", function (value) {
    value.bind(function (to) {
      if (to) {
        $(".dashboard-welcome-message").show();
      } else {
        $(".dashboard-welcome-message").hide();
      }
    });
  });

  // Social Media URLs
  wp.customize("mcqhome_facebook_url", function (value) {
    value.bind(function (to) {
      updateSocialLink(".social-facebook", to);
    });
  });

  wp.customize("mcqhome_twitter_url", function (value) {
    value.bind(function (to) {
      updateSocialLink(".social-twitter", to);
    });
  });

  wp.customize("mcqhome_linkedin_url", function (value) {
    value.bind(function (to) {
      updateSocialLink(".social-linkedin", to);
    });
  });

  wp.customize("mcqhome_instagram_url", function (value) {
    value.bind(function (to) {
      updateSocialLink(".social-instagram", to);
    });
  });

  // Footer Copyright
  wp.customize("mcqhome_footer_copyright", function (value) {
    value.bind(function (to) {
      $(".footer-copyright").html(to);
    });
  });

  // Show Footer Social Links
  wp.customize("mcqhome_show_footer_social", function (value) {
    value.bind(function (to) {
      if (to) {
        $(".footer-social-links").show();
      } else {
        $(".footer-social-links").hide();
      }
    });
  });

  // Helper Functions

  /**
   * Update CSS custom property
   */
  function updateCSSVariable(property, value) {
    document.documentElement.style.setProperty(property, value);
  }

  /**
   * Load Google Font dynamically
   */
  function loadGoogleFont(fontName) {
    var fontUrl =
      "https://fonts.googleapis.com/css2?family=" +
      fontName.replace(" ", "+") +
      ":wght@300;400;500;600;700&display=swap";

    // Remove existing font link if it exists
    $('link[href*="' + fontName.replace(" ", "+") + '"]').remove();

    // Add new font link
    $("<link>")
      .attr("rel", "stylesheet")
      .attr("href", fontUrl)
      .appendTo("head");
  }

  /**
   * Update social media links
   */
  function updateSocialLink(selector, url) {
    var $link = $(selector);
    if (url) {
      $link.attr("href", url).parent().show();
    } else {
      $link.parent().hide();
    }
  }

  /**
   * Color manipulation utilities
   */
  function hexToRgb(hex) {
    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result
      ? {
          r: parseInt(result[1], 16),
          g: parseInt(result[2], 16),
          b: parseInt(result[3], 16),
        }
      : null;
  }

  function rgbToHex(r, g, b) {
    return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
  }

  function lightenColor(color, percent) {
    var rgb = hexToRgb(color);
    if (!rgb) return color;

    var r = Math.min(255, Math.floor(rgb.r + ((255 - rgb.r) * percent) / 100));
    var g = Math.min(255, Math.floor(rgb.g + ((255 - rgb.g) * percent) / 100));
    var b = Math.min(255, Math.floor(rgb.b + ((255 - rgb.b) * percent) / 100));

    return rgbToHex(r, g, b);
  }

  function darkenColor(color, percent) {
    var rgb = hexToRgb(color);
    if (!rgb) return color;

    var r = Math.max(0, Math.floor((rgb.r * (100 - percent)) / 100));
    var g = Math.max(0, Math.floor((rgb.g * (100 - percent)) / 100));
    var b = Math.max(0, Math.floor((rgb.b * (100 - percent)) / 100));

    return rgbToHex(r, g, b);
  }

  // Initialize customizer preview
  $(document).ready(function () {
    console.log("MCQHome Customizer Preview initialized");

    // Add customizer preview class to body
    $("body").addClass("customizer-preview");

    // Initialize any existing customizations
    var primaryColor = getComputedStyle(document.documentElement)
      .getPropertyValue("--mcqhome-primary-color")
      .trim();

    if (primaryColor) {
      console.log("Primary color detected:", primaryColor);
    }
  });
})(jQuery);
