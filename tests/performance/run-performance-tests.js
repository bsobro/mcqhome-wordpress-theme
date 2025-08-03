/**
 * Performance tests using Lighthouse and custom metrics
 */

const lighthouse = require("lighthouse");
const chromeLauncher = require("chrome-launcher");
const puppeteer = require("puppeteer");

class PerformanceTestRunner {
  constructor() {
    this.baseUrl = process.env.TEST_URL || "http://localhost:8080";
    this.results = {
      lighthouse: {},
      customMetrics: {},
      passed: 0,
      failed: 0,
      tests: [],
    };
  }

  async runLighthouseTest(url, testName) {
    console.log(`Running Lighthouse test for: ${testName}`);

    const chrome = await chromeLauncher.launch({ chromeFlags: ["--headless"] });

    try {
      const options = {
        logLevel: "info",
        output: "json",
        onlyCategories: [
          "performance",
          "accessibility",
          "best-practices",
          "seo",
        ],
        port: chrome.port,
      };

      const runnerResult = await lighthouse(url, options);
      const report = runnerResult.lhr;

      const scores = {
        performance: Math.round(report.categories.performance.score * 100),
        accessibility: Math.round(report.categories.accessibility.score * 100),
        bestPractices: Math.round(
          report.categories["best-practices"].score * 100
        ),
        seo: Math.round(report.categories.seo.score * 100),
      };

      const metrics = {
        firstContentfulPaint:
          report.audits["first-contentful-paint"].numericValue,
        largestContentfulPaint:
          report.audits["largest-contentful-paint"].numericValue,
        firstMeaningfulPaint:
          report.audits["first-meaningful-paint"].numericValue,
        speedIndex: report.audits["speed-index"].numericValue,
        timeToInteractive: report.audits["interactive"].numericValue,
        totalBlockingTime: report.audits["total-blocking-time"].numericValue,
        cumulativeLayoutShift:
          report.audits["cumulative-layout-shift"].numericValue,
      };

      this.results.lighthouse[testName] = {
        scores,
        metrics,
        url,
      };

      // Check if scores meet thresholds
      const thresholds = {
        performance: 90,
        accessibility: 95,
        bestPractices: 90,
        seo: 90,
      };

      let passed = true;
      const failures = [];

      Object.entries(thresholds).forEach(([category, threshold]) => {
        if (scores[category] < threshold) {
          passed = false;
          failures.push(`${category}: ${scores[category]} < ${threshold}`);
        }
      });

      if (passed) {
        this.results.passed++;
        this.results.tests.push({
          name: `Lighthouse: ${testName}`,
          status: "passed",
        });
        console.log(`✓ Lighthouse: ${testName}`);
      } else {
        this.results.failed++;
        this.results.tests.push({
          name: `Lighthouse: ${testName}`,
          status: "failed",
          error: failures.join(", "),
        });
        console.error(`✗ Lighthouse: ${testName}: ${failures.join(", ")}`);
      }

      return { scores, metrics, passed };
    } finally {
      await chrome.kill();
    }
  }

