/**
 * Accessibility tests using axe-core
 */

const puppeteer = require("puppeteer");
const axeCore = require("axe-core");

class AccessibilityTestRunner {
  constructor() {
    this.baseUrl = process.env.TEST_URL || "http://localhost:8080";
    this.results = {
      passed: 0,
      failed: 0,
      tests: [],
      violations: [],
      summary: {},
    };
  }

  async setup() {
    this.browser = await puppeteer.launch({
      headless: process.env.HEADLESS !== "false",
      args: ["--no-sandbox", "--disable-setuid-sandbox"],
    });

    this.page = await this.browser.newPage();
    await this.page.setViewport({ width: 1280, height: 720 });

    // Inject axe-core into the page
    await this.page.addScriptTag({
      content: axeCore.source,
    });
  }

  async teardown() {
    if (this.browser) {
      await this.browser.close();
    }
  }

  async runAxeTest(url, testName, options = {}) {
    console.log(`Running accessibility test for: ${testName}`);

    try {
      await this.page.goto(url, { waitUntil: "networkidle0" });

      // Run axe-core accessibility tests
      const results = await this.page.evaluate((axeOptions) => {
        return axe.run(document, axeOptions);
      }, options);

      const violations = results.violations;
      const passes = results.passes;
      const incomplete = results.incomplete;

      // Store results
      this.results.summary[testName] = {
        violations: violations.length,
        passes: passes.length,
        incomplete: incomplete.length,
        url,
      };

      // Process violations
      if (violations.length > 0) {
        this.results.failed++;
        this.results.tests.push({
          name: `Accessibility: ${testName}`,
          status: "failed",
          error: `${violations.length} accessibility violations found`,
        });

        // Store detailed violation information
        violations.forEach((violation) => {
          this.results.violations.push({
            testName,
            url,
            id: violation.id,
            impact: violation.impact,
            description: violation.description,
            help: violation.help,
            helpUrl: violation.helpUrl,
            nodes: violation.nodes.map((node) => ({
              html: node.html,
              target: node.target,
              failureSummary: node.failureSummary,
            })),
          });
        });

        console.error(
          `✗ Accessibility: ${testName}: ${violations.length} violations`
        );
      } else {
        this.results.passed++;
        this.results.tests.push({
          name: `Accessibility: ${testName}`,
          status: "passed",
        });
        console.log(`✓ Accessibility: ${testName}`);
      }

      return {
        violations: violations.length,
        passes: passes.length,
        incomplete: incomplete.length,
      };
    } catch (error) {
      this.results.failed++;
      this.results.tests.push({
        name: `Accessibility: ${testName}`,
        status: "failed",
        error: error.message,
      });
      console.error(`✗ Accessibility: ${testName}: ${error.message}`);
      return null;
    }
  }

