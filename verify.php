<?php
/**
 * System Verification Script
 * Checks if all requirements are met for BEMS to run properly
 */

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'>";
echo "<title>BEMS - System Verification</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    h1 { color: #0d6efd; }
    .check { padding: 10px; margin: 10px 0; border-left: 4px solid #0d6efd; background: #f0f8ff; }
    .pass { border-left-color: #28a745; background: #e8f5e9; }
    .fail { border-left-color: #dc3545; background: #ffebee; }
    .warning { border-left-color: #ffc107; background: #fff8e1; }
    .status { font-weight: bold; }
    .pass .status { color: #28a745; }
    .fail .status { color: #dc3545; }
    .warning .status { color: #ffc107; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    th, td { text-align: left; padding: 12px; border-bottom: 1px solid #ddd; }
    th { background: #f8f9fa; font-weight: bold; }
    .summary { margin-top: 30px; padding: 15px; border-radius: 5px; }
    .summary.pass { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
    .summary.fail { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
</style></head><body>";

echo "<div class='container'>";
echo "<h1><i>✓</i> BEMS - System Verification</h1>";

$checks = [];
$passCount = 0;
$failCount = 0;

// PHP Version
echo "<div class='check'>";
if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
    echo "<span class='status pass'>✓ PHP Version OK</span> - " . PHP_VERSION;
    $passCount++;
    $checks[] = ['item' => 'PHP Version', 'status' => 'PASS', 'details' => PHP_VERSION];
} else {
    echo "<span class='status fail'>✗ PHP Version</span> - Required: 7.4+, Found: " . PHP_VERSION;
    $failCount++;
    $checks[] = ['item' => 'PHP Version', 'status' => 'FAIL', 'details' => 'Required 7.4+, found ' . PHP_VERSION];
}
echo "</div>";

// MySQL Extension
echo "<div class='check'>";
if (extension_loaded('mysqli')) {
    echo "<span class='status pass'>✓ MySQLi Extension</span> - Installed";
    $passCount++;
    $checks[] = ['item' => 'MySQLi Extension', 'status' => 'PASS', 'details' => 'Installed'];
} else {
    echo "<span class='status fail'>✗ MySQLi Extension</span> - Not installed";
    $failCount++;
    $checks[] = ['item' => 'MySQLi Extension', 'status' => 'FAIL', 'details' => 'Not installed'];
}
echo "</div>";

// JSON Extension
echo "<div class='check'>";
if (extension_loaded('json')) {
    echo "<span class='status pass'>✓ JSON Extension</span> - Installed";
    $passCount++;
    $checks[] = ['item' => 'JSON Extension', 'status' => 'PASS', 'details' => 'Installed'];
} else {
    echo "<span class='status fail'>✗ JSON Extension</span> - Not installed";
    $failCount++;
    $checks[] = ['item' => 'JSON Extension', 'status' => 'FAIL', 'details' => 'Not installed'];
}
echo "</div>";

// Directory Permissions
$dirs = [
    'config' => __DIR__ . '/config',
    'storage/logs' => __DIR__ . '/storage/logs',
    'public' => __DIR__ . '/public',
    'assets' => __DIR__ . '/assets'
];

echo "<h3>Directory Permissions</h3>";
foreach ($dirs as $name => $path) {
    echo "<div class='check'>";
    if (is_dir($path) && is_readable($path)) {
        echo "<span class='status pass'>✓ " . $name . " Directory</span> - Readable";
        $passCount++;
        $checks[] = ['item' => $name . ' Directory', 'status' => 'PASS', 'details' => 'Readable'];
    } else {
        echo "<span class='status fail'>✗ " . $name . " Directory</span> - Not readable";
        $failCount++;
        $checks[] = ['item' => $name . ' Directory', 'status' => 'FAIL', 'details' => 'Not readable'];
    }
    echo "</div>";
}

// Database Connection
echo "<h3>Database Connection</h3>";
echo "<div class='check'>";
require_once __DIR__ . '/config/config.php';
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    if ($conn->connect_error) {
        echo "<span class='status fail'>✗ Database Connection</span> - " . $conn->connect_error;
        $failCount++;
        $checks[] = ['item' => 'Database Connection', 'status' => 'FAIL', 'details' => $conn->connect_error];
    } else {
        echo "<span class='status pass'>✓ Database Connection OK</span> - Connected to " . DB_NAME;
        $passCount++;
        $checks[] = ['item' => 'Database Connection', 'status' => 'PASS', 'details' => 'Connected to ' . DB_NAME];
        
        // Check tables
        $tables = ['users', 'events', 'attendees', 'audit_logs'];
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result && $result->num_rows > 0) {
                echo "<br><span class='status pass'>✓ Table: $table</span>";
                $passCount++;
                $checks[] = ['item' => 'Database Table: ' . $table, 'status' => 'PASS', 'details' => 'Exists'];
            } else {
                echo "<br><span class='status warning'>⚠ Table: $table</span> - Not found (run init-db.php)";
                $checks[] = ['item' => 'Database Table: ' . $table, 'status' => 'WARNING', 'details' => 'Not found'];
            }
        }
        $conn->close();
    }
} catch (Exception $e) {
    echo "<span class='status fail'>✗ Database Connection</span> - " . $e->getMessage();
    $failCount++;
    $checks[] = ['item' => 'Database Connection', 'status' => 'FAIL', 'details' => $e->getMessage()];
}
echo "</div>";

// File Checks
echo "<h3>Required Files</h3>";
$files = [
    'config/config.php' => __DIR__ . '/config/config.php',
    'config/init-db.php' => __DIR__ . '/config/init-db.php',
    'src/Core.php' => __DIR__ . '/src/Core.php',
    'src/Models.php' => __DIR__ . '/src/Models.php',
    'api/auth.php' => __DIR__ . '/api/auth.php',
    'public/index.html' => __DIR__ . '/public/index.html',
    'assets/css/style.css' => __DIR__ . '/assets/css/style.css',
    'assets/js/api.js' => __DIR__ . '/assets/js/api.js'
];

foreach ($files as $name => $path) {
    echo "<div class='check'>";
    if (file_exists($path)) {
        echo "<span class='status pass'>✓ " . $name . "</span> - Found";
        $passCount++;
        $checks[] = ['item' => 'File: ' . $name, 'status' => 'PASS', 'details' => 'Found'];
    } else {
        echo "<span class='status fail'>✗ " . $name . "</span> - Not found";
        $failCount++;
        $checks[] = ['item' => 'File: ' . $name, 'status' => 'FAIL', 'details' => 'Not found'];
    }
    echo "</div>";
}

// Summary
echo "<div class='summary " . ($failCount === 0 ? 'pass' : 'fail') . "'>";
echo "<h2>Verification Summary</h2>";
echo "<p><strong>Passed:</strong> " . $passCount . " | <strong>Failed:</strong> " . $failCount . "</p>";

if ($failCount === 0) {
    echo "<p>✓ All checks passed! The system is ready to use.</p>";
    echo "<p><a href='http://localhost/BusinessEventManagementSystem/public/index.html' style='color: #0d6efd; text-decoration: none; font-weight: bold;'>→ Go to Application</a></p>";
    echo "<p>First-time users: Run <a href='config/init-db.php' style='color: #0d6efd;'>Database Initialization</a> if database tables are not created.</p>";
} else {
    echo "<p>✗ Some checks failed. Please resolve the issues before proceeding.</p>";
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ul>";
    if ($failCount > 0) {
        echo "<li>Check PHP extensions: MySQLi and JSON must be enabled</li>";
        echo "<li>Verify database credentials in config/config.php</li>";
        echo "<li>Ensure all required files are in place</li>";
        echo "<li>Check file and directory permissions</li>";
    }
    echo "</ul>";
}

echo "</div>";

// Detailed Report Table
echo "<h3>Detailed Report</h3>";
echo "<table>";
echo "<tr><th>Item</th><th>Status</th><th>Details</th></tr>";
foreach ($checks as $check) {
    $statusClass = strtolower($check['status']);
    echo "<tr>";
    echo "<td>" . $check['item'] . "</td>";
    echo "<td><span class='status $statusClass'>" . $check['status'] . "</span></td>";
    echo "<td>" . $check['details'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<p style='margin-top: 30px; color: #666; font-size: 12px;'>";
echo "Verification Time: " . date('Y-m-d H:i:s') . "<br>";
echo "Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? '') . "<br>";
echo "</p>";

echo "</div></body></html>";