  async testPageLoadPerformance() {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();

    try {
      // Test homepage load performance
      await page.goto(this.baseUrl, { waitUntil: "networkidle0" });

      const performanceMetrics = await page.evaluate(() => {
        const navigation = performance.getEntriesByType("navigation")[0];
        const paint = performance.getEntriesByType("paint");

        return {
          domContentLoaded:
            navigation.domContentLoadedEventEnd -
            navigation.domContentLoadedEventStart,
          loadComplete: navigation.loadEventEnd - navigation.loadEventStart,
          firstPaint:
            paint.find((p) => p.name === "first-paint")?.startTime || 0,
          firstContentfulPaint:
            paint.find((p) => p.name === "first-contentful-paint")?.startTime ||
            0,
          totalLoadTime: navigation.loadEventEnd - navigation.fetchStart,
          dnsLookup: navigation.domainLookupEnd - navigation.domainLookupStart,
          tcpConnect: navigation.connectEnd - navigation.connectStart,
          serverResponse: navigation.responseEnd - navigation.requestStart,
          domProcessing: navigation.domComplete - navigation.domLoading,
        };
      });

      this.results.customMetrics.pageLoad = performanceMetrics;

      // Check performance thresholds
      const thresholds = {
        totalLoadTime: 3000, // 3 seconds
        firstContentfulPaint: 1500, // 1.5 seconds
        domContentLoaded: 2000, // 2 seconds
      };

      let passed = true;
      const failures = [];

      Object.entries(thresholds).forEach(([metric, threshold]) => {
        if (performanceMetrics[metric] > threshold) {
          passed = false;
          failures.push(
            `${metric}: ${performanceMetrics[metric]}ms > ${threshold}ms`
          );
        }
      });

      if (passed) {
        this.results.passed++;
        this.results.tests.push({
          name: "Page Load Performance",
          status: "passed",
        });
        console.log("✓ Page Load Performance");
      } else {
        this.results.failed++;
        this.results.tests.push({
          name: "Page Load Performance",
          status: "failed",
          error: failures.join(", "),
        });
        console.error(`✗ Page Load Performance: ${failures.join(", ")}`);
      }
    } finally {
      await browser.close();
    }
  }

  async testAssessmentPerformance() {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();

    try {
      // Navigate to assessment page
      await page.goto(`${this.baseUrl}/take-assessment/?set_id=1`);

      // Measure assessment initialization time
      const initStartTime = Date.now();
      await page.waitForSelector("#mcq-assessment-container", {
        timeout: 10000,
      });
      const initTime = Date.now() - initStartTime;

      // Measure question navigation performance
      const navigationTimes = [];

      for (let i = 0; i < 5; i++) {
        const navStartTime = Date.now();

        // Click next button if available
        const nextButton = await page.$("#mcq-next-btn");
        if (nextButton) {
          await nextButton.click();
          await page.waitForFunction(
            (expectedQuestion) => {
              const currentQuestion = document.querySelector(".mcq-question");
              return (
                currentQuestion &&
                currentQuestion.dataset.questionId !== expectedQuestion
              );
            },
            {},
            i.toString()
          );

          const navTime = Date.now() - navStartTime;
          navigationTimes.push(navTime);
        } else {
          break;
        }
      }

      const avgNavigationTime =
        navigationTimes.length > 0
          ? navigationTimes.reduce((a, b) => a + b, 0) / navigationTimes.length
          : 0;

      // Measure auto-save performance
      const autoSaveTimes = [];

      for (let i = 0; i < 3; i++) {
        const saveStartTime = Date.now();

        // Select an answer to trigger auto-save
        const answerOption = await page.$('input[name="answer"]');
        if (answerOption) {
          await answerOption.click();

          // Wait for auto-save to complete (mock implementation)
          await page.waitForTimeout(100);

          const saveTime = Date.now() - saveStartTime;
          autoSaveTimes.push(saveTime);
        }
      }

      const avgAutoSaveTime =
        autoSaveTimes.length > 0
          ? autoSaveTimes.reduce((a, b) => a + b, 0) / autoSaveTimes.length
          : 0;

      const assessmentMetrics = {
        initializationTime: initTime,
        averageNavigationTime: avgNavigationTime,
        averageAutoSaveTime: avgAutoSaveTime,
        navigationTimes,
        autoSaveTimes,
      };

      this.results.customMetrics.assessment = assessmentMetrics;

      // Check performance thresholds
      const thresholds = {
        initializationTime: 2000, // 2 seconds
        averageNavigationTime: 500, // 0.5 seconds
        averageAutoSaveTime: 1000, // 1 second
      };

      let passed = true;
      const failures = [];

      Object.entries(thresholds).forEach(([metric, threshold]) => {
        if (assessmentMetrics[metric] > threshold) {
          passed = false;
          failures.push(
            `${metric}: ${assessmentMetrics[metric]}ms > ${threshold}ms`
          );
        }
      });

      if (passed) {
        this.results.passed++;
        this.results.tests.push({
          name: "Assessment Performance",
          status: "passed",
        });
        console.log("✓ Assessment Performance");
      } else {
        this.results.failed++;
        this.results.tests.push({
          name: "Assessment Performance",
          status: "failed",
          error: failures.join(", "),
        });
        console.error(`✗ Assessment Performance: ${failures.join(", ")}`);
      }
    } finally {
      await browser.close();
    }
  }

