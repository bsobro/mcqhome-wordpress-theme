/**
 * MCQHome Theme Customizer Controls
 *
 * This file handles enhanced functionality for the WordPress Customizer admin interface
 */

(function ($) {
  "use strict";

  wp.customize.bind("ready", function () {
    console.log("MCQHome Customizer Controls initialized");

    // Add custom styling to the customizer
    addCustomizerStyles();

    // Initialize color picker enhancements
    initColorPickerEnhancements();

    // Initialize font preview
    initFontPreview();

    // Initialize conditional controls
    initConditionalControls();

    // Initialize reset buttons
    initResetButtons();
  });

  /**
   * Add custom styling to the customizer interface
   */
  function addCustomizerStyles() {
    var customCSS = `
            <style>
                .mcqhome-customizer-section {
                    border-left: 4px solid #2563eb;
                    padding-left: 12px;
                }
                
                .mcqhome-customizer-heading {
                    font-weight: 600;
                    color: #1f2937;
                    margin-bottom: 8px;
                }
                
                .mcqhome-customizer-description {
                    font-size: 13px;
                    color: #6b7280;
                    font-style: italic;
                }
                
                .mcqhome-color-preview {
                    width: 20px;
                    height: 20px;
                    border-radius: 3px;
                    display: inline-block;
                    margin-left: 8px;
                    border: 1px solid #d1d5db;
                }
                
                .mcqhome-font-preview {
                    padding: 8px;
                    background: #f9fafb;
                    border: 1px solid #e5e7eb;
                    border-radius: 4px;
                    margin-top: 8px;
                    font-size: 14px;
                }
                
                .mcqhome-reset-button {
                    background: #dc2626;
                    color: white;
                    border: none;
                    padding: 4px 8px;
                    border-radius: 3px;
                    font-size: 11px;
                    cursor: pointer;
                    margin-left: 8px;
                }
                
                .mcqhome-reset-button:hover {
                    background: #b91c1c;
                }
            </style>
        `;

    $("head").append(customCSS);
  }

  /**
   * Initialize color picker enhancements
   */
  function initColorPickerEnhancements() {
    // Add color previews next to color controls
    wp.customize.control.each(function (control) {
      if (control.params.type === "color") {
        var $control = control.container;
        var $input = $control.find('input[type="text"]');

        if ($input.length) {
          var currentColor = $input.val();
          var $preview = $('<span class="mcqhome-color-preview"></span>');
          $preview.css("background-color", currentColor);
          $input.after($preview);

          // Update preview when color changes
          $input.on("change", function () {
            $preview.css("background-color", $(this).val());
          });
        }
      }
    });
  }

  /**
   * Initialize font preview functionality
   */
  function initFontPreview() {
    var fontControls = ["mcqhome_body_font", "mcqhome_heading_font"];

    fontControls.forEach(function (controlId) {
      wp.customize.control(controlId, function (control) {
        if (control) {
          var $control = control.container;
          var $select = $control.find("select");

          if ($select.length) {
            var $preview = $(
              '<div class="mcqhome-font-preview">Sample text in this font</div>'
            );
            $select.after($preview);

            // Update preview font
            function updateFontPreview() {
              var selectedFont = $select.val();
              $preview.css("font-family", selectedFont + ", sans-serif");

              // Load Google Font for preview
              loadGoogleFontForPreview(selectedFont);
            }

            // Initial preview
            updateFontPreview();

            // Update on change
            $select.on("change", updateFontPreview);
          }
        }
      });
    });
  }

  /**
   * Initialize conditional controls
   */
  function initConditionalControls() {
    // Show/hide social media controls based on footer social setting
    wp.customize("mcqhome_show_footer_social", function (setting) {
      var socialControls = [
        "mcqhome_facebook_url",
        "mcqhome_twitter_url",
        "mcqhome_linkedin_url",
        "mcqhome_instagram_url",
      ];

      function toggleSocialControls(show) {
        socialControls.forEach(function (controlId) {
          wp.customize.control(controlId, function (control) {
            if (control) {
              if (show) {
                control.container.show();
              } else {
                control.container.hide();
              }
            }
          });
        });
      }

      // Initial state
      toggleSocialControls(setting.get());

      // Update on change
      setting.bind(toggleSocialControls);
    });

    // Show/hide performance-related controls based on main performance setting
    wp.customize("mcqhome_enable_caching", function (setting) {
      function togglePerformanceNote(enabled) {
        var $note = $(".mcqhome-performance-note");
        if (enabled && $note.length === 0) {
          wp.customize.control(
            "mcqhome_enable_lazy_loading",
            function (control) {
              if (control) {
                control.container.after(
                  '<p class="mcqhome-performance-note" style="font-size: 12px; color: #059669; font-style: italic;">' +
                    "Performance optimizations are enabled. Your site will load faster!" +
                    "</p>"
                );
              }
            }
          );
        } else if (!enabled) {
          $note.remove();
        }
      }

      // Initial state
      togglePerformanceNote(setting.get());

      // Update on change
      setting.bind(togglePerformanceNote);
    });
  }

  /**
   * Initialize reset buttons for sections
   */
  function initResetButtons() {
    var resetSections = {
      mcqhome_colors: {
        mcqhome_primary_color: "#2563eb",
        mcqhome_secondary_color: "#64748b",
      },
      mcqhome_typography: {
        mcqhome_body_font: "Inter",
        mcqhome_heading_font: "Inter",
      },
      mcqhome_layout: {
        mcqhome_container_width: "1200",
        mcqhome_header_layout: "default",
      },
    };

    Object.keys(resetSections).forEach(function (sectionId) {
      wp.customize.section(sectionId, function (section) {
        if (section) {
          var $sectionContent = section.contentContainer;
          var $resetButton = $(
            '<button type="button" class="mcqhome-reset-button">' +
              "Reset Section" +
              "</button>"
          );

          $sectionContent.prepend($resetButton);

          $resetButton.on("click", function (e) {
            e.preventDefault();

            if (
              confirm(
                "Are you sure you want to reset all settings in this section to their defaults?"
              )
            ) {
              var settings = resetSections[sectionId];
              Object.keys(settings).forEach(function (settingId) {
                wp.customize(settingId, function (setting) {
                  if (setting) {
                    setting.set(settings[settingId]);
                  }
                });
              });
            }
          });
        }
      });
    });
  }

  /**
   * Load Google Font for preview in customizer
   */
  function loadGoogleFontForPreview(fontName) {
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
   * Add helpful tooltips to controls
   */
  function addTooltips() {
    var tooltips = {
      mcqhome_primary_color:
        "This color will be used for buttons, links, and other primary elements.",
      mcqhome_secondary_color:
        "This color will be used for secondary text and subtle elements.",
      mcqhome_container_width:
        "Controls the maximum width of content containers across the site.",
      mcqhome_enable_autosave:
        "When enabled, student progress is automatically saved during assessments.",
      mcqhome_enable_lazy_loading:
        "Improves page load speed by loading images only when needed.",
    };

    Object.keys(tooltips).forEach(function (controlId) {
      wp.customize.control(controlId, function (control) {
        if (control) {
          var $control = control.container;
          var $label = $control.find("label");

          if ($label.length) {
            $label.attr("title", tooltips[controlId]);
            $label.css("cursor", "help");
          }
        }
      });
    });
  }

  // Initialize tooltips after a short delay
  setTimeout(addTooltips, 1000);
})(jQuery);
