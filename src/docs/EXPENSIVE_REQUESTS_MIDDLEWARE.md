# Expensive Requests Middleware

This middleware (`LogExpensiveRequests`) measures the duration of Laravel server-side requests and logs requests that exceed a configurable threshold as expensive.

## Features

- Measures request duration in milliseconds
- Configurable threshold via environment variable
- Stores expensive requests in the database via `SystemErrorRepository`
- Optional threshold override per route
- Memory usage tracking
- Request/response size tracking
- User context logging (when authenticated)
- Database persistence for performance analysis and monitoring

## Configuration

### Environment Variable

Add to your `.env` file:

```env
ED_EXPENSIVE_REQUEST_THRESHOLD=2000
```

This sets the default threshold to 2000 milliseconds (2 seconds). Requests taking longer than this will be logged.

### Configuration File

The middleware reads from `config/ed.php`:

```php
'expensive_request_threshold' => env('ED_EXPENSIVE_REQUEST_THRESHOLD', 2000), // milliseconds
```

## Usage

### 1. Apply to Specific Routes

Use the `log.expensive` middleware alias on specific routes:

```php
// In your route files
Route::get('/slow-endpoint', [Controller::class, 'method'])->middleware('log.expensive');
Route::get('/another-endpoint', [Controller::class, 'method'])->middleware('log.expensive:1000'); // Custom 1-second threshold
```

### 2. Apply to Route Groups

```php
Route::middleware(['log.expensive'])->group(function () {
    Route::get('/api/slow', [ApiController::class, 'slowMethod']);
    Route::post('/api/processing', [ApiController::class, 'processData']);
});
```

### 3. Apply to All Routes (Global Middleware)

To apply to all requests, add it to the global middleware stack in `bootstrap/app.php`:

```php
$middleware->group('web', [
    // ... other middleware
    LogExpensiveRequests::class,
]);
```

### 4. Custom Threshold Per Route

You can override the threshold for specific routes:

```php
// Route with custom 1-second threshold
Route::get('/critical-endpoint', [Controller::class, 'method'])
    ->middleware('log.expensive:1000');
```

## Database Storage

When a request exceeds the threshold, it will be stored in the `system_errors` table with the following information:

- **message**: "Expensive request detected"
- **category**: "performance"
- **error**: JSON-encoded detailed information including:
  - URL, method, route name
  - IP address and user agent
  - HTTP status code
  - Memory usage in MB
  - User ID (if authenticated)
  - Request and response sizes in KB
  - Query parameters (for GET requests)
  - Timestamp
- **file**: Request file information
- **line**: Request line information
- **user_agent**: User agent string
- **account_id**: User ID (if authenticated)
- **session_id**: Session identifier
- **url**: Full request URL
- **ip**: Client IP address
- **duration**: Request duration in seconds (calculated from LARAVEL_START or REQUEST_TIME_FLOAT)

## Database Integration

The middleware integrates with your existing `SystemErrorRepository` to store expensive request data in the `system_errors` table. This provides several advantages:

1. **Persistent Storage**: Data is stored in the database for long-term analysis
2. **Consistent Format**: Uses the same structure as other system errors
3. **Queryable Data**: Easy to query and analyze performance patterns
4. **User Context**: Automatically includes user and session information
5. **Category Organization**: Tagged with "performance" category for easy filtering

### Querying Expensive Requests

You can query expensive requests from the database:

```php
use App\Models\SystemError;

// Get all expensive requests
$expensiveRequests = SystemError::where('category', 'performance')->get();

// Get expensive requests for a specific user
$userExpensiveRequests = SystemError::where('category', 'performance')
    ->where('account_id', $userId)
    ->get();

// Get expensive requests from the last 24 hours
$recentExpensiveRequests = SystemError::where('category', 'performance')
    ->where('created_at', '>=', now()->subDay())
    ->get();
```

## Performance Considerations

- The middleware has minimal overhead (~0.1ms per request)
- Database writes add slight overhead but provide persistent storage
- Consider using it only on routes where performance monitoring is important
- For high-traffic applications, consider adding database indexes on the `system_errors` table for better query performance
- The `SystemErrorRepository` handles the database storage efficiently

## Monitoring and Alerting

You can set up monitoring based on the database entries:

1. **Database Analysis**: Query the `system_errors` table to identify performance patterns
2. **Alerting**: Set up alerts when expensive requests occur frequently
3. **Dashboards**: Create dashboards to visualize request performance trends
4. **Performance Reports**: Generate reports on slow endpoints and user impact

## Example Use Cases

- API endpoints that perform complex queries
- File upload/processing endpoints
- Search functionality
- Report generation
- Data export endpoints
- Any endpoint where performance is critical
