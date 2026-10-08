# Lab 3 Access and Input Test Matrix

## Test setup

Tests were run locally against an isolated SQLite database at
`storage/framework/testing/lab3-access-matrix.sqlite`; the application's normal
database was not used for the live CSRF checks. The database was migrated and
seeded with the fictional accounts in `UserSeeder`. Browser-style account tests
use independent authenticated test clients; T02 logs out Student A before
authenticating as Student B.

Automated matrix cases:

```powershell
php artisan test --filter=ServiceRequestAccessInputMatrixTest
```

The automated run passed all 9 feature tests (65 assertions). Laravel bypasses
CSRF verification during PHPUnit runs, so T04's valid-token denial and T09's
missing/invalid-token results were additionally checked against the live local
web server (`http://127.0.0.1:8810`) with an authenticated HTTP session and the
isolated SQLite database.

## Results

| ID | Expected result | Actual result | Result | Evidence reference |
| --- | --- | --- | --- | --- |
| T01 | Guest list and detail requests redirect to login; no request data is returned. | Both guest requests redirected to the login route; no request details were returned. | PASS | `tests/Feature/ServiceRequestAccessInputMatrixTest.php::test_t01_guest_cannot_open_request_list_or_detail` |
| T02 | Student A and Student B each see and open only their own records. | Student A saw/opened only A's record; after logout, Student B saw/opened only B's record. | PASS | `tests/Feature/ServiceRequestAccessInputMatrixTest.php::test_t02_each_student_sees_and_opens_only_their_own_requests` |
| T03 | Each student receives the chosen denial for the other student's ID; no details are disclosed. | Both cross-owner detail requests returned 404 and did not include the other student's purpose. | PASS | `tests/Feature/ServiceRequestAccessInputMatrixTest.php::test_t03_each_student_gets_404_for_the_other_students_record` |
| T04 | A student with a valid session and CSRF token is denied a status PATCH; stored status is unchanged. | Live authenticated PATCH with a valid CSRF token returned HTTP 403; status remained `pending`. | PASS | `tests/Feature/ServiceRequestAccessInputMatrixTest.php::test_t04_student_status_patch_is_denied_and_does_not_change_database_status`; live HTTP check at `PATCH /requests/1/status` |
| T05 | Administrator can list all records, view a request, and save an allowed status. | Administrator list included both students' records, detail returned successfully, and `approved` was saved. | PASS | `tests/Feature/ServiceRequestAccessInputMatrixTest.php::test_t05_administrator_can_list_view_and_update_student_request` |
| T06 | Quantity `0`, `-1`, and non-integer values, plus blank item name, are rejected; no invalid row is saved. | All four inputs returned HTTP 422 validation errors; request table remained empty. | PASS | `tests/Feature/ServiceRequestAccessInputMatrixTest.php::test_t06_invalid_item_or_quantity_is_rejected_without_saving_a_row` |
| T07 | Student-supplied `user_id`, `status`, `is_admin`, or `role` is rejected; values cannot be spoofed. | Each of the four fields returned HTTP 422; no request row was created. | PASS | `tests/Feature/ServiceRequestAccessInputMatrixTest.php::test_t07_student_cannot_supply_owner_status_or_role_fields` |
| T08 | `<b>LAB3</b>` is shown as literal text; apostrophe is stored safely. | Database retained the exact purpose and apostrophe; Blade output escaped the markup to `&lt;b&gt;LAB3&lt;/b&gt;`. | PASS | `tests/Feature/ServiceRequestAccessInputMatrixTest.php::test_t08_html_markup_is_escaped_and_apostrophe_is_stored_safely` |
| T09 | Web POSTs with missing or invalid CSRF token are rejected (normally 419); database remains unchanged. | Live authenticated web requests with missing and invalid tokens each returned HTTP 419; request count stayed 0 before and after both attempts. | PASS | Live HTTP checks against `POST /requests`; isolated database count verified before and after |
| T10 | Administrator's invalid status is rejected; existing status stays unchanged. | `processing` returned HTTP 422 with a status validation error; stored status remained `pending`. | PASS | `tests/Feature/ServiceRequestAccessInputMatrixTest.php::test_t10_administrator_invalid_status_is_rejected_without_changing_status` |

## Reproducing the live CSRF checks

Use the dedicated test database only; these commands reset that SQLite file:

```powershell
$env:DB_CONNECTION = 'sqlite'
$env:DB_DATABASE = "$PWD\storage\framework\testing\lab3-access-matrix.sqlite"
$env:APP_DEBUG = 'false'
$env:SESSION_DRIVER = 'file'
$env:CACHE_STORE = 'file'
New-Item -ItemType File -Path $env:DB_DATABASE -Force | Out-Null
php artisan migrate:fresh --seed --force
php artisan serve --host=127.0.0.1 --port=8810
```

Authenticate in a fresh browser session with each seeded account when checking
account-specific behavior. For the live CSRF check, submit a request form once
without its token and once with an invalid token; both should return 419. Check
that the request count is unchanged. The seeded local lab accounts use the
password configured in `database/seeders/UserSeeder.php`; do not use these
development credentials in a deployed environment.
