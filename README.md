# 📦 BD Courier Customer Delivery Stats

A powerful PHP package for tracking customer delivery statistics across Pathao, Steadfast, and RedX courier services in Bangladesh. Perfect for e-commerce businesses looking to analyze customer ordering patterns and delivery history.

> **Note**: Framework-agnostic fork of [shahariar-ahmad/courier-fraud-checker-bd](https://packagist.org/packages/shahariar-ahmad/courier-fraud-checker-bd), enhanced to work with any PHP framework.

---

## ✨ Key Features

- **Works with Any PHP Framework**: Seamlessly integrates with Laravel, WordPress, CodeIgniter, Symfony, and more
- **Multiple Courier Support**: Track customer delivery statistics across Pathao, Steadfast, and RedX
- **Phone Number Validation**: Built-in validation for Bangladeshi phone numbers
- **Flexible Configuration**: Support for environment variables or direct configuration
- **Delivery Analytics**: Comprehensive success/cancel/total delivery statistics
- **Error Handling**: Graceful error handling with detailed messages
- **Simple Integration**: Easy-to-use API with comprehensive documentation

---

## ⚙️ Installation

```bash
composer require huzaifaalmesbah/bd-courier-customer-delivery-stats
```

### Requirements:
- PHP 7.4 or higher
- Guzzle HTTP client (automatically installed)

---

## 🔧 Configuration

### Option 1: Environment Variables (Recommended)

Add these to your `.env` file:

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

### Option 2: Direct Configuration

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

## 🚀 Quick Start

```php
<?php
require_once 'vendor/autoload.php';

use Ham\BdCourier\CustomerDeliveryStats\CourierCustomerStats;

// Initialize (will auto-load from environment variables)
$customerStats = new CourierCustomerStats();

// Check customer delivery history across all services
$result = $customerStats->check('01712345678');

print_r($result);
```

**Output Example:**

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

The package includes built-in validation for Bangladeshi phone numbers:

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

### Check Individual Courier Services

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

### Risk Assessment Example

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

## 🔌 Framework Integration Examples

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

## 📁 Examples

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

---

## 🔗 Links & Support

- **GitHub**: [huzaifaalmesbah/bd-courier-customer-delivery-stats](https://github.com/huzaifaalmesbah/bd-courier-customer-delivery-stats)
- **Packagist**: [huzaifaalmesbah/bd-courier-customer-delivery-stats](https://packagist.org/packages/huzaifaalmesbah/bd-courier-customer-delivery-stats)
- **Issues**: [GitHub Issues](https://github.com/huzaifaalmesbah/bd-courier-customer-delivery-stats/issues)
- **Contact**: hi@huzaifa.im
- **Original Package**: [shahariar-ahmad/courier-fraud-checker-bd](https://packagist.org/packages/shahariar-ahmad/courier-fraud-checker-bd)

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

---

## 📊 Version History

- **v1.0.0** - Initial release
  - Framework-agnostic design
  - Support for multiple PHP frameworks
  - Comprehensive phone validation
  - Enhanced error handling
  - Complete documentation and examples

---

## 🙏 Credits

This package is a universal, framework-agnostic fork of the original Laravel-specific package:
- **Original Author**: Shahariar Ahmad
- **Original Package**: [shahariar-ahmad/courier-fraud-checker-bd](https://packagist.org/packages/shahariar-ahmad/courier-fraud-checker-bd)
- **Original Repository**: [ShahariarAhmad/ShahariarAhmad-CourierFraudCheckerBD---packagist.org](https://github.com/ShahariarAhmad/ShahariarAhmad-CourierFraudCheckerBD---packagist.org)

### Enhancements in This Version:
- ✨ **Universal Compatibility**: Works with any PHP framework
- 🔧 **Framework-Agnostic**: No longer depends on Laravel
- 📱 **Enhanced Phone Validation**: Improved phone number validation
- 🛠️ **Flexible Configuration**: Multiple configuration methods
- 📚 **Comprehensive Examples**: Examples for various frameworks

**Special thanks to Shahariar Ahmad for the original implementation.**
