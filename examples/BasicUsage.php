<?php

/**
 * Basic Usage Example
 * 
 * This example shows how to use the Courier Customer Delivery Stats package in any PHP environment.
 * 
 */

require_once 'vendor/autoload.php';

use Ham\BdCourier\CustomerDeliveryStats\CourierCustomerStats;
use Ham\BdCourier\CustomerDeliveryStats\Helpers\PhoneValidator;

// Simple .env file loader (for demonstration purposes)
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Load environment variables from .env file
loadEnv(__DIR__ . '/../.env');

// Example phone number used throughout this file
$examplePhoneNumber = '01786161430';

// Example 1: Using configuration array (COMMENTED OUT)
/*
$customerStats = new CourierCustomerStats([
    'pathao_user' => 'your_pathao_email@example.com',
    'pathao_password' => 'your_pathao_password',
    'steadfast_user' => 'your_steadfast_email@example.com',
    'steadfast_password' => 'your_steadfast_password',
    'redx_user' => 'your_redx_username',
    'redx_password' => 'your_redx_password',
]);
*/

// Example 2: Using environment variables (package will auto-detect from .env file)
echo "Loading credentials from .env file...\n";
echo "PATHAO_USER: " . (getenv('PATHAO_USER') ?: 'Not set') . "\n";
echo "STEADFAST_USER: " . (getenv('STEADFAST_USER') ?: 'Not set') . "\n";
echo "REDX_USER: " . (getenv('REDX_USER') ?: 'Not set') . "\n\n";

$customerStats = new CourierCustomerStats(); // Will automatically load from environment variables

try {
    // Check both services
    $phoneNumber = $examplePhoneNumber;
    $result = $customerStats->check($phoneNumber);
    
    echo "Customer Delivery Stats for: $phoneNumber\n";
    echo "==================================\n";
    
    // Display Pathao results
    if (isset($result['pathao'])) {
        echo "Pathao Results:\n";
        if (isset($result['pathao']['error'])) {
            echo "  Error: " . $result['pathao']['error'] . "\n";
        } else {
            echo "  Successful deliveries: " . $result['pathao']['success'] . "\n";
            echo "  Cancelled deliveries: " . $result['pathao']['cancel'] . "\n";
            echo "  Total deliveries: " . $result['pathao']['total'] . "\n";
        }
        echo "\n";
    }
    
    // Display Steadfast results
    if (isset($result['steadfast'])) {
        echo "Steadfast Results:\n";
        if (isset($result['steadfast']['error'])) {
            echo "  Error: " . $result['steadfast']['error'] . "\n";
        } else {
            echo "  Successful deliveries: " . $result['steadfast']['success'] . "\n";
            echo "  Cancelled deliveries: " . $result['steadfast']['cancel'] . "\n";
            echo "  Total deliveries: " . $result['steadfast']['total'] . "\n";
        }
        echo "\n";
    }
    
    // Display RedX results
    if (isset($result['redx'])) {
        echo "RedX Results:\n";
        if (isset($result['redx']['error'])) {
            echo "  Error: " . $result['redx']['error'] . "\n";
        } else {
            echo "  Successful deliveries: " . $result['redx']['success'] . "\n";
            echo "  Cancelled deliveries: " . $result['redx']['cancel'] . "\n";
            echo "  Total deliveries: " . $result['redx']['total'] . "\n";
        }
        echo "\n";
    }
    
    // Calculate risk score
    $totalOrders = ($result['pathao']['total'] ?? 0) + ($result['steadfast']['total'] ?? 0) + ($result['redx']['total'] ?? 0);
    $totalCancels = ($result['pathao']['cancel'] ?? 0) + ($result['steadfast']['cancel'] ?? 0) + ($result['redx']['cancel'] ?? 0);
    
    if ($totalOrders > 0) {
        $cancellationRate = ($totalCancels / $totalOrders) * 100;
        echo "Overall Statistics:\n";
        echo "  Total orders: $totalOrders\n";
        echo "  Total cancellations: $totalCancels\n";
        echo "  Cancellation rate: " . number_format($cancellationRate, 2) . "%\n";
        
        // Risk assessment
        if ($totalOrders < 3) {
            echo "  Risk Level: NEW CUSTOMER (insufficient data)\n";
        } elseif ($cancellationRate > 50) {
            echo "  Risk Level: HIGH RISK\n";
        } elseif ($cancellationRate > 25) {
            echo "  Risk Level: MEDIUM RISK\n";
        } else {
            echo "  Risk Level: LOW RISK\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Example 3: Check individual services
try {
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "Individual Service Checks\n";
    echo str_repeat("=", 50) . "\n";
    
    // Check only Pathao
    $pathaoResult = $customerStats->checkPathao($phoneNumber);
    echo "Pathao only check:\n";
    print_r($pathaoResult);
    
    // Check only Steadfast
    $steadfastResult = $customerStats->checkSteadfast($phoneNumber);
    echo "\nSteadfast only check:\n";
    print_r($steadfastResult);
    
    // Check only RedX
    $redxResult = $customerStats->checkRedX($phoneNumber);
    echo "\nRedX only check:\n";
    print_r($redxResult);
    
} catch (Exception $e) {
    echo "Individual check error: " . $e->getMessage() . "\n";
}

// Example 4: Phone number validation
echo "\n" . str_repeat("=", 50) . "\n";
echo "Phone Number Validation Examples\n";
echo str_repeat("=", 50) . "\n";

$testNumbers = [
    $phoneNumber,     // Valid - example phone number
    '01787654321',     // Valid
    '+8801712345678',  // Invalid (has country code)
    '8801712345678',   // Invalid (has country code)
    '01512345678',     // Invalid (wrong operator code)
    '017123456789',    // Invalid (too long)
    '0171234567',      // Invalid (too short)
];

foreach ($testNumbers as $number) {
    $isValid = PhoneValidator::isValid($number);
    $sanitized = PhoneValidator::sanitize($number);
    
    echo "Number: $number\n";
    echo "  Valid: " . ($isValid ? 'Yes' : 'No') . "\n";
    echo "  Sanitized: $sanitized\n";
    
    if (!$isValid) {
        $error = PhoneValidator::getValidationError($number);
        echo "  Error: $error\n";
    } else {
        $withCountryCode = PhoneValidator::withCountryCode($number);
        echo "  With country code: $withCountryCode\n";
    }
    echo "\n";
}

echo "Usage complete!\n"; 