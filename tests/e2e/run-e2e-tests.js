/**
 * End-to-end tests using Puppeteer
 */

const puppeteer = require("puppeteer");
const path = require("path");

class E2ETestRunner {
  constructor() {
    this.browser = null;
    this.page = null;
    this.baseUrl = process.env.TEST_URL || "http://localhost:8080";
    this.results = {
      passed: 0,
      failed: 0,
      tests: [],
    };
  }

  async setup() {
    console.log("Setting up E2E tests...");

    this.browser = await puppeteer.launch({
      headless: process.env.HEADLESS !== "false",
      slowMo: process.env.SLOW_MO ? parseInt(process.env.SLOW_MO) : 0,
      args: ["--no-sandbox", "--disable-setuid-sandbox"],
    });

    this.page = await this.browser.newPage();

    // Set viewport
    await this.page.setViewport({ width: 1280, height: 720 });

    // Enable request interception for mocking
    await this.page.setRequestInterception(true);

    // Handle requests
    this.page.on("request", (request) => {
      // Allow all requests by default
      request.continue();
    });

    // Log console messages
    this.page.on("console", (msg) => {
      if (msg.type() === "error") {
        console.error("Browser console error:", msg.text());
      }
    });

    // Log page errors
    this.page.on("pageerror", (error) => {
      console.error("Page error:", error.message);
    });
  }

  async teardown() {
    if (this.browser) {
      await this.browser.close();
    }
  }

  async runTest(testName, testFunction) {
    console.log(`Running test: ${testName}`);

    try {
      await testFunction();
      this.results.passed++;
      this.results.tests.push({ name: testName, status: "passed" });
      console.log(`✓ ${testName}`);
    } catch (error) {
      this.results.failed++;
      this.results.tests.push({
        name: testName,
        status: "failed",
        error: error.message,
      });
      console.error(`✗ ${testName}: ${error.message}`);
    }
  }

  async testUserRegistrationFlow() {
    await this.page.goto(`${this.baseUrl}/register/`);

    // Wait for registration form to load
    await this.page.waitForSelector("#mcqhome-registration-form", {
      timeout: 5000,
    });

    // Select student role
    await this.page.click('input[value="student"]');

    // Fill in registration form
    await this.page.type('input[name="user_login"]', "e2e_test_student");
    await this.page.type('input[name="user_email"]', "e2e_student@test.com");
    await this.page.type('input[name="user_pass"]', "testpassword123");
    await this.page.type('input[name="user_pass_confirm"]', "testpassword123");
    await this.page.type('input[name="first_name"]', "E2E");
    await this.page.type('input[name="last_name"]', "Student");

    // Submit form
    await this.page.click("#registration-submit");

    // Wait for success message or redirect
    try {
      await this.page.waitForSelector(".success-message", { timeout: 10000 });
      const successMessage = await this.page.$eval(
        ".success-message",
        (el) => el.textContent
      );

      if (!successMessage.includes("successful")) {
        throw new Error("Registration success message not found");
      }
    } catch (error) {
      // Check if redirected to dashboard
      await this.page.waitForNavigation({ timeout: 10000 });
      const currentUrl = this.page.url();

      if (!currentUrl.includes("/dashboard/")) {
        throw new Error("Registration did not redirect to dashboard");
      }
    }
  }