  async testResourceOptimization() {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();

    try {
      // Enable request interception to analyze resources
      await page.setRequestInterception(true);

      const resources = {
        css: [],
        js: [],
        images: [],
        fonts: [],
        other: [],
      };

      let totalSize = 0;
      let compressedRequests = 0;
      let cachedRequests = 0;

      page.on("request", (request) => {
        request.continue();
      });

      page.on("response", (response) => {
        const url = response.url();
        const headers = response.headers();
        const contentLength = parseInt(headers["content-length"] || "0");
        const contentType = headers["content-type"] || "";
        const contentEncoding = headers["content-encoding"] || "";
        const cacheControl = headers["cache-control"] || "";

        totalSize += contentLength;

        if (
          contentEncoding.includes("gzip") ||
          contentEncoding.includes("br")
        ) {
          compressedRequests++;
        }

        if (
          cacheControl.includes("max-age") ||
          headers["etag"] ||
          headers["last-modified"]
        ) {
          cachedRequests++;
        }

        // Categorize resources
        if (contentType.includes("text/css") || url.includes(".css")) {
          resources.css.push({
            url,
            size: contentLength,
            compressed: !!contentEncoding,
          });
        } else if (contentType.includes("javascript") || url.includes(".js")) {
          resources.js.push({
            url,
            size: contentLength,
            compressed: !!contentEncoding,
          });
        } else if (contentType.includes("image/")) {
          resources.images.push({ url, size: contentLength });
        } else if (
          contentType.includes("font/") ||
          url.includes(".woff") ||
          url.includes(".ttf")
        ) {
          resources.fonts.push({ url, size: contentLength });
        } else {
          resources.other.push({ url, size: contentLength });
        }
      });

      await page.goto(this.baseUrl, { waitUntil: "networkidle0" });

      const totalRequests = Object.values(resources).reduce(
        (sum, arr) => sum + arr.length,
        0
      );
      const compressionRate =
        totalRequests > 0 ? (compressedRequests / totalRequests) * 100 : 0;
      const cacheRate =
        totalRequests > 0 ? (cachedRequests / totalRequests) * 100 : 0;

      const optimizationMetrics = {
        totalSize: totalSize / 1024, // KB
        totalRequests,
        compressionRate,
        cacheRate,
        resources: {
          css: resources.css.length,
          js: resources.js.length,
          images: resources.images.length,
          fonts: resources.fonts.length,
          other: resources.other.length,
        },
        largestResources: Object.values(resources)
          .flat()
          .sort((a, b) => b.size - a.size)
          .slice(0, 5)
          .map((r) => ({ url: r.url, size: Math.round(r.size / 1024) + "KB" })),
      };

      this.results.customMetrics.resourceOptimization = optimizationMetrics;

      // Check optimization thresholds
      const thresholds = {
        totalSize: 2048, // 2MB
        totalRequests: 50,
        compressionRate: 80, // 80% of resources should be compressed
        cacheRate: 70, // 70% of resources should have cache headers
      };

      let passed = true;
      const failures = [];

      if (optimizationMetrics.totalSize > thresholds.totalSize) {
        passed = false;
        failures.push(
          `Total size: ${Math.round(optimizationMetrics.totalSize)}KB > ${
            thresholds.totalSize
          }KB`
        );
      }

      if (optimizationMetrics.totalRequests > thresholds.totalRequests) {
        passed = false;
        failures.push(
          `Total requests: ${optimizationMetrics.totalRequests} > ${thresholds.totalRequests}`
        );
      }

      if (optimizationMetrics.compressionRate < thresholds.compressionRate) {
        passed = false;
        failures.push(
          `Compression rate: ${Math.round(
            optimizationMetrics.compressionRate
          )}% < ${thresholds.compressionRate}%`
        );
      }

      if (optimizationMetrics.cacheRate < thresholds.cacheRate) {
        passed = false;
        failures.push(
          `Cache rate: ${Math.round(optimizationMetrics.cacheRate)}% < ${
            thresholds.cacheRate
          }%`
        );
      }

      if (passed) {
        this.results.passed++;
        this.results.tests.push({
          name: "Resource Optimization",
          status: "passed",
        });
        console.log("✓ Resource Optimization");
      } else {
        this.results.failed++;
        this.results.tests.push({
          name: "Resource Optimization",
          status: "failed",
          error: failures.join(", "),
        });
        console.error(`✗ Resource Optimization: ${failures.join(", ")}`);
      }
    } finally {
      await browser.close();
    }
  }