  async testKeyboardNavigation() {
    console.log("Testing keyboard navigation...");

    try {
      await this.page.goto(`${this.baseUrl}/register/`);

      // Get all focusable elements
      const focusableElements = await this.page.$$eval(
        'a, button, input, textarea, select, [tabindex]:not([tabindex="-1"])',
        (elements) =>
          elements.map((el) => ({
            tagName: el.tagName,
            type: el.type || null,
            id: el.id || null,
            className: el.className || null,
            tabIndex: el.tabIndex,
          }))
      );

      if (focusableElements.length === 0) {
        throw new Error("No focusable elements found on page");
      }

      // Test tab navigation
      let currentFocusIndex = -1;
      const tabSequence = [];

      // Focus first element
      await this.page.keyboard.press("Tab");

      for (let i = 0; i < Math.min(focusableElements.length, 10); i++) {
        const focusedElement = await this.page.evaluate(() => {
          const focused = document.activeElement;
          return {
            tagName: focused.tagName,
            id: focused.id || null,
            className: focused.className || null,
            type: focused.type || null,
          };
        });

        tabSequence.push(focusedElement);

        // Move to next element
        await this.page.keyboard.press("Tab");
        await this.page.waitForTimeout(100);
      }

      // Test reverse tab navigation
      const reverseTabSequence = [];

      for (let i = 0; i < Math.min(5, tabSequence.length); i++) {
        await this.page.keyboard.down("Shift");
        await this.page.keyboard.press("Tab");
        await this.page.keyboard.up("Shift");

        const focusedElement = await this.page.evaluate(() => {
          const focused = document.activeElement;
          return {
            tagName: focused.tagName,
            id: focused.id || null,
            className: focused.className || null,
          };
        });

        reverseTabSequence.push(focusedElement);
        await this.page.waitForTimeout(100);
      }

      // Test Enter key on buttons
      const buttons = await this.page.$$("button");
      let buttonActivationWorking = true;

      if (buttons.length > 0) {
        try {
          await buttons[0].focus();

          // Listen for click events
          await this.page.evaluate(() => {
            window.keyboardTestClicked = false;
            document.addEventListener("click", () => {
              window.keyboardTestClicked = true;
            });
          });

          await this.page.keyboard.press("Enter");
          await this.page.waitForTimeout(100);

          const wasClicked = await this.page.evaluate(
            () => window.keyboardTestClicked
          );
          if (!wasClicked) {
            buttonActivationWorking = false;
          }
        } catch (error) {
          buttonActivationWorking = false;
        }
      }

      // Test Space key on buttons
      let spaceActivationWorking = true;

      if (buttons.length > 0) {
        try {
          await buttons[0].focus();

          await this.page.evaluate(() => {
            window.spaceTestClicked = false;
            document.addEventListener("click", () => {
              window.spaceTestClicked = true;
            });
          });

          await this.page.keyboard.press("Space");
          await this.page.waitForTimeout(100);

          const wasClicked = await this.page.evaluate(
            () => window.spaceTestClicked
          );
          if (!wasClicked) {
            spaceActivationWorking = false;
          }
        } catch (error) {
          spaceActivationWorking = false;
        }
      }

      const keyboardResults = {
        focusableElementsCount: focusableElements.length,
        tabSequenceLength: tabSequence.length,
        reverseTabSequenceLength: reverseTabSequence.length,
        buttonActivationWorking,
        spaceActivationWorking,
        tabSequence: tabSequence.slice(0, 5), // First 5 for brevity
        reverseTabSequence: reverseTabSequence.slice(0, 3), // First 3 for brevity
      };

      // Check if keyboard navigation is working properly
      const issues = [];

      if (focusableElements.length === 0) {
        issues.push("No focusable elements found");
      }

      if (tabSequence.length < Math.min(focusableElements.length, 5)) {
        issues.push("Tab navigation not working properly");
      }

      if (!buttonActivationWorking) {
        issues.push("Enter key does not activate buttons");
      }

      if (!spaceActivationWorking) {
        issues.push("Space key does not activate buttons");
      }

      if (issues.length === 0) {
        this.results.passed++;
        this.results.tests.push({
          name: "Keyboard Navigation",
          status: "passed",
        });
        console.log("✓ Keyboard Navigation");
      } else {
        this.results.failed++;
        this.results.tests.push({
          name: "Keyboard Navigation",
          status: "failed",
          error: issues.join(", "),
        });
        console.error(`✗ Keyboard Navigation: ${issues.join(", ")}`);
      }

      return keyboardResults;
    } catch (error) {
      this.results.failed++;
      this.results.tests.push({
        name: "Keyboard Navigation",
        status: "failed",
        error: error.message,
      });
      console.error(`✗ Keyboard Navigation: ${error.message}`);
      return null;
    }
  }

