# Students Honoring System - API Documentation

## Overview

This Laravel REST API provides a complete backend for managing student honor certificates and grades. The system allows:

- **Students**: Anonymous form submission of certificates (name, class, school, grade, image)
- **Admin Users**: Full management of student records, filtering, searching, sorting, and dynamic column management
- **File Storage**: Certificate images stored in Supabase Storage
- **Database**: PostgreSQL via Supabase
- **Authentication**: Laravel Sanctum for admin API token authentication

---

## Authentication

### Admin Authentication

All admin endpoints require two conditions:

1. **API Token**: Generated via Sanctum after admin user logs in
2. **Admin Role**: User must have `is_admin = true` in the database

#### Get Admin Token

Use the following command to manually create an admin token in your application or integrate with your login system:

```php
php artisan tinker
$admin = User::where('email', 'admin@studentsystem.test')->first();
$token = $admin->createToken('admin-token')->plainTextToken;
// Use this token in Authorization header: Bearer <token>
```

#### Using Token in Requests

Include the token in the `Authorization` header:

```bash
curl -H "Authorization: Bearer <token>" https://your-app.com/api/admin/students
```

---

## Public Endpoints

### 1. Submit Student Certificate (Public)

**Endpoint**: `POST /api/students`

**Authorization**: None (public submission)

**Description**: Students anonymously submit their certificate information along with an image.

**Request Body** (multipart/form-data):

```json
{
  "full_name": "John Doe",
  "class": 10,
  "school_name": "Central High School",
  "grade": 95,
  "certificate": <binary image file>
}
```

**Validation Rules**:

| Field | Rule |
|-------|------|
| `full_name` | Required, string, max 255 chars |
| `class` | Required, integer, 1-12 |
| `school_name` | Required, string, max 255 chars |
| `grade` | Required, numeric, 0-100 |
| `certificate` | Required, image file (jpeg/png/jpg/gif), max 50MB |

**Response** (201 Created):

```json
{
  "message": "Student record created successfully",
  "data": {
    "id": 1,
    "full_name": "John Doe",
    "class": 10,
    "school_name": "Central High School",
    "grade": 95,
    "certificate_path": "certificates/uuid.jpg",
    "custom_data": null,
    "created_at": "2026-05-20T12:00:00Z",
    "updated_at": "2026-05-20T12:00:00Z"
  }
}
```

**Error Response** (422 Unprocessable Entity):

```json
{
  "message": "The grade field must not be greater than 100.",
  "errors": {
    "grade": ["The grade field must not be greater than 100."]
  }
}
```

---

## Admin Endpoints

All admin endpoints require `Authorization: Bearer <token>` header and admin role.

### 2. List Students (Admin)

**Endpoint**: `GET /api/admin/students`

**Authorization**: Required (Sanctum + admin role)

**Description**: Retrieve paginated list of all students with optional filtering, searching, and sorting.

**Query Parameters**:

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `class` | integer | Filter by class (1-12) | `?class=10` |
| `grade` | integer | Filter by grade (0-100) | `?grade=90` |
| `school_name` | string | Filter by school name (partial match) | `?school_name=Central` |
| `search` | string | Search by full name or school name | `?search=John` |
| `sort_by` | string | Sort field (class, grade, created_at, full_name) | `?sort_by=grade` |
| `sort_order` | string | Sort direction (asc, desc) | `?sort_order=desc` |
| `per_page` | integer | Items per page (default: 15) | `?per_page=20` |

**Response** (200 OK):

```json
{
  "data": [
    {
      "id": 1,
      "full_name": "John Doe",
      "class": 10,
      "school_name": "Central High School",
      "grade": 95,
      "certificate_path": "certificates/uuid.jpg",
      "custom_data": null,
      "created_at": "2026-05-20T12:00:00Z",
      "updated_at": "2026-05-20T12:00:00Z"
    }
  ],
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 15,
    "to": 15,
    "total": 75
  }
}
```

**Example Requests**:

```bash
# Filter by class
curl -H "Authorization: Bearer <token>" \
  "https://your-app.com/api/admin/students?class=10"

# Sort by grade (highest first)
curl -H "Authorization: Bearer <token>" \
  "https://your-app.com/api/admin/students?sort_by=grade&sort_order=desc"

# Search for student by name and filter by school
curl -H "Authorization: Bearer <token>" \
  "https://your-app.com/api/admin/students?search=John&school_name=Central"
```

---

### 3. View Single Student (Admin)

**Endpoint**: `GET /api/admin/students/{id}`

**Authorization**: Required (Sanctum + admin role)

**Description**: Retrieve detailed information for a specific student.

**URL Parameters**:

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Student ID |

**Response** (200 OK):

