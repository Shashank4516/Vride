# VRide Test Cases

## Scope
Covers core functionality and database integrity for:
- Authentication: register, login, logout
- Vehicle listing and browsing
- Booking flow
- Admin approvals
- Dashboard data rendering

## Environment Setup
1. Start Apache and MySQL in XAMPP.
2. Project path: `c:/xampp/htdocs/vride anti`
3. Open: `http://localhost/vride%20anti/index.php`

## Database Validation
1. Open `http://localhost/phpmyadmin`
2. Verify database exists: `vehicle_rental`
3. Verify tables exist:
- users
- vehicles
- bookings
4. Verify default admin exists:
- email: `admin@vrental.com`
- role: `admin`

## Functional Test Cases

### TC-01 Register user (happy path)
- Steps:
1. Open `register.php`
2. Fill valid data and submit.
- Expected:
1. Redirect to `login.php` (or dashboard in demo fallback).
2. New row in `users` table.

### TC-02 Register duplicate email
- Steps:
1. Register once with email `test1@example.com`.
2. Register again with same email.
- Expected:
1. Error message shown: email already registered.
2. No duplicate row inserted.

### TC-03 Login user
- Steps:
1. Open `login.php`
2. Login with registered credentials.
- Expected:
1. Redirect to `dashboard.php`.
2. Session values are set (`user_id`, `name`, `email`, `role`).

### TC-04 Login invalid password
- Steps:
1. Enter valid email with wrong password.
- Expected:
1. Error message shown.
2. No login session created.

### TC-05 List vehicle
- Steps:
1. Login as user.
2. Open `list_vehicle.php`.
3. Submit required fields with image URL or upload.
- Expected:
1. Row inserted in `vehicles`.
2. Status is `pending`.
3. `final_price` is set via AI suggestion.

### TC-06 Browse vehicles filters
- Steps:
1. Open `vehicles.php?type=2wheeler&city=Delhi`
- Expected:
1. Only approved vehicles shown.
2. Filter respects type and city.

### TC-07 Book vehicle with valid dates
- Steps:
1. Login as user.
2. Open `book_vehicle.php?id=<approved_vehicle_id>`.
3. Submit pickup and return dates.
- Expected:
1. Booking success confirmation shown.
2. Row inserted in `bookings` with `status = pending`.
3. `days` and amount computed correctly.

### TC-08 Book vehicle with invalid date order
- Steps:
1. Submit return date earlier than pickup date.
- Expected:
1. Error message shown.
2. No booking inserted.

### TC-09 Dashboard data
- Steps:
1. Open `dashboard.php` after creating bookings/listings.
- Expected:
1. Counts match database records for current user.
2. Recent bookings and listings render without PHP warnings.

### TC-10 Admin vehicle approval
- Steps:
1. Login as admin.
2. Open `admin.php?tab=pending_v`.
3. Approve pending vehicle with final price.
- Expected:
1. Vehicle status changes to `approved`.
2. Final price updated.
3. Vehicle appears in browse page.

### TC-11 Admin booking approval
- Steps:
1. Open `admin.php?tab=pending_b`.
2. Approve booking with final amount.
- Expected:
1. Booking status changes to `approved`.
2. Final amount and admin note saved.

### TC-12 Access control checks
- Steps:
1. Open `dashboard.php` while logged out.
2. Open `admin.php` as normal user.
- Expected:
1. Dashboard redirects to login.
2. Admin page blocked with error flash + redirect.

## Quick CLI Checks
Run from project folder:

```powershell
Get-ChildItem -File -Filter *.php | ForEach-Object { php -l $_.FullName }
```

Optional route smoke check:

```powershell
$job = Start-Job -ScriptBlock { Set-Location "c:/xampp/htdocs/vride anti"; php -S 127.0.0.1:8090 > $null 2>&1 }
'index.php','vehicles.php','login.php','register.php' | ForEach-Object {
  $code = curl.exe -s -o NUL -w "%{http_code}" ("http://127.0.0.1:8090/" + $_)
  "$_ -> $code"
}
Stop-Job $job | Out-Null
Remove-Job $job
```

## SQL Verification Snippets
```sql
SELECT COUNT(*) AS users_count FROM users;
SELECT COUNT(*) AS vehicles_count FROM vehicles;
SELECT COUNT(*) AS bookings_count FROM bookings;

SELECT id, title, status, final_price FROM vehicles ORDER BY id DESC LIMIT 10;
SELECT id, vehicle_id, user_id, days, amount, final_amount, status FROM bookings ORDER BY id DESC LIMIT 10;
```