  async testScreenReaderCompatibility() {
    console.log("Testing screen reader compatibility...");

    try {
      await this.page.goto(`${this.baseUrl}/`);

      // Check for proper heading structure
      const headings = await this.page.$$eval(
        "h1, h2, h3, h4, h5, h6",
        (headings) =>
          headings.map((h) => ({
            level: parseInt(h.tagName.charAt(1)),
            text: h.textContent.trim(),
            hasId: !!h.id,
          }))
      );

      // Check for skip links
      const skipLinks = await this.page.$$eval('a[href^="#"]', (links) =>
        links
          .filter(
            (link) =>
              link.textContent.toLowerCase().includes("skip") ||
              link.textContent.toLowerCase().includes("main")
          )
          .map((link) => ({
            text: link.textContent.trim(),
            href: link.href,
          }))
      );

      // Check for proper form labels
      const formInputs = await this.page.$$eval(
        "input, textarea, select",
        (inputs) =>
          inputs.map((input) => {
            const label =
              document.querySelector(`label[for="${input.id}"]`) ||
              input.closest("label");
            const ariaLabel = input.getAttribute("aria-label");
            const ariaLabelledBy = input.getAttribute("aria-labelledby");

            return {
              type: input.type || input.tagName.toLowerCase(),
              id: input.id,
              name: input.name,
              hasLabel: !!label,
              hasAriaLabel: !!ariaLabel,
              hasAriaLabelledBy: !!ariaLabelledBy,
              labelText: label ? label.textContent.trim() : null,
            };
          })
      );

      // Check for alt text on images
      const images = await this.page.$$eval("img", (imgs) =>
        imgs.map((img) => ({
          src: img.src,
          alt: img.alt,
          hasAlt: img.hasAttribute("alt"),
          isDecorative:
            img.alt === "" ||
            (img.hasAttribute("role") &&
              img.getAttribute("role") === "presentation"),
        }))
      );

      // Check for ARIA landmarks
      const landmarks = await this.page.$$eval(
        "[role], main, nav, header, footer, aside, section",
        (elements) =>
          elements.map((el) => ({
            tagName: el.tagName.toLowerCase(),
            role: el.getAttribute("role"),
            ariaLabel: el.getAttribute("aria-label"),
            ariaLabelledBy: el.getAttribute("aria-labelledby"),
          }))
      );

      // Check for proper button labels
      const buttons = await this.page.$$eval(
        'button, input[type="button"], input[type="submit"]',
        (btns) =>
          btns.map((btn) => ({
            type: btn.type || "button",
            text: btn.textContent.trim(),
            value: btn.value,
            ariaLabel: btn.getAttribute("aria-label"),
            title: btn.title,
            hasAccessibleName: !!(
              btn.textContent.trim() ||
              btn.value ||
              btn.getAttribute("aria-label") ||
              btn.title
            ),
          }))
      );

      const screenReaderResults = {
        headings: {
          count: headings.length,
          hasH1: headings.some((h) => h.level === 1),
          structure: headings.slice(0, 10), // First 10 for brevity
        },
        skipLinks: {
          count: skipLinks.length,
          links: skipLinks,
        },
        formInputs: {
          total: formInputs.length,
          withLabels: formInputs.filter(
            (input) =>
              input.hasLabel || input.hasAriaLabel || input.hasAriaLabelledBy
          ).length,
          unlabeled: formInputs.filter(
            (input) =>
              !input.hasLabel && !input.hasAriaLabel && !input.hasAriaLabelledBy
          ),
        },
        images: {
          total: images.length,
          withAlt: images.filter((img) => img.hasAlt).length,
          missingAlt: images.filter((img) => !img.hasAlt && !img.isDecorative),
        },
        landmarks: {
          count: landmarks.length,
          types: [...new Set(landmarks.map((l) => l.role || l.tagName))],
        },
        buttons: {
          total: buttons.length,
          withAccessibleNames: buttons.filter((btn) => btn.hasAccessibleName)
            .length,
          withoutNames: buttons.filter((btn) => !btn.hasAccessibleName),
        },
      };

      // Evaluate screen reader compatibility
      const issues = [];

      if (screenReaderResults.headings.count === 0) {
        issues.push("No headings found");
      } else if (!screenReaderResults.headings.hasH1) {
        issues.push("No H1 heading found");
      }

      if (screenReaderResults.skipLinks.count === 0) {
        issues.push("No skip links found");
      }

      if (screenReaderResults.formInputs.unlabeled.length > 0) {
        issues.push(
          `${screenReaderResults.formInputs.unlabeled.length} form inputs without labels`
        );
      }

      if (screenReaderResults.images.missingAlt.length > 0) {
        issues.push(
          `${screenReaderResults.images.missingAlt.length} images without alt text`
        );
      }

      if (screenReaderResults.buttons.withoutNames.length > 0) {
        issues.push(
          `${screenReaderResults.buttons.withoutNames.length} buttons without accessible names`
        );
      }

      if (issues.length === 0) {
        this.results.passed++;
        this.results.tests.push({
          name: "Screen Reader Compatibility",
          status: "passed",
        });
        console.log("✓ Screen Reader Compatibility");
      } else {
        this.results.failed++;
        this.results.tests.push({
          name: "Screen Reader Compatibility",
          status: "failed",
          error: issues.join(", "),
        });
        console.error(`✗ Screen Reader Compatibility: ${issues.join(", ")}`);
      }

      return screenReaderResults;
    } catch (error) {
      this.results.failed++;
      this.results.tests.push({
        name: "Screen Reader Compatibility",
        status: "failed",
        error: error.message,
      });
      console.error(`✗ Screen Reader Compatibility: ${error.message}`);
      return null;
    }
  }