  async testTeacherRegistrationWithInstitution() {
    await this.page.goto(`${this.baseUrl}/register/`);

    // Wait for form
    await this.page.waitForSelector("#mcqhome-registration-form");

    // Select teacher role
    await this.page.click('input[value="teacher"]');

    // Wait for teacher-specific fields to appear
    await this.page.waitForSelector(".teacher-fields", { visible: true });

    // Fill basic information
    await this.page.type('input[name="user_login"]', "e2e_test_teacher");
    await this.page.type('input[name="user_email"]', "e2e_teacher@test.com");
    await this.page.type('input[name="user_pass"]', "testpassword123");
    await this.page.type('input[name="user_pass_confirm"]', "testpassword123");
    await this.page.type('input[name="first_name"]', "E2E");
    await this.page.type('input[name="last_name"]', "Teacher");

    // Select institution
    await this.page.select('select[name="institution_id"]', "1");
    await this.page.type('input[name="specialization"]', "Mathematics");

    // Submit form
    await this.page.click("#registration-submit");

    // Wait for success or redirect
    await this.page.waitForFunction(
      () => {
        return (
          document.querySelector(".success-message")?.style.display ===
            "block" || window.location.href.includes("/dashboard/")
        );
      },
      { timeout: 10000 }
    );
  }

  async testAssessmentTakingFlow() {
    // First login as a student (assuming we have test data)
    await this.page.goto(`${this.baseUrl}/wp-login.php`);

    await this.page.type("#user_login", "test_student");
    await this.page.type("#user_pass", "test_password");
    await this.page.click("#wp-submit");

    // Navigate to assessment
    await this.page.goto(`${this.baseUrl}/take-assessment/?set_id=1`);

    // Wait for assessment to load
    await this.page.waitForSelector("#mcq-assessment-container");

    // Check timer is running
    const timerElement = await this.page.$("#mcq-timer");
    const initialTime = await this.page.evaluate(
      (el) => el.textContent,
      timerElement
    );

    // Wait a moment and check timer has changed
    await this.page.waitForTimeout(2000);
    const updatedTime = await this.page.evaluate(
      (el) => el.textContent,
      timerElement
    );

    if (initialTime === updatedTime) {
      throw new Error("Timer is not running");
    }

    // Answer first question
    await this.page.click('input[name="answer"][value="B"]');

    // Check progress updated
    const progressWidth = await this.page.$eval(
      ".progress-fill",
      (el) => el.style.width
    );
    if (progressWidth === "0%") {
      throw new Error("Progress not updated after answering question");
    }

    // Navigate to next question
    await this.page.click("#mcq-next-btn");

    // Answer second question
    await this.page.click('input[name="answer"][value="C"]');

    // Continue answering questions until the end
    let hasNextButton = true;
    while (hasNextButton) {
      try {
        await this.page.waitForSelector("#mcq-next-btn", { timeout: 1000 });
        await this.page.click('input[name="answer"][value="A"]'); // Random answer
        await this.page.click("#mcq-next-btn");
      } catch (error) {
        hasNextButton = false;
      }
    }

    // Submit assessment
    await this.page.waitForSelector("#mcq-submit-btn", { visible: true });

    // Handle confirmation dialog
    this.page.on("dialog", async (dialog) => {
      await dialog.accept();
    });

    await this.page.click("#mcq-submit-btn");

    // Wait for results page
    await this.page.waitForNavigation({ timeout: 15000 });

    // Verify we're on results page
    const currentUrl = this.page.url();
    if (
      !currentUrl.includes("assessment-results") &&
      !currentUrl.includes("results")
    ) {
      throw new Error("Did not navigate to results page after submission");
    }

    // Check results are displayed
    await this.page.waitForSelector(".assessment-results", { timeout: 5000 });
  }

