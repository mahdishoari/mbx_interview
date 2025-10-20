# Code Review - Candidate CAND-LT8V

## Target File
`code_review/BadController.php`

## Summary
This controller contains multiple design, security, and performance issues.  
Below is a structured review divided into key categories with severity and recommendations.

---

### 1. Security Issues ⚠️

**1.1 SQL Injection (High)**
```php
$sql = "SELECT * FROM orders WHERE user_id = $userId ORDER BY created_at DESC";
```
Problem: Directly interpolating $userId allows SQL injection.
✅ Fix: Use prepared statements:
```php
$stmt = $db->prepare("SELECT * FROM orders WHERE user_id = :id ORDER BY created_at DESC");
$stmt->execute([':id' => $userId]);
```
### 1.2 No Authentication/Authorization (High)
Problem: Anyone can access any user's orders by changing the user query parameter.

✅ Fix: Validate the logged-in user and restrict access to their own data.

### 1.3 No Input Validation (High)

Problem: $_GET['user'], $_GET['page'], and $_GET['per'] are used directly.

✅ Fix: Use filter_input(INPUT_GET, 'param', FILTER_VALIDATE_INT) and enforce limits.

### 2. Performance Issues ⚙️

### 2.1 Inefficient Pagination (High)
```php
$rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
$slice = array_slice($rows, $offset, $per);
```

Problem: Fetches all rows from DB and slices in PHP.

✅ Fix: Add pagination at SQL level:

```php
$stmt = $db->prepare("SELECT * FROM orders WHERE user_id = :id ORDER BY created_at DESC LIMIT :per OFFSE
```
### 2.2 N+1 Query Problem (High)

```php
foreach ($slice as &$r) {
$items = $db->query("SELECT * FROM order_items WHERE order_id = ".$r['id'])->fetchAll(PDO::FETCH_ASSOC);
$r['items'] = $items;
}
```

Problem: For every order, a new DB query is executed.

✅ Fix: Use a single query with JOIN or prefetch order items for all orders in one query.

### 2.3 Unbounded Defaults (Medium)
```php
$per = $_GET['per'] ?? 1000;
```

Problem: Allows huge page sizes leading to memory pressure.

✅ Fix: Cap per to a safe max (e.g., 100).

### 3. Maintainability 🧱

### 3.1 Hardcoded Database Path (Medium)
```php
new PDO('sqlite:/tmp/production.sqlite');
```

Problem: Not configurable, not portable.

✅ Fix: Load from configuration or environment variables.

### 3.2 No Error Handling (High)

Problem: If DB fails, controller crashes without a response.

✅ Fix: Wrap DB code in try/catch and return structured JSON error.

### 3.3 No Dependency Injection (Medium)

Problem: PDO created directly in controller, hard to test.

✅ Fix: Inject DB connection via constructor or a container.

### 4. Response & Headers 🌐

### 4.1 Missing Content-Type Header (Low)

Problem: Returns JSON without correct headers.

✅ Fix:
```php
header('Content-Type: application/json');
```
### 4.2 Inconsistent Cache Policy (Low)

Problem: Hardcoded Cache-Control: no-store — not configurable.

✅ Fix: Make caching strategy configurable per environment.

Recommended Refactor Outline

Controller → Only handles HTTP layer (validation + response)

Service/Repository → Handles DB queries and business logic

Model → Maps database entities

Auth Middleware → Validates session/token

Proper Error Response:

echo json_encode(['error' => 'Invalid request']);
http_response_code(400);