  async testColorContrast() {
    console.log("Testing color contrast...");

    try {
      await this.page.goto(`${this.baseUrl}/`);

      // Get color information for text elements
      const colorData = await this.page.evaluate(() => {
        const elements = document.querySelectorAll(
          "p, h1, h2, h3, h4, h5, h6, a, button, label, span, div"
        );
        const results = [];

        for (const element of elements) {
          const text = element.textContent.trim();
          if (text.length === 0) continue;

          const styles = window.getComputedStyle(element);
          const color = styles.color;
          const backgroundColor = styles.backgroundColor;
          const fontSize = styles.fontSize;
          const fontWeight = styles.fontWeight;

          results.push({
            text: text.substring(0, 50), // First 50 characters
            color,
            backgroundColor,
            fontSize,
            fontWeight,
            tagName: element.tagName.toLowerCase(),
          });

          if (results.length >= 50) break; // Limit to 50 elements
        }

        return results;
      });

      // This is a simplified contrast check
      // In a real implementation, you would calculate actual contrast ratios
      const contrastIssues = colorData.filter((item) => {
        // Simple heuristic: if background is transparent and color is light, it might be an issue
        return (
          item.backgroundColor === "rgba(0, 0, 0, 0)" &&
          (item.color.includes("255") || item.color.includes("white"))
        );
      });

      const colorResults = {
        totalElements: colorData.length,
        potentialIssues: contrastIssues.length,
        sampleElements: colorData.slice(0, 10),
      };

      // For now, we'll pass if there are no obvious issues
      // In a real implementation, you would use a proper contrast calculation library
      if (contrastIssues.length === 0) {
        this.results.passed++;
        this.results.tests.push({
          name: "Color Contrast",
          status: "passed",
        });
        console.log("✓ Color Contrast");
      } else {
        this.results.failed++;
        this.results.tests.push({
          name: "Color Contrast",
          status: "failed",
          error: `${contrastIssues.length} potential contrast issues found`,
        });
        console.error(
          `✗ Color Contrast: ${contrastIssues.length} potential issues`
        );
      }

      return colorResults;
    } catch (error) {
      this.results.failed++;
      this.results.tests.push({
        name: "Color Contrast",
        status: "failed",
        error: error.message,
      });
      console.error(`✗ Color Contrast: ${error.message}`);
      return null;
    }
  }

  generateReport() {
    const report = {
      summary: {
        totalTests: this.results.passed + this.results.failed,
        passed: this.results.passed,
        failed: this.results.failed,
        successRate:
          this.results.passed + this.results.failed > 0
            ? (
                (this.results.passed /
                  (this.results.passed + this.results.failed)) *
                100
              ).toFixed(2) + "%"
            : "0%",
      },
      testResults: this.results.tests,
      violations: this.results.violations,
      pageResults: this.results.summary,
      recommendations: this.generateRecommendations(),
    };

    return report;
  }

