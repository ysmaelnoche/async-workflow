<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Test form submission data
$testData = [
    '_token' => csrf_token(),
    'employee_id' => '12',
    'requested_by' => 'Test Employee',
    'request_type' => 'Plumbing Repair',
    'title' => 'Test PFMO Request',
    'priority' => 'Routine',
    'purpose' => 'For Action',
    'body' => 'This is a test PFMO request submission.',
    'date_needed' => '2025-09-10'
];

echo "Testing form submission with data:\n";
print_r($testData);

try {
    // Create a request instance
    $request = \Illuminate\Http\Request::create('/secretary/store-proxy-request', 'POST', $testData);
    $request->headers->set('Content-Type', 'application/x-www-form-urlencoded');
    
    // Set up authentication (assuming user ID 3 is a secretary)
    \Illuminate\Support\Facades\Auth::loginUsingId(3);
    
    // Call the controller method
    $controller = new \App\Http\Controllers\SecretaryController();
    $response = $controller->storeProxyRequest($request);
    
    echo "\n✅ Form submission successful!\n";
    if (method_exists($response, 'getStatusCode')) {
        echo "Status Code: " . $response->getStatusCode() . "\n";
    }
    
    // Check if it's a redirect response
    if ($response instanceof \Illuminate\Http\RedirectResponse) {
        echo "Redirect URL: " . $response->getTargetUrl() . "\n";
    }
    
    // Check if it's a JSON response
    if (method_exists($response, 'getData')) {
        echo "Response data: " . json_encode($response->getData()) . "\n";
    }
    
} catch (\Exception $e) {
    echo "\n❌ Form submission failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
