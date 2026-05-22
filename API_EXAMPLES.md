# API Usage Examples

Quick reference for common API operations.

## Table of Contents

1. [Public Student Submission](#public-student-submission)
2. [Admin Authentication](#admin-authentication)
3. [List & Filter Students](#list--filter-students)
4. [Manage Students](#manage-students)
5. [Manage Dynamic Fields](#manage-dynamic-fields)
6. [Error Handling](#error-handling)

---

## Public Student Submission

### Submit Certificate (Basic)

```bash
curl -X POST http://localhost:8000/api/students \
  -F "full_name=John Doe" \
  -F "class=10" \
  -F "school_name=Central High School" \
  -F "grade=95" \
  -F "certificate=@path/to/certificate.jpg"
```

### Submit with cURL (Full Response)

```bash
curl -v -X POST http://localhost:8000/api/students \
  -F "full_name=Alice Johnson" \
  -F "class=11" \
  -F "school_name=Lincoln High" \
  -F "grade=88" \
  -F "certificate=@certificate.png" \
  | json_pp
```

### Submit with Python

```python
import requests

data = {
    'full_name': 'Bob Smith',
    'class': 12,
    'school_name': 'Washington Academy',
    'grade': 92,
}

files = {
    'certificate': open('cert.jpg', 'rb')
}

response = requests.post(
    'http://localhost:8000/api/students',
    data=data,
    files=files
)

print(response.json())
```

### Submit with JavaScript/Node.js

```javascript
const FormData = require('form-data');
const fs = require('fs');
const axios = require('axios');

const form = new FormData();
form.append('full_name', 'Carol Davis');
form.append('class', 10);
form.append('school_name', 'Jefferson High');
form.append('grade', 90);
form.append('certificate', fs.createReadStream('cert.jpg'));

axios.post('http://localhost:8000/api/students', form, {
  headers: form.getHeaders()
}).then(res => console.log(res.data));
```

---

## Admin Authentication

### Get Admin Token (Interactive)

```bash
php artisan tinker
> $admin = User::where('email', 'admin@studentsystem.test')->first();
> $token = $admin->createToken('api-token')->plainTextToken;
> echo $token;
```

### Get Admin Token (Script)

```php
<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';

$admin = App\Models\User::where('email', 'admin@studentsystem.test')->first();
$token = $admin->createToken('api-token')->plainTextToken;
echo "Token: $token\n";
?>
```

### Store Token in Environment

```bash
# Save to .env or config
ADMIN_TOKEN=your_token_here

# Use in subsequent requests
TOKEN=$(grep ADMIN_TOKEN .env | cut -d '=' -f2)
```

---

## List & Filter Students

### List All Students (Basic)

```bash
TOKEN=your_token_here

curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/admin/students
```

### List with Pagination

```bash
curl -H "Authorization: Bearer $TOKEN" \
  "http://localhost:8000/api/admin/students?per_page=20"
```

### Filter by Class (Grade 10)

```bash
curl -H "Authorization: Bearer $TOKEN" \
  "http://localhost:8000/api/admin/students?class=10"
```

### Filter by Grade (90+)

```bash
curl -H "Authorization: Bearer $TOKEN" \
  "http://localhost:8000/api/admin/students?grade=90"
```

### Filter by School

```bash
curl -H "Authorization: Bearer $TOKEN" \
  "http://localhost:8000/api/admin/students?school_name=Central"
```

### Combine Filters

```bash
# Students in Class 10 from Central school with grade 90+
curl -H "Authorization: Bearer $TOKEN" \
  "http://localhost:8000/api/admin/students?class=10&school_name=Central&grade=90"
```

### Search by Name

```bash
curl -H "Authorization: Bearer $TOKEN" \
  "http://localhost:8000/api/admin/students?search=John"
```

### Search by School

```bash
curl -H "Authorization: Bearer $TOKEN" \
  "http://localhost:8000/api/admin/students?search=Lincoln"
```

### Sort by Class (Ascending)

```bash
curl -H "Authorization: Bearer $TOKEN" \
  "http://localhost:8000/api/admin/students?sort_by=class&sort_order=asc"
```

### Sort by Grade (Descending - Highest First)

```bash
curl -H "Authorization: Bearer $TOKEN" \
  "http://localhost:8000/api/admin/students?sort_by=grade&sort_order=desc"
```

### Complex Query Example

```bash
# Find all students from 'Central' school in classes 10-12,
# with grades 85+, sorted by grade (highest first), 25 per page

curl -H "Authorization: Bearer $TOKEN" \
  "http://localhost:8000/api/admin/students?search=Central&class=10&grade=85&sort_by=grade&sort_order=desc&per_page=25"
```

### Using Python for Complex Queries

```python
import requests

headers = {'Authorization': f'Bearer {TOKEN}'}

params = {
    'search': 'Central',
    'grade': 85,
    'sort_by': 'grade',
    'sort_order': 'desc',
    'per_page': 20
}

response = requests.get(
    'http://localhost:8000/api/admin/students',
    headers=headers,
    params=params
)

students = response.json()['data']
for student in students:
    print(f"{student['full_name']}: Grade {student['grade']}")
```

---

## Manage Students

### View Single Student

```bash
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/admin/students/1
```

### Create Student (Admin)

```bash
# Without certificate
curl -X POST http://localhost:8000/api/admin/students \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "David Wilson",
    "class": 12,
    "school_name": "Adams High",
    "grade": 87
  }'
```

### Create Student with Certificate

```bash
curl -X POST http://localhost:8000/api/admin/students \
  -H "Authorization: Bearer $TOKEN" \
  -F "full_name=Emma Brown" \
  -F "class=11" \
  -F "school_name=Brooks Academy" \
  -F "grade=91" \
  -F "certificate=@cert.jpg"
```

### Update Student (Partial)

```bash
# Update grade only
curl -X PUT http://localhost:8000/api/admin/students/1 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "grade": 96
  }'
```

### Update Student (Multiple Fields)

```bash
curl -X PUT http://localhost:8000/api/admin/students/1 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "John Updated",
    "school_name": "New High School",
    "grade": 94
  }'
```

### Update Student with New Certificate

```bash
curl -X PUT http://localhost:8000/api/admin/students/1 \
  -H "Authorization: Bearer $TOKEN" \
  -F "grade=98" \
  -F "certificate=@new_cert.jpg"
```

### Delete Student

```bash
curl -X DELETE http://localhost:8000/api/admin/students/1 \
  -H "Authorization: Bearer $TOKEN"
```

### Batch Delete Multiple Students (Script)

```bash
TOKEN=your_token

for id in 1 2 3 4 5; do
  curl -X DELETE http://localhost:8000/api/admin/students/$id \
    -H "Authorization: Bearer $TOKEN"
  echo "Deleted student $id"
done
```

---

## Manage Dynamic Fields

### List Dynamic Fields

```bash
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/admin/fields
```

### Create Dynamic Field (Text)

```bash
curl -X POST http://localhost:8000/api/admin/fields \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "field_name": "achievements",
    "field_type": "text",
    "is_visible": true
  }'
```

### Create Dynamic Field (Number)

```bash
curl -X POST http://localhost:8000/api/admin/fields \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "field_name": "gpa",
    "field_type": "number",
    "is_visible": true
  }'
```

### Create Dynamic Field (Date)

```bash
curl -X POST http://localhost:8000/api/admin/fields \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "field_name": "graduation_date",
    "field_type": "date",
    "is_visible": true
  }'
```

### Create Multiple Dynamic Fields (Script)

```bash
TOKEN=your_token
BASE_URL=http://localhost:8000/api/admin/fields

fields=(
  '{"field_name":"achievements","field_type":"text","is_visible":true}'
  '{"field_name":"gpa","field_type":"number","is_visible":true}'
  '{"field_name":"graduation_date","field_type":"date","is_visible":true}'
)

for field in "${fields[@]}"; do
  curl -X POST $BASE_URL \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -d "$field"
  echo "Created field"
done
```

### Delete Dynamic Field

```bash
curl -X DELETE http://localhost:8000/api/admin/fields/1 \
  -H "Authorization: Bearer $TOKEN"
```

### Create Hidden Field (Not visible to users)

```bash
curl -X POST http://localhost:8000/api/admin/fields \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "field_name": "internal_notes",
    "field_type": "text",
    "is_visible": false
  }'
```

---

## Error Handling

### Missing Required Field

```bash
curl -X POST http://localhost:8000/api/students \
  -F "full_name=John" \
  -F "class=10" \
  # Missing school_name, grade, certificate
```

**Response** (422):

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "school_name": ["The school name field is required."],
    "grade": ["The grade field is required."],
    "certificate": ["The certificate field is required."]
  }
}
```

### Invalid Grade (Out of Range)

```bash
curl -X POST http://localhost:8000/api/students \
  -F "full_name=John" \
  -F "class=10" \
  -F "school_name=High School" \
  -F "grade=150" \
  -F "certificate=@cert.jpg"
```

**Response** (422):

```json
{
  "message": "The grade field must not be greater than 100.",
  "errors": {
    "grade": ["The grade field must not be greater than 100."]
  }
}
```

### Missing Authorization Token

```bash
curl http://localhost:8000/api/admin/students
```

**Response** (401):

```json
{
  "message": "Unauthenticated."
}
```

### Non-Admin User

```bash
# User token with is_admin=false
curl -H "Authorization: Bearer user_token" \
  http://localhost:8000/api/admin/students
```

**Response** (403):

```json
{
  "message": "Unauthorized. Admin access required."
}
```

### Invalid File Type

```bash
curl -X POST http://localhost:8000/api/students \
  -F "full_name=John" \
  -F "class=10" \
  -F "school_name=High School" \
  -F "grade=95" \
  -F "certificate=@document.pdf"  # Not an image
```

**Response** (422):

```json
{
  "message": "The certificate field must be an image.",
  "errors": {
    "certificate": ["The certificate field must be an image."]
  }
}
```

### File Too Large

```bash
# certificate.iso is 100MB (exceeds 50MB limit)
curl -X POST http://localhost:8000/api/students \
  -F "certificate=@certificate.iso"
```

**Response** (422):

```json
{
  "message": "The certificate field must not be greater than 51200 kilobytes.",
  "errors": {
    "certificate": ["The certificate field must not be greater than 51200 kilobytes."]
  }
}
```

### Duplicate Dynamic Field

```bash
# Try to create a field that already exists
curl -X POST http://localhost:8000/api/admin/fields \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "field_name": "achievements",
    "field_type": "text"
  }'
```

**Response** (422):

```json
{
  "message": "The field name has already been taken.",
  "errors": {
    "field_name": ["The field name has already been taken."]
  }
}
```

### Resource Not Found

```bash
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/admin/students/999
```

**Response** (404):

```json
{
  "message": "No query results found for model [App\\Models\\Student]."
}
```

---

## Success Response Examples

### Successful Submission

```json
{
  "message": "Student record created successfully",
  "data": {
    "id": 1,
    "full_name": "John Doe",
    "class": 10,
    "school_name": "Central High School",
    "grade": 95,
    "certificate_path": "certificates/550e8400-e29b-41d4-a716-446655440000.jpg",
    "custom_data": null,
    "created_at": "2026-05-20T12:00:00Z",
    "updated_at": "2026-05-20T12:00:00Z"
  }
}
```

### Successful Admin Create

```json
{
  "message": "Student created successfully",
  "data": {
    "id": 2,
    "full_name": "Jane Smith",
    "class": 11,
    "school_name": "Lincoln High",
    "grade": 88,
    "certificate_path": null,
    "custom_data": null,
    "created_at": "2026-05-20T13:00:00Z",
    "updated_at": "2026-05-20T13:00:00Z"
  }
}
```

### Successful List Response

```json
{
  "data": [
    {
      "id": 1,
      "full_name": "John Doe",
      "class": 10,
      "school_name": "Central High School",
      "grade": 95,
      "certificate_path": "certificates/uuid1.jpg",
      "custom_data": null,
      "created_at": "2026-05-20T12:00:00Z",
      "updated_at": "2026-05-20T12:00:00Z"
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/admin/students?page=1",
    "last": "http://localhost:8000/api/admin/students?page=5",
    "prev": null,
    "next": "http://localhost:8000/api/admin/students?page=2"
  },
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

---

## Tips & Tricks

### Save Token to Variable

```bash
TOKEN=$(php artisan tinker --execute='echo User::first()->createToken("token")->plainTextToken;')
echo "Token: $TOKEN"
```

### Create Alias for Admin Requests

```bash
alias admin-api='curl -H "Authorization: Bearer $TOKEN"'

# Usage
admin-api http://localhost:8000/api/admin/students
```

### Format JSON Output

```bash
curl -s -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/admin/students | json_pp
```

### Export Data to CSV (Linux/Mac)

```bash
TOKEN=your_token

curl -s -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/admin/students | \
  jq -r '.data[] | [.id, .full_name, .class, .grade] | @csv' > students.csv
```

### Check API Availability

```bash
curl -w "\nStatus: %{http_code}\n" \
  http://localhost:8000/api/students
```

---

**Last Updated**: May 20, 2026