  async testMCQCreationFlow() {
    // Login as teacher
    await this.page.goto(`${this.baseUrl}/wp-login.php`);

    await this.page.type("#user_login", "test_teacher");
    await this.page.type("#user_pass", "test_password");
    await this.page.click("#wp-submit");

    // Navigate to MCQ creation
    await this.page.goto(`${this.baseUrl}/wp-admin/post-new.php?post_type=mcq`);

    // Wait for MCQ editor to load
    await this.page.waitForSelector("#mcq_question_text");

    // Fill in MCQ details
    await this.page.type("#title", "E2E Test MCQ");

    // Fill question text (assuming TinyMCE is loaded)
    await this.page.waitForSelector("#mcq_question_text_ifr");
    const questionFrame = await this.page.$("#mcq_question_text_ifr");
    const questionFrameContent = await questionFrame.contentFrame();
    await questionFrameContent.type("body", "What is the result of 5 + 3?");

    // Fill answer options
    await this.page.type('input[name="mcq_option_a"]', "6");
    await this.page.type('input[name="mcq_option_b"]', "7");
    await this.page.type('input[name="mcq_option_c"]', "8");
    await this.page.type('input[name="mcq_option_d"]', "9");

    // Select correct answer
    await this.page.click('input[name="mcq_correct_answer"][value="C"]');

    // Fill explanation
    await this.page.waitForSelector("#mcq_explanation_ifr");
    const explanationFrame = await this.page.$("#mcq_explanation_ifr");
    const explanationFrameContent = await explanationFrame.contentFrame();
    await explanationFrameContent.type(
      "body",
      "5 + 3 equals 8, which is option C."
    );

    // Publish MCQ
    await this.page.click("#publish");

    // Wait for success message
    await this.page.waitForSelector(".notice-success", { timeout: 10000 });

    const successMessage = await this.page.$eval(
      ".notice-success",
      (el) => el.textContent
    );
    if (!successMessage.includes("published")) {
      throw new Error("MCQ was not published successfully");
    }
  }

  async testResponsiveDesign() {
    // Test mobile viewport
    await this.page.setViewport({ width: 375, height: 667 }); // iPhone SE

    await this.page.goto(`${this.baseUrl}/`);

    // Check mobile navigation
    const mobileMenu = await this.page.$(".mobile-menu-toggle");
    if (!mobileMenu) {
      throw new Error("Mobile menu toggle not found");
    }

    // Test assessment on mobile
    await this.page.goto(`${this.baseUrl}/take-assessment/?set_id=1`);

    // Check assessment is responsive
    const assessmentContainer = await this.page.$("#mcq-assessment-container");
    const containerWidth = await this.page.evaluate(
      (el) => el.offsetWidth,
      assessmentContainer
    );

    if (containerWidth > 375) {
      throw new Error("Assessment container is not responsive on mobile");
    }

    // Check touch-friendly answer options
    const answerOptions = await this.page.$$('input[name="answer"]');
    for (const option of answerOptions) {
      const optionSize = await this.page.evaluate((el) => {
        const rect = el.getBoundingClientRect();
        return { width: rect.width, height: rect.height };
      }, option);

      // Touch targets should be at least 44px
      if (optionSize.width < 44 || optionSize.height < 44) {
        throw new Error("Answer options are not touch-friendly");
      }
    }

    // Test tablet viewport
    await this.page.setViewport({ width: 768, height: 1024 }); // iPad

    await this.page.reload();

    // Check layout adapts to tablet
    const updatedContainerWidth = await this.page.evaluate(
      (el) => el.offsetWidth,
      assessmentContainer
    );
    if (updatedContainerWidth <= 375) {
      throw new Error("Layout does not adapt to tablet viewport");
    }
  }

  async testAccessibility() {
    await this.page.goto(`${this.baseUrl}/`);

    // Check for basic accessibility features
    const skipLink = await this.page.$('a[href="#main"]');
    if (!skipLink) {
      console.warn("Skip to main content link not found");
    }

    // Check form labels
    await this.page.goto(`${this.baseUrl}/register/`);

    const formInputs = await this.page.$$("input[required]");
    for (const input of formInputs) {
      const inputId = await this.page.evaluate((el) => el.id, input);
      const inputName = await this.page.evaluate((el) => el.name, input);

      // Check for associated label
      const label = await this.page.$(`label[for="${inputId}"]`);
      const parentLabel = await this.page.evaluateHandle(
        (el) => el.closest("label"),
        input
      );

      if (!label && !parentLabel) {
        throw new Error(`Input ${inputName} does not have an associated label`);
      }
    }

    // Check heading hierarchy
    const headings = await this.page.$$eval(
      "h1, h2, h3, h4, h5, h6",
      (headings) =>
        headings.map((h) => ({ tag: h.tagName, text: h.textContent.trim() }))
    );

    if (headings.length === 0) {
      throw new Error("No headings found on page");
    }

    // Should start with h1
    if (headings[0].tag !== "H1") {
      throw new Error("Page does not start with h1 heading");
    }

    // Check for alt text on images
    const images = await this.page.$$("img");
    for (const img of images) {
      const alt = await this.page.evaluate((el) => el.alt, img);
      const src = await this.page.evaluate((el) => el.src, img);

      if (!alt && !src.includes("decorative")) {
        console.warn(`Image ${src} missing alt text`);
      }
    }
  }

