<?php
/**
 * Comprehensive test runner for MCQHome Theme
 */

// Prevent direct access
if (!defined('ABSPATH') && !defined('WP_CLI')) {
    // Allow running from command line
    if (php_sapi_name() === 'cli') {
        // Set up WordPress environment for CLI
        $wp_load_path = dirname(__DIR__) . '/wp-load.php';
        if (file_exists($wp_load_path)) {
            require_once $wp_load_path;
        } else {
            echo "WordPress not found. Please run from WordPress root directory.\n";
            exit(1);
        }
    } else {
        exit('Direct access not allowed');
    }
}

class MCQHomeTestRunner {
    
    private $results = [
        'php' => ['passed' => 0, 'failed' => 0, 'tests' => []],
        'js' => ['passed' => 0, 'failed' => 0, 'tests' => []],
        'e2e' => ['passed' => 0, 'failed' => 0, 'tests' => []],
        'performance' => ['passed' => 0, 'failed' => 0, 'tests' => []],
        'accessibility' => ['passed' => 0, 'failed' => 0, 'tests' => []]
    ];
    
    private $startTime;
    private $verbose = false;
    
    public function __construct($verbose = false) {
        $this->verbose = $verbose;
        $this->startTime = microtime(true);
    }
    
    /**
     * Run all test suites
     */
    public function runAllTests() {
        $this->printHeader();
        
        // Run PHP tests
        $this->runPHPTests();
        
        // Run JavaScript tests
        $this->runJavaScriptTests();
        
        // Run E2E tests
        $this->runE2ETests();
        
        // Run Performance tests
        $this->runPerformanceTests();
        
        // Run Accessibility tests
        $this->runAccessibilityTests();
        
        // Print summary
        $this->printSummary();
        
        return $this->getOverallResult();
    }
    
    /**
     * Run PHP unit and integration tests
     */
    private function runPHPTests() {
        $this->printSection('PHP Tests (PHPUnit)');
        
        $phpunitPath = $this->findPHPUnit();
        if (!$phpunitPath) {
            $this->results['php']['failed']++;
            $this->results['php']['tests'][] = [
                'name' => 'PHPUnit Setup',
                'status' => 'failed',
                'error' => 'PHPUnit not found'
            ];
            echo "✗ PHPUnit not found. Please install PHPUnit.\n";
            return;
        }
        
        $configPath = dirname(__DIR__) . '/phpunit.xml';
        if (!file_exists($configPath)) {
            $this->results['php']['failed']++;
            $this->results['php']['tests'][] = [
                'name' => 'PHPUnit Configuration',
                'status' => 'failed',
                'error' => 'phpunit.xml not found'
            ];
            echo "✗ phpunit.xml not found.\n";
            return;
        }
        
        // Run PHPUnit tests
        $command = "$phpunitPath --configuration $configPath --testdox";
        if (!$this->verbose) {
            $command .= ' 2>/dev/null';
        }
        
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            $this->results['php']['passed']++;
            $this->results['php']['tests'][] = [
                'name' => 'PHPUnit Test Suite',
                'status' => 'passed'
            ];
            echo "✓ PHP tests passed\n";
        } else {
            $this->results['php']['failed']++;
            $this->results['php']['tests'][] = [
                'name' => 'PHPUnit Test Suite',
                'status' => 'failed',
                'error' => 'Some PHP tests failed'
            ];
            echo "✗ PHP tests failed\n";
        }
        
