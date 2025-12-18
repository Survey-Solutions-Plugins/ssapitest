<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Workspace;
use App\Models\Questionnaire;
use App\Models\Assignment;
use App\Models\Interview;
use App\Models\Supervisor;
use App\Models\Interviewer;
use Illuminate\Support\Facades\Hash;

class SurveySolutionsSeeder extends Seeder
{
    /**
     * Run the database seeds for Survey Solutions API
     */
    public function run(): void
    {
        // Create Administrator
        $admin = User::create([
            'name' => 'System Administrator',
            'username' => 'Admin',
            'email' => 'admin@surveyapi.com',
            'password' => Hash::make('password123'),
            'role' => 'administrator',
            'phone' => '+1234567890'
        ]);

        // Create Headquarters User
        $hq = User::create([
            'name' => 'Headquarters Manager',
            'username' => 'HQ',
            'email' => 'hq@surveyapi.com',
            'password' => Hash::make('password123'),
            'role' => 'headquarters',
            'phone' => '+1234567891'
        ]);

        // Create Workspaces
        $workspace1 = Workspace::create([
            'name' => 'survey_workspace_1',
            'display_name' => 'Survey Workspace 1',
            'description' => 'Primary survey workspace for data collection'
        ]);

        $workspace2 = Workspace::create([
            'name' => 'demo_workspace',
            'display_name' => 'Demo Workspace',
            'description' => 'Demo workspace for testing purposes'
        ]);

        // Create Supervisor Users
        $supervisor1 = User::create([
            'name' => 'John Supervisor',
            'username' => 'Supervisor1',
            'email' => 'supervisor1@surveyapi.com',
            'password' => Hash::make('password123'),
            'role' => 'supervisor',
            'phone' => '+1234567892'
        ]);

        $supervisor2 = User::create([
            'name' => 'Jane Supervisor',
            'username' => 'Supervisor2',
            'email' => 'supervisor2@surveyapi.com',
            'password' => Hash::make('password123'),
            'role' => 'supervisor',
            'phone' => '+1234567893'
        ]);

        // Create Supervisor records
        $sup1 = Supervisor::create([
            'user_id' => $supervisor1->id,
            'workspace_id' => $workspace1->id
        ]);

        $sup2 = Supervisor::create([
            'user_id' => $supervisor2->id,
            'workspace_id' => $workspace2->id
        ]);

        // Create Interviewer Users
        $interviewer1 = User::create([
            'name' => 'Alice Interviewer',
            'username' => 'Interviewer1',
            'email' => 'interviewer1@surveyapi.com',
            'password' => Hash::make('password123'),
            'role' => 'interviewer',
            'phone' => '+1234567894'
        ]);

        $interviewer2 = User::create([
            'name' => 'Bob Interviewer',
            'username' => 'Interviewer2',
            'email' => 'interviewer2@surveyapi.com',
            'password' => Hash::make('password123'),
            'role' => 'interviewer',
            'phone' => '+1234567895'
        ]);

        $interviewer3 = User::create([
            'name' => 'Charlie Interviewer',
            'username' => 'Interviewer3',
            'email' => 'interviewer3@surveyapi.com',
            'password' => Hash::make('password123'),
            'role' => 'interviewer',
            'phone' => '+1234567896'
        ]);

        // Create Interviewer records
        Interviewer::create([
            'user_id' => $interviewer1->id,
            'supervisor_id' => $sup1->id,
            'workspace_id' => $workspace1->id
        ]);

        Interviewer::create([
            'user_id' => $interviewer2->id,
            'supervisor_id' => $sup1->id,
            'workspace_id' => $workspace1->id
        ]);

        Interviewer::create([
            'user_id' => $interviewer3->id,
            'supervisor_id' => $sup2->id,
            'workspace_id' => $workspace2->id
        ]);

        // Create Questionnaires
        $questionnaire1 = Questionnaire::create([
            'questionnaire_id' => 'HOUSEHOLD_SURVEY_2024',
            'version' => 'v1.0',
            'title' => 'Household Demographics Survey 2024',
            'description' => 'Comprehensive household demographics and socio-economic survey',
            'document' => [
                'sections' => [
                    ['id' => 'demographics', 'title' => 'Demographics'],
                    ['id' => 'economics', 'title' => 'Economic Status'],
                    ['id' => 'health', 'title' => 'Health Information']
                ]
            ],
            'audio_recording_enabled' => true,
            'criticality_level' => 'normal',
            'workspace_id' => $workspace1->id
        ]);

        $questionnaire2 = Questionnaire::create([
            'questionnaire_id' => 'BUSINESS_SURVEY_2024',
            'version' => 'v1.0',
            'title' => 'Small Business Impact Survey 2024',
            'description' => 'Survey to assess small business economic impact',
            'document' => [
                'sections' => [
                    ['id' => 'business_info', 'title' => 'Business Information'],
                    ['id' => 'impact', 'title' => 'Economic Impact'],
                    ['id' => 'outlook', 'title' => 'Future Outlook']
                ]
            ],
            'audio_recording_enabled' => false,
            'criticality_level' => 'critical',
            'workspace_id' => $workspace2->id
        ]);

        // Create Assignments
        $assignment1 = Assignment::create([
            'questionnaire_id' => $questionnaire1->id,
            'workspace_id' => $workspace1->id,
            'responsible_id' => $supervisor1->id,
            'identifying_data' => [
                'region' => 'North District',
                'area_code' => 'ND001',
                'interviewer_target' => 50
            ],
            'quantity' => 50,
            'interviews_count' => 0,
            'archived' => false,
            'audio_recording' => true,
            'status' => 'assigned'
        ]);

        $assignment2 = Assignment::create([
            'questionnaire_id' => $questionnaire2->id,
            'workspace_id' => $workspace2->id,
            'responsible_id' => $supervisor2->id,
            'identifying_data' => [
                'region' => 'South District',
                'area_code' => 'SD001',
                'interviewer_target' => 30
            ],
            'quantity' => 30,
            'interviews_count' => 0,
            'archived' => false,
            'audio_recording' => false,
            'status' => 'assigned'
        ]);

        // Create Sample Interviews
        $interview1 = Interview::create([
            'interview_id' => 'INT_' . uniqid(),
            'assignment_id' => $assignment1->id,
            'questionnaire_id' => $questionnaire1->id,
            'workspace_id' => $workspace1->id,
            'interviewer_id' => $interviewer1->id,
            'supervisor_id' => $supervisor1->id,
            'status' => 'created',
            'identifying_data' => [
                'household_id' => 'HH001',
                'address' => '123 Main Street',
                'respondent_name' => 'John Smith'
            ],
            'answers' => [
                'demographics' => [
                    'household_size' => 4,
                    'head_age' => 45,
                    'education_level' => 'university'
                ]
            ],
            'has_errors' => false,
            'errors_count' => 0
        ]);

        $interview2 = Interview::create([
            'interview_id' => 'INT_' . uniqid(),
            'assignment_id' => $assignment2->id,
            'questionnaire_id' => $questionnaire2->id,
            'workspace_id' => $workspace2->id,
            'interviewer_id' => $interviewer3->id,
            'supervisor_id' => $supervisor2->id,
            'status' => 'interview_completed',
            'identifying_data' => [
                'business_id' => 'BIZ001',
                'business_name' => 'Local Coffee Shop',
                'owner_name' => 'Sarah Johnson'
            ],
            'answers' => [
                'business_info' => [
                    'employee_count' => 5,
                    'annual_revenue' => 150000,
                    'industry' => 'food_service'
                ]
            ],
            'has_errors' => false,
            'errors_count' => 0
        ]);

        // Update assignment interview counts
        $assignment1->update(['interviews_count' => 1]);
        $assignment2->update(['interviews_count' => 1]);

        $this->command->info('Survey Solutions seeder completed successfully!');
        $this->command->info('Created users:');
        $this->command->info('- Administrator: admin@surveyapi.com / password123');
        $this->command->info('- Headquarters: hq@surveyapi.com / password123');
        $this->command->info('- Supervisors: supervisor1@surveyapi.com, supervisor2@surveyapi.com / password123');
        $this->command->info('- Interviewers: interviewer1@surveyapi.com, interviewer2@surveyapi.com, interviewer3@surveyapi.com / password123');
        $this->command->info('Created 2 workspaces, 2 questionnaires, 2 assignments, and 2 sample interviews');
    }
}