  generateRecommendations() {
    const recommendations = [];

    // Group violations by impact level
    const criticalViolations = this.results.violations.filter(
      (v) => v.impact === "critical"
    );
    const seriousViolations = this.results.violations.filter(
      (v) => v.impact === "serious"
    );
    const moderateViolations = this.results.violations.filter(
      (v) => v.impact === "moderate"
    );

    if (criticalViolations.length > 0) {
      recommendations.push(
        `Fix ${criticalViolations.length} critical accessibility violations immediately`
      );
    }

    if (seriousViolations.length > 0) {
      recommendations.push(
        `Address ${seriousViolations.length} serious accessibility violations`
      );
    }

    if (moderateViolations.length > 0) {
      recommendations.push(
        `Consider fixing ${moderateViolations.length} moderate accessibility violations`
      );
    }

    // Common violation types
    const violationTypes = {};
    this.results.violations.forEach((violation) => {
      violationTypes[violation.id] = (violationTypes[violation.id] || 0) + 1;
    });

    Object.entries(violationTypes)
      .sort(([, a], [, b]) => b - a)
      .slice(0, 5)
      .forEach(([violationType, count]) => {
        recommendations.push(
          `Most common issue: ${violationType} (${count} instances)`
        );
      });

    return recommendations;
  }

  async runAllTests() {
    console.log("Starting Accessibility tests...\n");

    await this.setup();

    try {
      // Run axe-core tests on key pages
      await this.runAxeTest(this.baseUrl, "Homepage");
      await this.runAxeTest(`${this.baseUrl}/register/`, "Registration Page");
      await this.runAxeTest(`${this.baseUrl}/browse/`, "Browse Page");
      await this.runAxeTest(
        `${this.baseUrl}/take-assessment/?set_id=1`,
        "Assessment Page"
      );

      // Run custom accessibility tests
      await this.testKeyboardNavigation();
      await this.testScreenReaderCompatibility();
      await this.testColorContrast();
    } finally {
      await this.teardown();
    }

    // Generate and display report
    const report = this.generateReport();

    console.log("\n=== Accessibility Test Results ===");
    console.log(`Total Tests: ${report.summary.totalTests}`);
    console.log(`Passed: ${report.summary.passed}`);
    console.log(`Failed: ${report.summary.failed}`);
    console.log(`Success Rate: ${report.summary.successRate}`);

    if (Object.keys(report.pageResults).length > 0) {
      console.log("\n--- Page Results ---");
      Object.entries(report.pageResults).forEach(([pageName, results]) => {
        console.log(
          `${pageName}: ${results.violations} violations, ${results.passes} passes`
        );
      });
    }

    if (report.violations.length > 0) {
      console.log("\n--- Top Violations ---");
      const violationCounts = {};
      report.violations.forEach((v) => {
        violationCounts[v.id] = (violationCounts[v.id] || 0) + 1;
      });

      Object.entries(violationCounts)
        .sort(([, a], [, b]) => b - a)
        .slice(0, 5)
        .forEach(([violation, count]) => {
          console.log(`- ${violation}: ${count} instances`);
        });
    }

    if (report.recommendations.length > 0) {
      console.log("\n--- Recommendations ---");
      report.recommendations.forEach((rec, index) => {
        console.log(`${index + 1}. ${rec}`);
      });
    }

    if (this.results.failed > 0) {
      console.log("\n--- Failed Tests ---");
      this.results.tests
        .filter((test) => test.status === "failed")
        .forEach((test) => {
          console.log(`- ${test.name}: ${test.error}`);
        });
    }

    return this.results.failed === 0;
  }
}

// Run tests if called directly
if (require.main === module) {
  const runner = new AccessibilityTestRunner();
  runner
    .runAllTests()
    .then((success) => {
      process.exit(success ? 0 : 1);
    })
    .catch((error) => {
      console.error("Accessibility test runner failed:", error);
      process.exit(1);
    });
}

module.exports = AccessibilityTestRunner;