  async testPerformance() {
    // Enable performance monitoring
    await this.page.coverage.startJSCoverage();
    await this.page.coverage.startCSSCoverage();

    const startTime = Date.now();

    await this.page.goto(`${this.baseUrl}/`, { waitUntil: "networkidle0" });

    const loadTime = Date.now() - startTime;

    // Check load time (should be under 3 seconds)
    if (loadTime > 3000) {
      throw new Error(`Page load time too slow: ${loadTime}ms`);
    }

    // Check for performance metrics
    const performanceMetrics = await this.page.evaluate(() => {
      const navigation = performance.getEntriesByType("navigation")[0];
      return {
        domContentLoaded:
          navigation.domContentLoadedEventEnd -
          navigation.domContentLoadedEventStart,
        loadComplete: navigation.loadEventEnd - navigation.loadEventStart,
        firstPaint:
          performance.getEntriesByName("first-paint")[0]?.startTime || 0,
        firstContentfulPaint:
          performance.getEntriesByName("first-contentful-paint")[0]
            ?.startTime || 0,
      };
    });

    console.log("Performance metrics:", performanceMetrics);

    // Stop coverage
    const jsCoverage = await this.page.coverage.stopJSCoverage();
    const cssCoverage = await this.page.coverage.stopCSSCoverage();

    // Calculate coverage
    let totalBytes = 0;
    let usedBytes = 0;

    [...jsCoverage, ...cssCoverage].forEach((entry) => {
      totalBytes += entry.text.length;
      entry.ranges.forEach((range) => {
        usedBytes += range.end - range.start - 1;
      });
    });

    const coveragePercentage = (usedBytes / totalBytes) * 100;
    console.log(`Code coverage: ${coveragePercentage.toFixed(2)}%`);

    if (coveragePercentage < 50) {
      console.warn("Low code coverage detected");
    }
  }

  async runAllTests() {
    console.log("Starting E2E tests...\n");

    await this.setup();

    try {
      await this.runTest("User Registration Flow", () =>
        this.testUserRegistrationFlow()
      );
      await this.runTest("Teacher Registration with Institution", () =>
        this.testTeacherRegistrationWithInstitution()
      );
      await this.runTest("Assessment Taking Flow", () =>
        this.testAssessmentTakingFlow()
      );
      await this.runTest("MCQ Creation Flow", () => this.testMCQCreationFlow());
      await this.runTest("Responsive Design", () =>
        this.testResponsiveDesign()
      );
      await this.runTest("Accessibility", () => this.testAccessibility());
      await this.runTest("Performance", () => this.testPerformance());
    } finally {
      await this.teardown();
    }

    // Print results
    console.log("\n=== E2E Test Results ===");
    console.log(`Passed: ${this.results.passed}`);
    console.log(`Failed: ${this.results.failed}`);
    console.log(`Total: ${this.results.passed + this.results.failed}`);

    if (this.results.failed > 0) {
      console.log("\nFailed tests:");
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
  const runner = new E2ETestRunner();
  runner
    .runAllTests()
    .then((success) => {
      process.exit(success ? 0 : 1);
    })
    .catch((error) => {
      console.error("E2E test runner failed:", error);
      process.exit(1);
    });
}

module.exports = E2ETestRunner;
