<?php
/**
 * LibreKB v1 to v2 Database Migration Script - Standalone Version
 * This script migrates the database from v1 to v2 format
 * Self-contained version that only requires database configuration
 */

// Start output buffering to prevent header issues
ob_start();

// Database Configuration - Update these values to match your database
$db_host = 'localhost';
$db_user = 'X';
$db_pass = 'X';
$db_name = 'X';

echo "<!DOCTYPE html>\n";
echo "<html>\n";
echo "<head>\n";
echo "    <title>LibreKB v1 to v2 Migration</title>\n";
echo "    <style>\n";
echo "        body { font-family: Arial, sans-serif; margin: 40px; }\n";
echo "        .success { color: green; }\n";
echo "        .error { color: red; }\n";
echo "        .info { color: blue; }\n";
echo "        .warning { color: orange; }\n";
echo "    </style>\n";
echo "</head>\n";
echo "<body>\n";
echo "<h1>LibreKB v1 to v2 Database Migration</h1>\n";

try {
    // Connect to database directly using PDO
    echo "<p class='info'>Connecting to database...</p>\n";
    
    $dsn = "mysql:host={$db_host};dbname={$db_name}";
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if (!$pdo) {
        throw new Exception("Could not connect to database");
    }
    echo "<p class='success'>✓ Database connection successful</p>\n";
    
    // Check if settings table exists
    echo "<p class='info'>Checking settings table...</p>\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'settings'");
    if ($stmt->rowCount() == 0) {
        throw new Exception("Settings table does not exist. Please ensure LibreKB v1 is properly installed.");
    }
    echo "<p class='success'>✓ Settings table found</p>\n";
    
    // Check for version setting
    echo "<p class='info'>Checking for version setting...</p>\n";
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE name = 'version'");
    $stmt->execute();
    $versionSetting = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($versionSetting) {
        echo "<p class='success'>✓ Version setting found: " . htmlspecialchars($versionSetting['value']) . "</p>\n";
        
        // Delete version setting as it's no longer used
        echo "<p class='info'>Removing version setting (no longer used)...</p>\n";
        $stmt = $pdo->prepare("DELETE FROM settings WHERE name = 'version'");
        if ($stmt->execute()) {
            echo "<p class='success'>✓ Version setting removed</p>\n";
        } else {
            throw new Exception("Failed to remove version setting");
        }
    } else {
        throw new Exception("Version setting not found. This does not appear to be a valid LibreKB v1 installation, or it has already been upgraded to v2.");
    }
    
    // Add kb_visibility setting
    echo "<p class='info'>Adding kb_visibility setting...</p>\n";
    
    // Check if kb_visibility already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE name = 'kb_visibility'");
    $stmt->execute();
    $visibilityExists = $stmt->fetchColumn();
    
    if ($visibilityExists > 0) {
        echo "<p class='warning'>⚠ kb_visibility setting already exists, skipping...</p>\n";
    } else {
        $stmt = $pdo->prepare("INSERT INTO settings (name, value) VALUES ('kb_visibility', 'public')");
        if ($stmt->execute()) {
            echo "<p class='success'>✓ kb_visibility setting added with value 'public'</p>\n";
        } else {
            throw new Exception("Failed to add kb_visibility setting");
        }
    }
    
    // Check if categories table exists
    echo "<p class='info'>Checking categories table...</p>\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'categories'");
    if ($stmt->rowCount() == 0) {
        throw new Exception("Categories table does not exist. Please ensure LibreKB v1 is properly installed.");
    }
    echo "<p class='success'>✓ Categories table found</p>\n";
    
    // Check if parent column already exists in categories table
    echo "<p class='info'>Checking for parent column in categories table...</p>\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM categories LIKE 'parent'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='warning'>⚠ Parent column already exists in categories table, skipping...</p>\n";
    } else {
        // Add parent column to categories table
        echo "<p class='info'>Adding parent column to categories table...</p>\n";
        $stmt = $pdo->query("ALTER TABLE categories ADD COLUMN parent INT(6) DEFAULT NULL");
        if ($stmt) {
            echo "<p class='success'>✓ Parent column added to categories table</p>\n";
        } else {
            throw new Exception("Failed to add parent column to categories table");
        }
    }
    
    // Check if users table exists
    echo "<p class='info'>Checking users table...</p>\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() == 0) {
        throw new Exception("Users table does not exist. Please ensure LibreKB v1 is properly installed.");
    }
    echo "<p class='success'>✓ Users table found</p>\n";
    
    // Check if name column already exists
    echo "<p class='info'>Checking for name column in users table...</p>\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'name'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='warning'>⚠ Name column already exists in users table, skipping...</p>\n";
    } else {
        // Add name column to users table
        echo "<p class='info'>Adding name column to users table...</p>\n";
        $stmt = $pdo->query("ALTER TABLE users ADD COLUMN name VARCHAR(255) NOT NULL DEFAULT 'User'");
        if ($stmt) {
            echo "<p class='success'>✓ Name column added to users table</p>\n";
        } else {
            throw new Exception("Failed to add name column to users table");
        }
        
        // Update all existing users to have the default name 'User'
        echo "<p class='info'>Setting default name for existing users...</p>\n";
        $stmt = $pdo->query("UPDATE users SET name = 'User' WHERE name = '' OR name IS NULL");
        if ($stmt) {
            $affectedRows = $stmt->rowCount();
            echo "<p class='success'>✓ Updated user(s) with default name 'User'</p>\n";
        } else {
            throw new Exception("Failed to update existing users with default name");
        }
    }
    
    echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin: 20px 0; border-radius: 5px;'>\n";
    echo "<h2>Migration Complete!</h2>\n";
    echo "<p><strong>The database migration from LibreKB v1 to v2 has been completed successfully.</strong></p>\n";
    echo "<p><strong style='color: red;'>IMPORTANT:</strong> Please delete this migration file (v1-to-v2-db-migration-standalone.php) from your server for security reasons.</p>\n";
    echo "<p>You can now use LibreKB v2 with all the new features!</p>\n";
    echo "</div>\n";
    
} catch (PDOException $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px;'>\n";
    echo "<h2>Database Connection Failed</h2>\n";
    echo "<p class='error'>Database Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p>Please check your database configuration at the top of this script and ensure:</p>\n";
    echo "<ul>\n";
    echo "<li>Database host, username, password, and database name are correct</li>\n";
    echo "<li>Database server is running</li>\n";
    echo "<li>Database user has proper permissions</li>\n";
    echo "</ul>\n";
    echo "</div>\n";
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px;'>\n";
    echo "<h2>Migration Failed</h2>\n";
    echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p>Please check your database configuration and ensure LibreKB v1 is properly installed before running this migration.</p>\n";
    echo "</div>\n";
}

echo "</body>\n";
echo "</html>\n";

// End output buffering and flush
ob_end_flush();
?>
