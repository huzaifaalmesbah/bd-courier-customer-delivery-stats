# Universal Courier Fraud Checker BD

A universal PHP package for fraud detection across e-commerce platforms. Analyze customer order behavior through Pathao, Steadfast, and RedX couriers in Bangladesh. Works with **any PHP framework or CMS** including Laravel, WordPress, CodeIgniter, Symfony, and more.

> **Note**: This is a universal, framework-agnostic fork of [shahariar-ahmad/courier-fraud-checker-bd](https://packagist.org/packages/shahariar-ahmad/courier-fraud-checker-bd) by Shahariar Ahmad, enhanced to work with any PHP framework beyond just Laravel.

---

## ✨ Features

- **Framework Agnostic**: Works with Laravel, WordPress, CodeIgniter, Symfony, and any PHP project
- **Multi-Courier Support**: Check customer delivery history across Pathao, Steadfast, and RedX
- **Phone Number Validation**: Validates Bangladeshi phone numbers with proper format
- **Flexible Configuration**: Supports environment variables, configuration arrays, or framework-specific configs
- **Comprehensive Statistics**: Get success/cancel/total delivery statistics
- **Error Handling**: Graceful error handling with detailed error messages
- **Easy Integration**: Simple API with extensive documentation and examples

---

## ⚙️ Installation

### Install via Composer:

```bash
composer require huzaifaalmesbah/bd-courier-customer-delivery-stats
```

### Requirements:
- PHP 7.4 or higher
- Guzzle HTTP client (automatically installed)

---

## 🔧 Configuration

### Option 1: Environment Variables (Recommended)

Add these environment variables to your `.env` file or system environment:

```env
# Pathao Credentials
PATHAO_USER=your_pathao_email@example.com
PATHAO_PASSWORD=your_pathao_password

# Steadfast Credentials
STEADFAST_USER=your_steadfast_email@example.com
STEADFAST_PASSWORD=your_steadfast_password

# RedX Credentials
REDX_USER=your_redx_phone@example.com
REDX_PASSWORD=your_redx_password
```

### Option 2: Configuration Array

```php
use Ham\BdCourier\CustomerDeliveryStats\CourierCustomerStats;

$customerStats = new CourierCustomerStats([
    'pathao_user' => 'your_pathao_email@example.com',
    'pathao_password' => 'your_pathao_password',
    'steadfast_user' => 'your_steadfast_email@example.com',
    'steadfast_password' => 'your_steadfast_password',
    'redx_user' => 'your_redx_phone@example.com',
    'redx_password' => 'your_redx_password',
]);
```

---

## 🚀 Basic Usage

```php
<?php
require_once 'vendor/autoload.php';

use Ham\BdCourier\CustomerDeliveryStats\CourierCustomerStats;

// Initialize (will auto-load from environment variables)
$customerStats = new CourierCustomerStats();

// Check both services
$result = $customerStats->check('01712345678');

print_r($result);
```

**Output:**

```php
[
    'pathao' => [
        'success' => 5,
        'cancel' => 2,
        'total' => 7
    ],
    'steadfast' => [
        'success' => 3,
        'cancel' => 1,
        'total' => 4
    ],
    'redx' => [
        'success' => 6,
        'cancel' => 2,
        'total' => 8
    ]
]
```

---

## 📱 Phone Number Validation

The package automatically validates Bangladeshi phone numbers:

```php
use Ham\BdCourier\CustomerDeliveryStats\Helpers\PhoneValidator;

// Validate phone number
if (PhoneValidator::isValid('01712345678')) {
    echo "Valid phone number";
}

// Get validation error
$error = PhoneValidator::getValidationError('+8801712345678');
echo $error; // "Invalid Bangladeshi phone number..."

// Sanitize phone number
$clean = PhoneValidator::sanitize('+88 017-1234-5678');
echo $clean; // "01712345678"

// Format with country code
$withCode = PhoneValidator::withCountryCode('01712345678');
echo $withCode; // "+8801712345678"
```

**Validation Rules:**
- ✅ Valid: `01712345678`, `01876543219`
- ❌ Invalid: `+8801712345678`, `02171234567`, `1234567890`

---

## 🛠️ Advanced Usage

### Check Individual Services

```php
// Check only Pathao
$pathaoResult = $customerStats->checkPathao('01712345678');

// Check only Steadfast
$steadfastResult = $customerStats->checkSteadfast('01712345678');

// Check only RedX
$redxResult = $customerStats->checkRedX('01712345678');
```

### Error Handling

```php
try {
    $result = $customerStats->check('01712345678');
    
    if (isset($result['pathao']['error'])) {
        echo "Pathao Error: " . $result['pathao']['error'];
    }
    
    if (isset($result['steadfast']['error'])) {
        echo "Steadfast Error: " . $result['steadfast']['error'];
    }
    
    if (isset($result['redx']['error'])) {
        echo "RedX Error: " . $result['redx']['error'];
    }
    
} catch (Exception $e) {
    echo "Configuration Error: " . $e->getMessage();
}
```

### Fraud Risk Assessment

```php
$result = $customerStats->check('01712345678');

$totalOrders = ($result['pathao']['total'] ?? 0) + ($result['steadfast']['total'] ?? 0) + ($result['redx']['total'] ?? 0);
$totalCancels = ($result['pathao']['cancel'] ?? 0) + ($result['steadfast']['cancel'] ?? 0) + ($result['redx']['cancel'] ?? 0);

if ($totalOrders > 0) {
    $cancellationRate = ($totalCancels / $totalOrders) * 100;
    
    if ($totalOrders < 3) {
        $risk = 'NEW CUSTOMER';
    } elseif ($cancellationRate > 50) {
        $risk = 'HIGH RISK';
    } elseif ($cancellationRate > 25) {
        $risk = 'MEDIUM RISK';
    } else {
        $risk = 'LOW RISK';
    }
    
    echo "Risk Level: $risk (Cancellation Rate: {$cancellationRate}%)";
}
```

---

## 🔌 Framework Integration

### Laravel Integration

```php
// In your controller
use Ham\BdCourier\CustomerDeliveryStats\CourierCustomerStats;

class OrderController extends Controller
{
    public function checkCustomer(Request $request)
    {
        $customerStats = new CourierCustomerStats([
            'pathao_user' => env('PATHAO_USER'),
            'pathao_password' => env('PATHAO_PASSWORD'),
            'steadfast_user' => env('STEADFAST_USER'),
            'steadfast_password' => env('STEADFAST_PASSWORD'),
            'redx_user' => env('REDX_USER'),
            'redx_password' => env('REDX_PASSWORD'),
        ]);
        
        $result = $customerStats->check($request->phone);
        
        return response()->json($result);
    }
}
```

### WordPress Integration

```php
// In functions.php or as a plugin
require_once get_template_directory() . '/vendor/autoload.php';

use Ham\BdCourier\CustomerDeliveryStats\CourierCustomerStats;

function check_customer_delivery_stats($phone) {
    $customerStats = new CourierCustomerStats([
        'pathao_user' => get_option('pathao_user'),
        'pathao_password' => get_option('pathao_password'),
        'steadfast_user' => get_option('steadfast_user'),
        'steadfast_password' => get_option('steadfast_password'),
        'redx_user' => get_option('redx_user'),
        'redx_password' => get_option('redx_password'),
    ]);
    
    return $customerStats->check($phone);
}

// Use shortcode: [courier_delivery_stats]
add_shortcode('courier_delivery_stats', function() {
    // Return customer stats form HTML
});
```

### CodeIgniter Integration

```php
// In your controller (CI 3.x)
require_once APPPATH . '../vendor/autoload.php';

use Ham\BdCourier\CustomerDeliveryStats\CourierCustomerStats;

class Customer_stats extends CI_Controller 
{
    public function check() {
        $customerStats = new CourierCustomerStats([
            'pathao_user' => $this->config->item('pathao_user'),
            'pathao_password' => $this->config->item('pathao_password'),
            'steadfast_user' => $this->config->item('steadfast_user'),
            'steadfast_password' => $this->config->item('steadfast_password'),
            'redx_user' => $this->config->item('redx_user'),
            'redx_password' => $this->config->item('redx_password'),
        ]);
        
        $result = $customerStats->check($this->input->post('phone'));
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }
}
```

---

## 📁 Complete Examples

Check the `examples/` directory for complete integration examples:

- `examples/BasicUsage.php` - Basic usage in any PHP project

---

## 🧹 Troubleshooting

### Common Issues

1. **Missing Configuration**
   ```
   Error: Missing required configuration: pathao_user, pathao_password, redx_user, redx_password
   ```
   **Solution**: Ensure all credentials are set in environment variables or configuration array.

2. **Invalid Phone Number**
   ```
   Error: Invalid Bangladeshi phone number. Please use the local format...
   ```
   **Solution**: Use local BD format like `01712345678` without `+88` prefix.

3. **HTTP Request Failed**
   ```
   Error: HTTP POST request failed: Connection timeout
   ```
   **Solution**: Check internet connection and API endpoint availability.

4. **Guzzle HTTP Client Missing**
   ```
   Error: Class 'GuzzleHttp\Client' not found
   ```
   **Solution**: Run `composer install` to install dependencies.

---

## 📝 License

This package is open-source software licensed under the [GNU General Public License v3.0 (GPL-3.0)](https://opensource.org/licenses/GPL-3.0).

### License Summary:

✅ **You are allowed to:**
- Use the package for personal or commercial projects
- Modify the source code for your own use
- Distribute the modified or original source code (under GPL-3.0)
- Study and learn from the source code freely

❌ **You are NOT allowed to:**
- Re-license the package under a different license
- Distribute as part of proprietary closed-source software
- Sub-license or sell under a restrictive license

**Important**: Any distributed modifications must also be licensed under GPL-3.0.

---

## 🔗 Links & Support

- **Repository**: https://github.com/huzaifaalmesbah/bd-courier-customer-delivery-stats
- **Packagist**: https://packagist.org/packages/huzaifaalmesbah/bd-courier-customer-delivery-stats
- **Issues**: https://github.com/huzaifaalmesbah/bd-courier-customer-delivery-stats/issues
- **Documentation**: See `examples/` directory
- **Email**: hi@huzaifa.im
- **Original Package**: https://packagist.org/packages/shahariar-ahmad/courier-fraud-checker-bd

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

---

## 📊 Version History

- **v1.0.0** - Initial universal package release
  - Framework-agnostic design
  - Support for Laravel, WordPress, CodeIgniter
  - Comprehensive phone validation
  - Enhanced error handling
  - Complete documentation and examples

---

## 🙏 Credits & Attribution

This package is a universal, framework-agnostic fork of the original Laravel-specific package:
- **Original Package**: [shahariar-ahmad/courier-fraud-checker-bd](https://packagist.org/packages/shahariar-ahmad/courier-fraud-checker-bd)
- **Original Author**: Shahariar Ahmad
- **Original Repository**: [ShahariarAhmad/ShahariarAhmad-CourierFraudCheckerBD---packagist.org](https://github.com/ShahariarAhmad/ShahariarAhmad-CourierFraudCheckerBD---packagist.org)

### What's New in This Fork:
- ✨ **Universal Compatibility**: Works with any PHP framework (Laravel, WordPress, CodeIgniter, Symfony, etc.)
- 🔧 **Framework-Agnostic**: No longer depends on Laravel's HTTP client or services
- 📱 **Enhanced Phone Validation**: Improved phone number utilities and validation
- 🛠️ **Better Configuration**: Supports multiple configuration methods
- 📚 **Comprehensive Examples**: Integration examples for multiple frameworks
- 🧪 **Better Testing**: Enhanced test suite with real API testing

**Special thanks to Shahariar Ahmad for the original implementation and API integration work that made this universal package possible.**