```json
{
  "data": {
    "id": 1,
    "full_name": "John Doe",
    "class": 10,
    "school_name": "Central High School",
    "grade": 95,
    "certificate_path": "certificates/uuid.jpg",
    "custom_data": null,
    "created_at": "2026-05-20T12:00:00Z",
    "updated_at": "2026-05-20T12:00:00Z"
  }
}
```

**Error Response** (404 Not Found):

```json
{
  "message": "No query results found for model [App\\Models\\Student]."
}
```

---

### 4. Create Student (Admin)

**Endpoint**: `POST /api/admin/students`

**Authorization**: Required (Sanctum + admin role)

**Description**: Admin creates a new student record manually (certificate is optional).

**Request Body** (multipart/form-data or JSON):

```json
{
  "full_name": "Jane Smith",
  "class": 11,
  "school_name": "Lincoln High School",
  "grade": 88,
  "certificate": <optional binary file>
}
```

**Validation Rules**:

| Field | Rule |
|-------|------|
| `full_name` | Required, string, max 255 chars |
| `class` | Required, integer, 1-12 |
| `school_name` | Required, string, max 255 chars |
| `grade` | Required, numeric, 0-100 |
| `certificate` | Optional, image file, max 50MB |

**Response** (201 Created):

```json
{
  "message": "Student created successfully",
  "data": {
    "id": 2,
    "full_name": "Jane Smith",
    "class": 11,
    "school_name": "Lincoln High School",
    "grade": 88,
    "certificate_path": null,
    "custom_data": null,
    "created_at": "2026-05-20T13:00:00Z",
    "updated_at": "2026-05-20T13:00:00Z"
  }
}
```

---

### 5. Update Student (Admin)

**Endpoint**: `PUT /api/admin/students/{id}`

**Authorization**: Required (Sanctum + admin role)

**Description**: Update one or more student fields (all fields optional).

**URL Parameters**:

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Student ID |

**Request Body** (multipart/form-data or JSON):

```json
{
  "full_name": "John Updated",
  "grade": 92,
  "certificate": <optional updated image file>
}
```

**Validation Rules** (same as create, but all fields optional):

```
- full_name: optional, string, max 255
- class: optional, integer, 1-12
- school_name: optional, string, max 255
- grade: optional, numeric, 0-100
- certificate: optional, image file, max 50MB
```

**Response** (200 OK):

```json
{
  "message": "Student updated successfully",
  "data": {
    "id": 1,
    "full_name": "John Updated",
    "class": 10,
    "school_name": "Central High School",
    "grade": 92,
    "certificate_path": "certificates/new-uuid.jpg",
    "custom_data": null,
    "created_at": "2026-05-20T12:00:00Z",
    "updated_at": "2026-05-20T13:30:00Z"
  }
}
```

---

### 6. Delete Student (Admin)

**Endpoint**: `DELETE /api/admin/students/{id}`

**Authorization**: Required (Sanctum + admin role)

**Description**: Delete a student record and their associated certificate file.

**URL Parameters**:

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Student ID |

**Response** (200 OK):

```json
{
  "message": "Student deleted successfully"
}
```

**Error Response** (404 Not Found):

```json
{
  "message": "No query results found for model [App\\Models\\Student]."
}
```

---

### 7. List Dynamic Fields (Admin)

**Endpoint**: `GET /api/admin/fields`

**Authorization**: Required (Sanctum + admin role)

**Description**: Retrieve all custom dynamic fields (admin-created columns).

**Response** (200 OK):

```json
{
  "data": [
    {
      "id": 1,
      "field_name": "achievements",
      "field_type": "text",
      "is_visible": true,
      "order": 0,
      "created_at": "2026-05-20T12:00:00Z",
      "updated_at": "2026-05-20T12:00:00Z"
    }
  ]
}
```

---

### 8. Create Dynamic Field (Admin)

**Endpoint**: `POST /api/admin/fields`

**Authorization**: Required (Sanctum + admin role)

**Description**: Add a new dynamic field/column that admins can then populate for students.

**Request Body** (JSON):

```json
{
  "field_name": "achievements",
  "field_type": "text",
  "is_visible": true
}
```

**Validation Rules**:

| Field | Rule |
|-------|------|
| `field_name` | Required, string, unique in dynamic_fields |
| `field_type` | Required, one of: text, number, date, email, url |
| `is_visible` | Optional, boolean (default: true) |

**Supported Field Types**:

- `text`: Text input
- `number`: Numeric input (0-100)
- `date`: Date input
- `email`: Email input
- `url`: URL input

**Response** (201 Created):

```json
{
  "message": "Dynamic field created successfully",
  "data": {
    "id": 1,
    "field_name": "achievements",
    "field_type": "text",
    "is_visible": true,
    "order": 1,
    "created_at": "2026-05-20T13:00:00Z",
    "updated_at": "2026-05-20T13:00:00Z"
  }
}
```