        if ($this->verbose && !empty($output)) {
            echo implode("\n", $output) . "\n";
        }
    }
    
    /**
     * Run JavaScript tests
     */
    private function runJavaScriptTests() {
        $this->printSection('JavaScript Tests (Jest)');
        
        $packageJsonPath = dirname(__DIR__) . '/package.json';
        if (!file_exists($packageJsonPath)) {
            $this->results['js']['failed']++;
            $this->results['js']['tests'][] = [
                'name' => 'Jest Setup',
                'status' => 'failed',
                'error' => 'package.json not found'
            ];
            echo "✗ package.json not found.\n";
            return;
        }
        
        // Check if node_modules exists
        $nodeModulesPath = dirname(__DIR__) . '/node_modules';
        if (!is_dir($nodeModulesPath)) {
            echo "Installing npm dependencies...\n";
            $installCommand = 'cd ' . dirname(__DIR__) . ' && npm install';
            exec($installCommand, $installOutput, $installReturn);
            
            if ($installReturn !== 0) {
                $this->results['js']['failed']++;
                $this->results['js']['tests'][] = [
                    'name' => 'NPM Install',
                    'status' => 'failed',
                    'error' => 'npm install failed'
                ];
                echo "✗ npm install failed\n";
                return;
            }
        }
        
        // Run Jest tests
        $command = 'cd ' . dirname(__DIR__) . ' && npm run test:js';
        if (!$this->verbose) {
            $command .= ' 2>/dev/null';
        }
        
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            $this->results['js']['passed']++;
            $this->results['js']['tests'][] = [
                'name' => 'Jest Test Suite',
                'status' => 'passed'
            ];
            echo "✓ JavaScript tests passed\n";
        } else {
            $this->results['js']['failed']++;
            $this->results['js']['tests'][] = [
                'name' => 'Jest Test Suite',
                'status' => 'failed',
                'error' => 'Some JavaScript tests failed'
            ];
            echo "✗ JavaScript tests failed\n";
        }
        
        if ($this->verbose && !empty($output)) {
            echo implode("\n", $output) . "\n";
        }
    }
    
    /**
     * Run E2E tests
     */
    private function runE2ETests() {
        $this->printSection('End-to-End Tests (Puppeteer)');
        
        $e2eScript = dirname(__DIR__) . '/tests/e2e/run-e2e-tests.js';
        if (!file_exists($e2eScript)) {
            $this->results['e2e']['failed']++;
            $this->results['e2e']['tests'][] = [
                'name' => 'E2E Setup',
                'status' => 'failed',
                'error' => 'E2E test script not found'
            ];
            echo "✗ E2E test script not found.\n";
            return;
        }
        
        // Check if site is accessible
        $testUrl = getenv('TEST_URL') ?: 'http://localhost:8080';
        $headers = @get_headers($testUrl);
        if (!$headers || strpos($headers[0], '200') === false) {
            $this->results['e2e']['failed']++;
            $this->results['e2e']['tests'][] = [
                'name' => 'Site Accessibility',
                'status' => 'failed',
                'error' => "Test site not accessible at $testUrl"
            ];
            echo "✗ Test site not accessible at $testUrl\n";
            echo "Please start your local server or set TEST_URL environment variable.\n";
            return;
        }
        
        // Run E2E tests
        $command = "node $e2eScript";
        if (!$this->verbose) {
            $command .= ' 2>/dev/null';
        }
        
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            $this->results['e2e']['passed']++;
            $this->results['e2e']['tests'][] = [
                'name' => 'E2E Test Suite',
                'status' => 'passed'
            ];
            echo "✓ E2E tests passed\n";
        } else {
            $this->results['e2e']['failed']++;
            $this->results['e2e']['tests'][] = [
                'name' => 'E2E Test Suite',
                'status' => 'failed',
                'error' => 'Some E2E tests failed'
            ];
            echo "✗ E2E tests failed\n";
        }
        
        if ($this->verbose && !empty($output)) {
            echo implode("\n", $output) . "\n";
        }
    }
    
    /**
     * Run Performance tests
     */
    private function runPerformanceTests() {
        $this->printSection('Performance Tests (Lighthouse)');
        
        $perfScript = dirname(__DIR__) . '/tests/performance/run-performance-tests.js';
        if (!file_exists($perfScript)) {
            $this->results['performance']['failed']++;
            $this->results['performance']['tests'][] = [
                'name' => 'Performance Setup',
                'status' => 'failed',
                'error' => 'Performance test script not found'
            ];
            echo "✗ Performance test script not found.\n";
            return;
        }
        
        // Run Performance tests
        $command = "node $perfScript";
        if (!$this->verbose) {
            $command .= ' 2>/dev/null';
        }
        
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            $this->results['performance']['passed']++;
            $this->results['performance']['tests'][] = [
                'name' => 'Performance Test Suite',
                'status' => 'passed'
            ];
            echo "✓ Performance tests passed\n";
        } else {
            $this->results['performance']['failed']++;
            $this->results['performance']['tests'][] = [
                'name' => 'Performance Test Suite',
                'status' => 'failed',
                'error' => 'Some performance tests failed'
            ];
            echo "✗ Performance tests failed\n";
        }
        
        if ($this->verbose && !empty($output)) {
            echo implode("\n", $output) . "\n";
        }
    }
    
    /**
     * Run Accessibility tests
     */
    private function runAccessibilityTests() {
        $this->printSection('Accessibility Tests (axe-core)');
        
        $a11yScript = dirname(__DIR__) . '/tests/accessibility/run-accessibility-tests.js';
        if (!file_exists($a11yScript)) {
            $this->results['accessibility']['failed']++;
            $this->results['accessibility']['tests'][] = [
                'name' => 'Accessibility Setup',
                'status' => 'failed',
                'error' => 'Accessibility test script not found'
            ];
            echo "✗ Accessibility test script not found.\n";
            return;
        }
        
        // Run Accessibility tests
        $command = "node $a11yScript";
        if (!$this->verbose) {
            $command .= ' 2>/dev/null';
        }
        
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            $this->results['accessibility']['passed']++;
            $this->results['accessibility']['tests'][] = [
                'name' => 'Accessibility Test Suite',
                'status' => 'passed'
            ];
            echo "✓ Accessibility tests passed\n";
        } else {
            $this->results['accessibility']['failed']++;
            $this->results['accessibility']['tests'][] = [
                'name' => 'Accessibility Test Suite',
                'status' => 'failed',
                'error' => 'Some accessibility tests failed'
            ];
            echo "✗ Accessibility tests failed\n";
        }
        
        if ($this->verbose && !empty($output)) {
            echo implode("\n", $output) . "\n";
        }
    }
    
    /**
     * Find PHPUnit executable
     */
    private function findPHPUnit() {
        $paths = [
            dirname(__DIR__) . '/vendor/bin/phpunit',
            'phpunit',
            '/usr/local/bin/phpunit',
            '/usr/bin/phpunit'
        ];
        
        foreach ($paths as $path) {
            if (is_executable($path)) {
                return $path;
            }
            
            // Check if command exists
            $output = [];
            $returnCode = 0;
            exec("which $path 2>/dev/null", $output, $returnCode);
            if ($returnCode === 0 && !empty($output)) {
                return $path;
            }
        }
        
        return null;
    }
    
    /**
     * Print test header
     */
    private function printHeader() {
        echo "\n";
        echo "=================================================\n";
        echo "         MCQHome Theme Test Suite\n";
        echo "=================================================\n";
        echo "Running comprehensive tests...\n\n";
    }
    
    /**
     * Print section header
     */
    private function printSection($title) {
        echo "\n--- $title ---\n";
    }
    
    /**
     * Print test summary
     */
    private function printSummary() {
        $totalPassed = 0;
        $totalFailed = 0;
        $totalTime = microtime(true) - $this->startTime;
        
        echo "\n";
        echo "=================================================\n";
        echo "                TEST SUMMARY\n";
        echo "=================================================\n";
        
        foreach ($this->results as $suite => $result) {
            $totalPassed += $result['passed'];
            $totalFailed += $result['failed'];
            $total = $result['passed'] + $result['failed'];
            
            if ($total > 0) {
                $percentage = round(($result['passed'] / $total) * 100, 1);
                echo sprintf("%-15s: %d/%d passed (%s%%)\n", 
                    ucfirst($suite), 
                    $result['passed'], 
                    $total, 
                    $percentage
                );
            } else {
                echo sprintf("%-15s: No tests run\n", ucfirst($suite));
            }
        }
        
        echo "\n";
        $grandTotal = $totalPassed + $totalFailed;
        if ($grandTotal > 0) {
            $overallPercentage = round(($totalPassed / $grandTotal) * 100, 1);
            echo "OVERALL RESULT  : $totalPassed/$grandTotal passed ($overallPercentage%)\n";
        } else {
            echo "OVERALL RESULT  : No tests run\n";
        }
        
        echo "EXECUTION TIME  : " . round($totalTime, 2) . " seconds\n";
        
        if ($totalFailed > 0) {
            echo "\nFAILED TESTS:\n";
            foreach ($this->results as $suite => $result) {
                foreach ($result['tests'] as $test) {
                    if ($test['status'] === 'failed') {
                        echo "- [$suite] {$test['name']}";
                        if (isset($test['error'])) {
                            echo ": {$test['error']}";
                        }
                        echo "\n";
                    }
                }
            }
        }
        
        echo "\n";
    }
    
    /**
     * Get overall test result
     */
    private function getOverallResult() {
        foreach ($this->results as $result) {
            if ($result['failed'] > 0) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Generate test report
     */
    public function generateReport($format = 'json') {
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'execution_time' => microtime(true) - $this->startTime,
            'results' => $this->results,
            'summary' => [
                'total_passed' => array_sum(array_column($this->results, 'passed')),
                'total_failed' => array_sum(array_column($this->results, 'failed')),
                'overall_success' => $this->getOverallResult()
            ]
        ];
        
        switch ($format) {
            case 'json':
                return json_encode($report, JSON_PRETTY_PRINT);
            
            case 'xml':
                return $this->arrayToXml($report);
            
            case 'html':
                return $this->generateHtmlReport($report);
            
            default:
                return $report;
        }
    }
    
    /**
     * Convert array to XML
     */
    private function arrayToXml($array, $rootElement = 'testReport') {
        $xml = new SimpleXMLElement("<?xml version=\"1.0\"?><$rootElement></$rootElement>");
        $this->arrayToXmlRecursive($array, $xml);
        return $xml->asXML();
    }
    
    /**
     * Recursive helper for array to XML conversion
     */
    private function arrayToXmlRecursive($array, &$xml) {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'item';
                }
                $subnode = $xml->addChild($key);
                $this->arrayToXmlRecursive($value, $subnode);
            } else {
                $xml->addChild($key, htmlspecialchars($value));
            }
        }
    }
    
    /**
     * Generate HTML report
     */
    private function generateHtmlReport($report) {
        $html = '<!DOCTYPE html>
<html>
<head>
    <title>MCQHome Theme Test Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { background: #f5f5f5; padding: 20px; border-radius: 5px; }
        .summary { margin: 20px 0; }
        .suite { margin: 20px 0; border: 1px solid #ddd; border-radius: 5px; }
        .suite-header { background: #e9e9e9; padding: 10px; font-weight: bold; }
        .test { padding: 10px; border-bottom: 1px solid #eee; }
        .passed { color: green; }
        .failed { color: red; }
        .error { color: #666; font-style: italic; }
    </style>
</head>
<body>
    <div class="header">
        <h1>MCQHome Theme Test Report</h1>
        <p>Generated: ' . $report['timestamp'] . '</p>
        <p>Execution Time: ' . round($report['execution_time'], 2) . ' seconds</p>
    </div>
    
    <div class="summary">
        <h2>Summary</h2>
        <p>Total Passed: <span class="passed">' . $report['summary']['total_passed'] . '</span></p>
        <p>Total Failed: <span class="failed">' . $report['summary']['total_failed'] . '</span></p>
        <p>Overall Result: ' . ($report['summary']['overall_success'] ? '<span class="passed">PASSED</span>' : '<span class="failed">FAILED</span>') . '</p>
    </div>';
        
        foreach ($report['results'] as $suiteName => $suite) {
            $html .= '<div class="suite">
                <div class="suite-header">' . ucfirst($suiteName) . ' Tests</div>';
            
            foreach ($suite['tests'] as $test) {
                $statusClass = $test['status'] === 'passed' ? 'passed' : 'failed';
                $html .= '<div class="test">
                    <span class="' . $statusClass . '">' . $test['name'] . ' - ' . strtoupper($test['status']) . '</span>';
                
                if (isset($test['error'])) {
                    $html .= '<div class="error">' . htmlspecialchars($test['error']) . '</div>';
                }
                
                $html .= '</div>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</body></html>';
        
        return $html;
    }
}

// Run tests if called directly
if (php_sapi_name() === 'cli' && (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME']) || defined('WP_CLI'))) {
    $verbose = in_array('--verbose', $argv) || in_array('-v', $argv);
    $format = 'text';
    
    // Check for format option
    foreach ($argv as $arg) {
        if (strpos($arg, '--format=') === 0) {
            $format = substr($arg, 9);
            break;
        }
    }
    
    $runner = new MCQHomeTestRunner($verbose);
    $success = $runner->runAllTests();
    
    // Generate report if requested
    if ($format !== 'text') {
        $report = $runner->generateReport($format);
        
        $reportFile = dirname(__DIR__) . "/tests/reports/test-report-" . date('Y-m-d-H-i-s') . ".$format";
        $reportDir = dirname($reportFile);
        
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0755, true);
        }
        
        file_put_contents($reportFile, $report);
        echo "Report saved to: $reportFile\n";
    }
    
    exit($success ? 0 : 1);
}