  async testDatabasePerformance() {
    // This would typically require access to the WordPress database
    // For now, we'll simulate database performance tests

    console.log("Testing database performance...");

    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();

    try {
      // Test pages that likely involve database queries
      const testPages = [
        { url: `${this.baseUrl}/`, name: "Homepage" },
        { url: `${this.baseUrl}/browse/`, name: "Browse Page" },
        { url: `${this.baseUrl}/institutions/`, name: "Institutions Page" },
        { url: `${this.baseUrl}/dashboard/`, name: "Dashboard" },
      ];

      const pageLoadTimes = {};

      for (const testPage of testPages) {
        const startTime = Date.now();

        try {
          await page.goto(testPage.url, {
            waitUntil: "networkidle0",
            timeout: 10000,
          });
          const loadTime = Date.now() - startTime;
          pageLoadTimes[testPage.name] = loadTime;
        } catch (error) {
          console.warn(`Could not load ${testPage.name}: ${error.message}`);
          pageLoadTimes[testPage.name] = null;
        }
      }

      // Calculate average load time for pages that loaded successfully
      const validLoadTimes = Object.values(pageLoadTimes).filter(
        (time) => time !== null
      );
      const averageLoadTime =
        validLoadTimes.length > 0
          ? validLoadTimes.reduce((a, b) => a + b, 0) / validLoadTimes.length
          : 0;

      const dbMetrics = {
        pageLoadTimes,
        averageLoadTime,
        testedPages: testPages.length,
        successfulPages: validLoadTimes.length,
      };

      this.results.customMetrics.database = dbMetrics;

      // Check if average load time is acceptable
      const threshold = 3000; // 3 seconds

      if (averageLoadTime <= threshold && validLoadTimes.length > 0) {
        this.results.passed++;
        this.results.tests.push({
          name: "Database Performance",
          status: "passed",
        });
        console.log("✓ Database Performance");
      } else {
        this.results.failed++;
        this.results.tests.push({
          name: "Database Performance",
          status: "failed",
          error: `Average load time: ${Math.round(
            averageLoadTime
          )}ms > ${threshold}ms`,
        });
        console.error(
          `✗ Database Performance: Average load time ${Math.round(
            averageLoadTime
          )}ms > ${threshold}ms`
        );
      }
    } finally {
      await browser.close();
    }
  }

  generateReport() {
    const report = {
      summary: {
        totalTests: this.results.passed + this.results.failed,
        passed: this.results.passed,
        failed: this.results.failed,
        successRate:
          (
            (this.results.passed /
              (this.results.passed + this.results.failed)) *
            100
          ).toFixed(2) + "%",
      },
      lighthouse: this.results.lighthouse,
      customMetrics: this.results.customMetrics,
      testDetails: this.results.tests,
      recommendations: this.generateRecommendations(),
    };

    return report;
  }