**Error Response** (422 Unprocessable Entity - Duplicate field name):

```json
{
  "message": "The field name has already been taken.",
  "errors": {
    "field_name": ["The field name has already been taken."]
  }
}
```

---

### 9. Delete Dynamic Field (Admin)

**Endpoint**: `DELETE /api/admin/fields/{id}`

**Authorization**: Required (Sanctum + admin role)

**Description**: Remove a custom dynamic field.

**URL Parameters**:

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Field ID |

**Response** (200 OK):

```json
{
  "message": "Dynamic field deleted successfully"
}
```

---

## Error Handling

### Common Error Responses

**401 Unauthorized** (Missing or invalid token):

```json
{
  "message": "Unauthenticated."
}
```

**403 Forbidden** (Not admin):

```json
{
  "message": "Unauthorized. Admin access required."
}
```

**422 Unprocessable Entity** (Validation errors):

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["The field name field is required."],
    "grade": ["The grade must be between 0 and 100."]
  }
}
```

**404 Not Found** (Resource doesn't exist):

```json
{
  "message": "No query results found for model [App\\Models\\Student]."
}
```

**500 Internal Server Error** (Unexpected error):

```json
{
  "message": "Server error details..."
}
```

---

## Filtering & Sorting Examples

### Get Students with Grade A (90-100) in Class 10

```bash
curl -H "Authorization: Bearer <token>" \
  "https://your-app.com/api/admin/students?class=10&grade=90&sort_by=grade&sort_order=desc"
```

### Search for Students from "Central" School and Sort by Class

```bash
curl -H "Authorization: Bearer <token>" \
  "https://your-app.com/api/admin/students?search=Central&sort_by=class&sort_order=asc"
```

### Paginate Results (20 per page)

```bash
curl -H "Authorization: Bearer <token>" \
  "https://your-app.com/api/admin/students?per_page=20&sort_by=created_at&sort_order=desc"
```

---

## Data Model

### Student

```json
{
  "id": "integer",
  "full_name": "string (max 255)",
  "class": "integer (1-12)",
  "school_name": "string (max 255)",
  "grade": "integer (0-100)",
  "certificate_path": "string (S3 path) | null",
  "custom_data": "JSON object | null",
  "created_at": "ISO 8601 timestamp",
  "updated_at": "ISO 8601 timestamp"
}
```

### DynamicField

```json
{
  "id": "integer",
  "field_name": "string (unique)",
  "field_type": "string (text|number|date|email|url)",
  "is_visible": "boolean",
  "order": "integer",
  "created_at": "ISO 8601 timestamp",
  "updated_at": "ISO 8601 timestamp"
}
```

---

## Security Features

1. **Sanctum Authentication**: API tokens for admin access
2. **Admin Authorization Middleware**: Only admin users (is_admin=true) can access protected endpoints
3. **Form Validation**: All inputs validated server-side
4. **File Upload Security**: Image files validated by type and size
5. **Database Isolation**: Students and admins use separate authorization logic

---

## Testing

Run the feature tests to verify API functionality:

```bash
# Run all tests
php artisan test --compact

# Run specific test suite
php artisan test tests/Feature/StudentSubmissionTest.php
php artisan test tests/Feature/AdminStudentManagementTest.php
php artisan test tests/Feature/AdminFieldManagementTest.php
```

---

## Setup & Configuration

### Environment Variables

```env
DB_CONNECTION=pgsql
DB_HOST=db.vpkxfiywlhsdowqosuqi.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=<your-password>

SUPABASE_URL=https://vpkxfiywlhsdowqosuqi.supabase.co
SUPABASE_KEY=<your-api-key>
SUPABASE_BUCKET=certificates
```

### Running Migrations

```bash
php artisan migrate
php artisan db:seed --class=AdminSeeder
```

### Creating Admin Users

```bash
php artisan tinker
User::create([
  'name' => 'Admin Name',
  'email' => 'admin@example.com',
  'password' => bcrypt('password'),
  'is_admin' => true,
]);
```

---

## Future Enhancements

- **Custom Fields in Student Records**: Store additional data in `custom_data` JSON column
- **File Preview**: Generate thumbnail URLs for certificates
- **Batch Operations**: Bulk delete, update students
- **Export**: CSV export of student data
- **Webhooks**: Notify external systems when students submit
- **Rate Limiting**: Prevent abuse of public submission endpoint
- **Email Notifications**: Confirmation emails to admins on new submissions

---

## Support & Questions

For issues or questions about the API, refer to the Laravel documentation:

- [Laravel Framework](https://laravel.com/docs)
- [Sanctum Authentication](https://laravel.com/docs/sanctum)
- [Supabase PostgreSQL](https://supabase.com/docs)
