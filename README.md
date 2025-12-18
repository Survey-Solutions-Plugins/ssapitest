# Laravel Survey Solutions API System

A comprehensive Laravel-based API system that replicates the functionality of Survey Solutions headquarters application. This system provides complete survey management capabilities including workspace management, questionnaire handling, assignment creation, interview management, and user administration.

## 🚀 Features

### Core Functionality
- **Workspace Management**: Create, manage, and control access to survey workspaces
- **Questionnaire Management**: Import and manage survey questionnaires with versions
- **Assignment System**: Create and manage survey assignments with quantity controls
- **Interview Management**: Complete interview lifecycle management with status tracking
- **User Management**: Hierarchical user system (Administrator → Headquarters → Supervisor → Interviewer)
- **Authentication**: Secure API authentication using Laravel Sanctum
- **Role-based Access**: Different permissions for different user roles

### API Endpoints (Survey Solutions Compatible)

#### Authentication
- `POST /api/v1/auth/login` - Authenticate user and get access token
- `POST /api/v1/auth/logout` - Logout and revoke token
- `GET /api/v1/auth/me` - Get authenticated user information
- `POST /api/v1/auth/refresh` - Refresh access token

#### Workspaces
- `GET /api/v1/workspaces` - List all workspaces
- `POST /api/v1/workspaces` - Create new workspace (Admin only)
- `GET /api/v1/workspaces/{name}` - Get workspace details
- `PATCH /api/v1/workspaces/{name}` - Update workspace
- `DELETE /api/v1/workspaces/{name}` - Delete workspace
- `GET /api/v1/workspaces/status/{name}` - Get workspace status
- `POST /api/v1/workspaces/{name}/disable` - Disable workspace
- `POST /api/v1/workspaces/{name}/enable` - Enable workspace

#### Assignments
- `GET /api/v1/assignments` - List assignments with filtering
- `POST /api/v1/assignments` - Create new assignment
- `GET /api/v1/assignments/{id}` - Get assignment details
- `PATCH /api/v1/assignments/{id}/archive` - Archive assignment
- `PATCH /api/v1/assignments/{id}/assign` - Assign responsible person
- `PATCH /api/v1/assignments/{id}/changeQuantity` - Change assignment quantity
- `PATCH /api/v1/assignments/{id}/close` - Close assignment
- `GET /api/v1/assignments/{id}/recordAudio` - Get audio recording status
- `PATCH /api/v1/assignments/{id}/recordAudio` - Set audio recording

#### Interviews
- `GET /api/v1/interviews` - List interviews with filtering
- `GET /api/v1/interviews/{id}` - Get interview details and answers
- `DELETE /api/v1/interviews/{id}` - Delete interview
- `PATCH /api/v1/interviews/{id}/approve` - Approve interview (Supervisor)
- `PATCH /api/v1/interviews/{id}/assign` - Assign interviewer
- `PATCH /api/v1/interviews/{id}/assignsupervisor` - Assign supervisor
- `PATCH /api/v1/interviews/{id}/hqapprove` - Approve interview (HQ)
- `PATCH /api/v1/interviews/{id}/hqreject` - Reject interview (HQ)
- `PATCH /api/v1/interviews/{id}/reject` - Reject interview (Supervisor)
- `GET /api/v1/interviews/{id}/stats` - Get interview statistics

#### Users
- `POST /api/v1/users` - Create new user
- `GET /api/v1/users/{id}` - Get user details
- `PATCH /api/v1/users/{id}/archive` - Archive user
- `PATCH /api/v1/users/{id}/unarchive` - Unarchive user
- `GET /api/v1/supervisors` - List supervisors
- `GET /api/v1/supervisors/{id}` - Get supervisor details
- `GET /api/v1/supervisors/{id}/interviewers` - Get supervisor's interviewers
- `GET /api/v1/interviewers` - List interviewers
- `GET /api/v1/interviewers/{id}` - Get interviewer details

## 🏗️ System Architecture

### Database Structure
- **Users**: Base user table with role-based authentication
- **Workspaces**: Survey workspace containers
- **Questionnaires**: Survey questionnaire definitions with versions
- **Assignments**: Survey assignments with quantity and targeting
- **Interviews**: Interview instances with status tracking
- **Interview Answers**: Individual question responses
- **Supervisors**: Supervisor role assignments to workspaces
- **Interviewers**: Interviewer assignments to supervisors and workspaces

### User Roles & Hierarchy
1. **Administrator**: System-wide access, workspace creation
2. **Headquarters**: Survey management, user oversight
3. **Supervisor**: Assignment management, interview approval
4. **Interviewer**: Interview execution, data collection

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- SQLite/MySQL
- Web server (Apache/Nginx)

### Installation

1. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed --class=SurveySolutionsSeeder
   ```

2. **Start the server**
   ```bash
   php artisan serve
   ```

The API will be available at `http://localhost:8000/api/v1`

### Test Accounts

After seeding, you can use these test accounts:

- **Administrator**: `admin@surveyapi.com` / `password123`
- **Headquarters**: `hq@surveyapi.com` / `password123`  
- **Supervisor 1**: `supervisor1@surveyapi.com` / `password123`
- **Supervisor 2**: `supervisor2@surveyapi.com` / `password123`
- **Interviewer 1**: `interviewer1@surveyapi.com` / `password123`
- **Interviewer 2**: `interviewer2@surveyapi.com` / `password123`
- **Interviewer 3**: `interviewer3@surveyapi.com` / `password123`

## 📝 API Usage Examples

### Authentication
```bash
# Login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@surveyapi.com","password":"password123"}'
```

### Create Workspace
```bash
curl -X POST http://localhost:8000/api/v1/workspaces \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "new_workspace",
    "display_name": "New Survey Workspace",
    "description": "Description of the workspace"
  }'
```

### List Assignments with Filtering
```bash
curl -X GET "http://localhost:8000/api/v1/assignments?workspace_id=1&status=assigned" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 🔧 Configuration

### Environment Variables
```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

### Laravel Sanctum Configuration
The system uses Laravel Sanctum for API authentication. Tokens are returned upon successful login and should be included in the Authorization header for protected routes.

## 🧪 Testing

The seeder creates comprehensive test data:
- 2 workspaces
- 2 questionnaires  
- 2 assignments
- 2 sample interviews
- 7 users across all roles

## 🔐 Security Features

- **Token-based authentication** using Laravel Sanctum
- **Role-based access control** with hierarchical permissions
- **Input validation** on all API endpoints
- **Account status checks** (archived/locked users)
- **SQL injection protection** via Eloquent ORM
- **Password hashing** using Laravel's built-in hashing

## 📊 Data Models

### Interview Status Flow
```
created → interview_completed → supervisor_assigned → 
approved_by_supervisor → approved_by_headquarters
                      ↓
                  rejected_by_supervisor
                      ↓  
                  rejected_by_headquarters
```

### Assignment Status Flow  
```
created → assigned → completed → archived
```

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

**Built with Laravel 12 & Laravel Sanctum**  
*Replicating Survey Solutions Headquarters API functionality*

For testing, use the provided test accounts and explore the comprehensive API endpoints that mirror the Survey Solutions structure and functionality.
