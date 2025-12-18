# Laravel Survey Solutions API System

This is a comprehensive Laravel-based API system that replicates the functionality of Survey Solutions headquarters application. The system provides complete survey management capabilities including workspace management, questionnaire handling, assignment creation, interview management, and user administration.

## ✅ Completed Project Structure

### Models & Database
- **Users**: Role-based authentication (Administrator, Headquarters, Supervisor, Interviewer)
- **Workspaces**: Survey workspace containers with enable/disable functionality  
- **Questionnaires**: Survey definitions with versions and audio recording settings
- **Assignments**: Survey assignments with quantity controls and responsible persons
- **Interviews**: Interview instances with comprehensive status tracking
- **Interview Answers**: Individual question responses with various data types
- **Supervisors**: Supervisor role assignments to workspaces
- **Interviewers**: Interviewer assignments to supervisors and workspaces

### API Controllers (Survey Solutions Compatible)
- **WorkspaceController**: Complete workspace management endpoints
- **AssignmentController**: Assignment lifecycle management
- **InterviewController**: Interview status management and approvals
- **UserController**: User management across all roles
- **AuthController**: Sanctum-based API authentication

### Key Features ✅
- **Multi-workspace support** with proper isolation
- **Hierarchical user management** (Admin → HQ → Supervisor → Interviewer)  
- **Assignment management** with quantity tracking and audio recording settings
- **Interview lifecycle** with status flows and approval processes
- **API authentication** using Laravel Sanctum tokens
- **Comprehensive seeding** with test accounts and sample data
- **Role-based access control** throughout the system

## 🚀 Ready to Use

The system is fully functional and includes:

### Test Accounts (password: password123)
- **Administrator**: admin@surveyapi.com
- **Headquarters**: hq@surveyapi.com  
- **Supervisors**: supervisor1@surveyapi.com, supervisor2@surveyapi.com
- **Interviewers**: interviewer1@surveyapi.com, interviewer2@surveyapi.com, interviewer3@surveyapi.com

### Sample Data
- 2 workspaces (survey_workspace_1, demo_workspace)
- 2 questionnaires with different settings
- 2 assignments with sample identifying data
- 2 interviews in different statuses
- Complete user hierarchy

### Server Access
- API Base URL: `http://localhost:8000/api/v1`
- Server started with: `php artisan serve`
- Database: SQLite (ready with migrations and seeded data)

## API Endpoints

All endpoints match Survey Solutions API structure:
- Authentication: `/auth/login`, `/auth/logout`, `/auth/me`
- Workspaces: Full CRUD with enable/disable
- Assignments: Create, manage, archive, close, assign responsibility
- Interviews: Status management, approvals, rejections, statistics
- Users: Create supervisors/interviewers, manage teams

## Development Guidelines
- Follow Survey Solutions API patterns and naming conventions
- Use proper role-based authorization for all endpoints
- Maintain hierarchical relationships between users and workspaces
- Implement proper status flows for interviews and assignments
- Use comprehensive validation and error handling
- Follow Laravel best practices with Eloquent relationships