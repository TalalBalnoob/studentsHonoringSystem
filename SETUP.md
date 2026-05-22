# Students Honoring System - API

A comprehensive Laravel REST API for managing student honor certificates and academic achievement tracking.

## Features

✅ **Public Student Submission**: Anonymous form for students to submit their name, class, school, grade, and certificate image  
✅ **Admin Dashboard API**: Full CRUD operations for student records with authentication  
✅ **Advanced Filtering**: Filter by class, grade, school name  
✅ **Search & Sort**: Search by student name/school, sort by class/grade/date  
✅ **Dynamic Columns**: Admins can add custom fields to student records  
✅ **File Storage**: Secure certificate image uploads to Supabase Storage  
✅ **Database**: PostgreSQL via Supabase  
✅ **API Authentication**: Laravel Sanctum for admin token-based access  
✅ **Comprehensive Testing**: 25 feature tests covering all endpoints  

## Tech Stack

- **Framework**: Laravel 13
- **Database**: PostgreSQL (Supabase)
- **File Storage**: Supabase Storage (S3-compatible)
- **Authentication**: Laravel Sanctum
- **API Style**: REST with JSON responses
- **Testing**: PHPUnit with comprehensive feature tests

## Quick Start

### Prerequisites

- PHP 8.2+
- Composer
- PostgreSQL (or Supabase)
- Node.js & npm (for frontend builds)

### Installation

1. **Clone the repository**

   ```bash
   git clone <repo-url>
   cd studentsHonoringSystem
   ```

2. **Install dependencies**

   ```bash
   composer install
   npm install
   ```

3. **Set up environment**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure Supabase credentials in `.env`**

   ```env
   DB_CONNECTION=pgsql
   DB_HOST=db.vpkxfiywlhsdowqosuqi.supabase.co
   DB_PORT=5432
   DB_DATABASE=postgres
   DB_USERNAME=postgres
   DB_PASSWORD=your_password

   SUPABASE_URL=https://vpkxfiywlhsdowqosuqi.supabase.co
   SUPABASE_KEY=your_api_key
   SUPABASE_BUCKET=certificates
   ```

5. **Run migrations**

   ```bash
   php artisan migrate
   ```

6. **Seed admin user**

   ```bash
   php artisan db:seed --class=AdminSeeder
   ```

7. **Start development server**

   ```bash
   php artisan serve
   npm run dev
   ```

## API Endpoints

### Public Endpoints

- `POST /api/students` - Submit student certificate (anonymous)

### Admin Endpoints (Requires Sanctum Token + is_admin role)

**Students Management**:

- `GET /api/admin/students` - List students (with filtering, search, sort)
- `GET /api/admin/students/{id}` - View single student
- `POST /api/admin/students` - Create student
- `PUT /api/admin/students/{id}` - Update student
- `DELETE /api/admin/students/{id}` - Delete student

**Dynamic Fields**:

- `GET /api/admin/fields` - List dynamic fields
- `POST /api/admin/fields` - Create dynamic field
- `DELETE /api/admin/fields/{id}` - Delete dynamic field

See [API_DOCUMENTATION.md](API_DOCUMENTATION.md) for complete endpoint documentation.

## Authentication

### Get Admin Token

1. **Create admin user** (if not already created):

   ```bash
   php artisan tinker
   User::create([
     'name' => 'Admin',
     'email' => 'admin@example.com',
     'password' => bcrypt('password'),
     'is_admin' => true,
   ]);
   ```

2. **Generate token**:

   ```bash
   $admin = User::where('email', 'admin@example.com')->first();
   $token = $admin->createToken('api-token')->plainTextToken;
   echo $token;
   ```

3. **Use token in API requests**:

   ```bash
   curl -H "Authorization: Bearer <token>" \
     https://your-app.com/api/admin/students
   ```

## Example Requests

### Submit Student Certificate (Public)

```bash
curl -X POST http://localhost:8000/api/students \
  -F "full_name=John Doe" \
  -F "class=10" \
  -F "school_name=Central High School" \
  -F "grade=95" \
  -F "certificate=@certificate.jpg"
```

### List Students with Filtering (Admin)

```bash
curl -H "Authorization: Bearer <token>" \
  "http://localhost:8000/api/admin/students?class=10&sort_by=grade&sort_order=desc"
```

### Search Students (Admin)

```bash
curl -H "Authorization: Bearer <token>" \
  "http://localhost:8000/api/admin/students?search=John&school_name=Central"
```

### Create Dynamic Field (Admin)

```bash
curl -X POST http://localhost:8000/api/admin/fields \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{
    "field_name": "achievements",
    "field_type": "text",
    "is_visible": true
  }'
```

## Testing

Run the complete test suite:

```bash
# All tests
php artisan test --compact

# Student submission tests
php artisan test tests/Feature/StudentSubmissionTest.php

# Admin management tests
php artisan test tests/Feature/AdminStudentManagementTest.php

# Dynamic field tests
php artisan test tests/Feature/AdminFieldManagementTest.php
```

**Test Results**: 25 passing tests covering:

- ✓ Public student form submission
- ✓ Form validation (all fields, types, sizes)
- ✓ Admin authentication & authorization
- ✓ Admin CRUD operations
- ✓ Filtering by class, grade, school
- ✓ Search by name/school
- ✓ Sorting by class/grade/date
- ✓ Dynamic field management

## Database Schema

### students table

```sql
- id (primary key)
- full_name (string)
- class (integer, 1-12)
- school_name (string)
- grade (integer, 0-100)
- certificate_path (string, nullable)
- custom_data (JSON, nullable)
- timestamps (created_at, updated_at)
```

### dynamic_fields table

```sql
- id (primary key)
- field_name (string, unique)
- field_type (string: text|number|date|email|url)
- is_visible (boolean)
- order (integer)
- timestamps (created_at, updated_at)
```

### users table (modified)

```sql
- id (primary key)
- name, email (existing)
- is_admin (boolean, added)
- password, timestamps (existing)
```

## File Storage

Certificate images are stored in Supabase Storage at:

```
https://vpkxfiywlhsdowqosuqi.supabase.co/storage/v1/object/public/certificates/{uuid}.jpg
```

Max file size: 50MB  
Allowed formats: JPEG, PNG, JPG, GIF

## Configuration

### Validation Rules

**Student Fields**:

- `full_name`: max 255 characters
- `class`: 1-12 (representing grades/classes)
- `school_name`: max 255 characters
- `grade`: 0-100 (percentage-based)
- `certificate`: image file, max 50MB

**Dynamic Field Types**:

- `text`: Text input
- `number`: Numeric input
- `date`: Date input
- `email`: Email input
- `url`: URL input

## Project Structure

```
app/
  ├── Http/
  │   ├── Controllers/
  │   │   ├── StudentController.php (public submissions)
  │   │   └── Admin/
  │   │       ├── AdminStudentController.php (CRUD)
  │   │       └── AdminFieldController.php (dynamic fields)
  │   ├── Middleware/
  │   │   └── IsAdmin.php (authorization)
  │   └── Requests/
  │       ├── StoreStudentRequest.php (public validation)
  │       ├── StoreAdminStudentRequest.php (admin creation)
  │       └── UpdateStudentRequest.php (admin updates)
  ├── Models/
  │   ├── Student.php
  │   ├── DynamicField.php
  │   └── User.php
  └── Services/
      └── CertificateStorageService.php (S3 uploads/deletes)

database/
  ├── migrations/
  │   ├── 2026_05_20_164323_create_students_table.php
  │   ├── 2026_05_20_164323_create_dynamic_fields_table.php
  │   └── 2026_05_20_164323_add_is_admin_to_users_table.php
  ├── factories/
  │   ├── StudentFactory.php
  │   └── DynamicFieldFactory.php
  └── seeders/
      └── AdminSeeder.php

routes/
  └── api.php (all API routes)

tests/
  └── Feature/
      ├── StudentSubmissionTest.php
      ├── AdminStudentManagementTest.php
      └── AdminFieldManagementTest.php
```

## Error Handling

All errors return proper HTTP status codes and JSON responses:

- **400**: Bad Request (invalid parameters)
- **401**: Unauthorized (missing/invalid token)
- **403**: Forbidden (not admin)
- **404**: Not Found (resource doesn't exist)
- **422**: Unprocessable Entity (validation errors)
- **500**: Internal Server Error

## Security Features

1. **Sanctum Tokens**: Secure API token authentication for admins
2. **Admin Middleware**: Protects all admin endpoints
3. **Form Validation**: Server-side validation of all inputs
4. **File Validation**: Type and size checks on certificate uploads
5. **Database Constraints**: Unique fields, proper types
6. **CORS**: Configurable cross-origin requests

## Troubleshooting

### Certificate Upload Fails (403 Forbidden)

Ensure Supabase bucket name matches your configuration:

```env
SUPABASE_BUCKET=certificates
```

### Admin Token Expired

Regenerate token:

```bash
php artisan tinker
$admin->tokens()->delete(); // Delete old tokens
$token = $admin->createToken('api-token')->plainTextToken;
```

### Database Connection Error

Verify Supabase credentials in `.env` and ensure the database is accessible.

## Performance Considerations

- **Pagination**: Admin endpoints paginate at 15 items per page by default
- **Indexing**: Database has indexes on class, grade, created_at for fast queries
- **File Uploads**: Large files (>50MB) are rejected to prevent slowdowns
- **Caching**: Consider adding Redis caching for frequently accessed data

## Future Enhancements

- Email notifications on new submissions
- Webhook integrations
- CSV export for student records
- Certificate preview/thumbnail generation
- Rate limiting for public endpoint
- Batch operations (bulk delete/update)
- Admin audit logs

## Contributing

To add features or fix bugs:

1. Create a feature branch
2. Write tests for new functionality
3. Ensure all tests pass: `php artisan test`
4. Submit a pull request

## License

This project is licensed under the MIT License.

## Support

For issues, questions, or feature requests, open an issue in the repository or contact the development team.

---

**Last Updated**: May 20, 2026  
**API Version**: 1.0  
**Maintenance**: Active