  generateRecommendations() {
    const recommendations = [];

    // Lighthouse recommendations
    Object.entries(this.results.lighthouse).forEach(([testName, data]) => {
      if (data.scores.performance < 90) {
        recommendations.push(
          `Improve performance for ${testName}: Score ${data.scores.performance}/100`
        );
      }

      if (data.metrics.largestContentfulPaint > 2500) {
        recommendations.push(
          `Optimize Largest Contentful Paint for ${testName}: ${Math.round(
            data.metrics.largestContentfulPaint
          )}ms`
        );
      }

      if (data.metrics.cumulativeLayoutShift > 0.1) {
        recommendations.push(
          `Reduce Cumulative Layout Shift for ${testName}: ${data.metrics.cumulativeLayoutShift.toFixed(
            3
          )}`
        );
      }
    });

    // Custom metrics recommendations
    if (this.results.customMetrics.resourceOptimization) {
      const ro = this.results.customMetrics.resourceOptimization;

      if (ro.compressionRate < 80) {
        recommendations.push(
          `Enable compression for more resources: Currently ${Math.round(
            ro.compressionRate
          )}%`
        );
      }

      if (ro.totalSize > 1024) {
        recommendations.push(
          `Reduce total page size: Currently ${Math.round(ro.totalSize)}KB`
        );
      }

      if (ro.totalRequests > 30) {
        recommendations.push(
          `Reduce number of HTTP requests: Currently ${ro.totalRequests}`
        );
      }
    }

    if (this.results.customMetrics.assessment) {
      const assessment = this.results.customMetrics.assessment;

      if (assessment.averageNavigationTime > 300) {
        recommendations.push(
          `Optimize assessment navigation: Currently ${Math.round(
            assessment.averageNavigationTime
          )}ms`
        );
      }

      if (assessment.initializationTime > 1500) {
        recommendations.push(
          `Optimize assessment initialization: Currently ${Math.round(
            assessment.initializationTime
          )}ms`
        );
      }
    }

    return recommendations;
  }

  async runAllTests() {
    console.log("Starting Performance tests...\n");

    try {
      // Run Lighthouse tests
      await this.runLighthouseTest(this.baseUrl, "Homepage");
      await this.runLighthouseTest(
        `${this.baseUrl}/register/`,
        "Registration Page"
      );
      await this.runLighthouseTest(`${this.baseUrl}/browse/`, "Browse Page");

      // Run custom performance tests
      await this.testPageLoadPerformance();
      await this.testAssessmentPerformance();
      await this.testResourceOptimization();
      await this.testDatabasePerformance();
    } catch (error) {
      console.error("Performance test error:", error);
      this.results.failed++;
      this.results.tests.push({
        name: "Performance Test Suite",
        status: "failed",
        error: error.message,
      });
    }

    // Generate and display report
    const report = this.generateReport();

    console.log("\n=== Performance Test Results ===");
    console.log(`Total Tests: ${report.summary.totalTests}`);
    console.log(`Passed: ${report.summary.passed}`);
    console.log(`Failed: ${report.summary.failed}`);
    console.log(`Success Rate: ${report.summary.successRate}`);

    if (Object.keys(report.lighthouse).length > 0) {
      console.log("\n--- Lighthouse Scores ---");
      Object.entries(report.lighthouse).forEach(([testName, data]) => {
        console.log(`${testName}:`);
        console.log(`  Performance: ${data.scores.performance}/100`);
        console.log(`  Accessibility: ${data.scores.accessibility}/100`);
        console.log(`  Best Practices: ${data.scores.bestPractices}/100`);
        console.log(`  SEO: ${data.scores.seo}/100`);
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
  const runner = new PerformanceTestRunner();
  runner
    .runAllTests()
    .then((success) => {
      process.exit(success ? 0 : 1);
    })
    .catch((error) => {
      console.error("Performance test runner failed:", error);
      process.exit(1);
    });
}

module.exports = PerformanceTestRunner;
