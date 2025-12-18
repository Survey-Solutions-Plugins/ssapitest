@extends('layouts.app')

@section('title', 'Survey Solutions - Dashboard')

@section('content')
<div class="container-fluid mt-4">
    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('warning') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('error') }}
        </div>
    @endif

    @if(!empty($apiWarnings) && count($apiWarnings) > 0)
        <div class="alert alert-warning">
            <div class="d-flex align-items-center mb-2">
                <i class="fas fa-triangle-exclamation me-2"></i>
                <strong>Headquarters API warnings</strong>
            </div>
            <ul class="mb-0">
                @foreach($apiWarnings as $w)
                    <li>{{ $w }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="sidebar p-3">
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item mb-2">
                        <a class="nav-link {{ ($activeModule ?? 'overview') === 'overview' ? 'active' : '' }}" href="{{ route('dashboard.module', ['module' => 'overview']) }}">
                            <i class="fas fa-chart-line me-2"></i>Overview
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link {{ ($activeModule ?? 'overview') === 'workspaces' ? 'active' : '' }}" href="{{ route('dashboard.module', ['module' => 'workspaces']) }}">
                            <i class="fas fa-folder me-2"></i>Workspaces
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link {{ ($activeModule ?? 'overview') === 'assignments' ? 'active' : '' }}" href="{{ route('dashboard.module', ['module' => 'assignments']) }}">
                            <i class="fas fa-tasks me-2"></i>Assignments
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link {{ ($activeModule ?? 'overview') === 'interviews' ? 'active' : '' }}" href="{{ route('dashboard.module', ['module' => 'interviews']) }}">
                            <i class="fas fa-clipboard-check me-2"></i>Interviews
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link {{ ($activeModule ?? 'overview') === 'users' ? 'active' : '' }}" href="{{ route('dashboard.module', ['module' => 'users']) }}">
                            <i class="fas fa-users me-2"></i>Users
                        </a>
                    </li>
                </ul>

                <hr>

                <div class="mt-3">
                    <small class="text-muted">Connected User</small>
                    <div class="bg-white p-2 rounded mt-1">
                        <strong>{{ $username }}</strong><br>
                        <small class="text-success">
                            <i class="fas fa-circle me-1"></i>Connected
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <!-- Overview Stats -->
            @if(($activeModule ?? 'overview') === 'overview')
            <div id="overview" class="mb-4">
                <h4 class="mb-3">Survey Solutions API Overview</h4>
                <div class="alert alert-info">
                    <i class="fas fa-key me-2"></i>
                    <strong>API User Mode:</strong> This interface is restricted to API User accounts only.
                </div>
                @if(config('app.debug') && !empty($usersDebug) && is_array($usersDebug))
                    <div class="text-muted small mb-2">
                        Users debug: supervisors raw {{ (int) data_get($usersDebug, 'supervisors_raw', 0) }}, added {{ (int) data_get($usersDebug, 'supervisors_added', 0) }}; team interviewers raw {{ (int) data_get($usersDebug, 'supervisor_interviewers_raw', 0) }}, added {{ (int) data_get($usersDebug, 'supervisor_interviewers_added', 0) }}; interviewers list status {{ data_get($usersDebug, 'interviewers_list_status') ?? 'n/a' }}.
                    </div>
                @endif
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="dashboard-card card border-primary">
                            <div class="card-body text-center">
                                <i class="fas fa-folder text-primary fa-2x mb-2"></i>
                                <h5 class="text-primary">{{ $workspaces->count() }}</h5>
                                <small>Workspaces</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="dashboard-card card border-warning">
                            <div class="card-body text-center">
                                <i class="fas fa-tasks text-warning fa-2x mb-2"></i>
                                <h5 class="text-warning">{{ $assignments->count() }}</h5>
                                <small>Active Assignments</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="dashboard-card card border-success">
                            <div class="card-body text-center">
                                <i class="fas fa-clipboard-check text-success fa-2x mb-2"></i>
                                <h5 class="text-success">{{ $interviews->count() }}</h5>
                                <small>Recent Interviews</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="dashboard-card card border-info">
                            <div class="card-body text-center">
                                <i class="fas fa-key text-info fa-2x mb-2"></i>
                                @if(!empty($usersAccessDenied))
                                    <h5 class="text-info">—</h5>
                                    <small>ApiUsers (forbidden)</small>
                                @elseif(!empty($usersAccessError))
                                    <h5 class="text-info">—</h5>
                                    <small>ApiUsers (unavailable)</small>
                                @else
                                    <h5 class="text-info">{{ $users->where('role', 'ApiUser')->count() }}</h5>
                                    <small>ApiUsers</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="dashboard-card card border-warning">
                            <div class="card-body text-center">
                                <i class="fas fa-user-tie text-warning fa-2x mb-2"></i>
                                @php($supervisorsCount = $users->where('role', 'Supervisor')->count())
                                @php($supervisorsFallback = is_array($workspaceStatus ?? null) ? (data_get($workspaceStatus, 'SupervisorsCount') ?? data_get($workspaceStatus, 'supervisorsCount')) : null)
                                @if(!empty($usersAccessDenied) || !empty($usersAccessError))
                                    <h5 class="text-warning">{{ $supervisorsFallback !== null ? $supervisorsFallback : '—' }}</h5>
                                    <small>Supervisors</small>
                                @else
                                    <h5 class="text-warning">{{ $supervisorsCount > 0 ? $supervisorsCount : ($supervisorsFallback !== null ? $supervisorsFallback : 0) }}</h5>
                                    <small>Supervisors</small>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="dashboard-card card border-secondary">
                            <div class="card-body text-center">
                                <i class="fas fa-user text-secondary fa-2x mb-2"></i>
                                @php($interviewersCount = $users->where('role', 'Interviewer')->count())
                                @php($interviewersFallback = is_array($workspaceStatus ?? null) ? (data_get($workspaceStatus, 'InterviewersCount') ?? data_get($workspaceStatus, 'interviewersCount')) : null)
                                @if(!empty($usersAccessDenied) || !empty($usersAccessError))
                                    <h5 class="text-secondary">{{ $interviewersFallback !== null ? $interviewersFallback : '—' }}</h5>
                                    <small>Interviewers</small>
                                @else
                                    <h5 class="text-secondary">{{ $interviewersCount > 0 ? $interviewersCount : ($interviewersFallback !== null ? $interviewersFallback : 0) }}</h5>
                                    <small>Interviewers</small>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="dashboard-card card border-primary">
                            <div class="card-body text-center">
                                <i class="fas fa-building text-primary fa-2x mb-2"></i>
                                @if(!empty($usersAccessDenied) || !empty($usersAccessError))
                                    <h5 class="text-primary">—</h5>
                                    <small>Headquarters</small>
                                @else
                                    <h5 class="text-primary">{{ $users->where('role', 'Headquarter')->count() }}</h5>
                                    <small>Headquarters</small>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="dashboard-card card border-info">
                            <div class="card-body text-center">
                                <i class="fas fa-eye text-info fa-2x mb-2"></i>
                                @if(!empty($usersAccessDenied) || !empty($usersAccessError))
                                    <h5 class="text-info">—</h5>
                                    <small>Observers</small>
                                @else
                                    <h5 class="text-info">{{ $users->where('role', 'Observer')->count() }}</h5>
                                    <small>Observers</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Workspaces Section -->
            @if(($activeModule ?? 'overview') === 'workspaces')
            <div id="workspaces" class="mb-4">
                @php($workspacesForbidden = !empty($apiWarnings) && collect($apiWarnings)->contains(fn ($w) => str_contains($w, 'Workspaces: HQ returned HTTP 403')))

                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <h4 class="mb-1">Workspaces</h4>
                        <div class="text-muted small">Manage headquarters workspaces</div>
                    </div>
                    <div class="text-muted small pt-2">
                        Total: <strong>{{ $workspaces->count() }}</strong>
                    </div>
                </div>

                @if($workspacesForbidden)
                    <div class="alert alert-warning">
                        <strong>Access denied.</strong> Headquarters returned <code>403</code> for listing workspaces. You may still be connected, but your account lacks permission for this module.
                    </div>
                @endif

                <div class="row g-3">
                    <div class="col-lg-5">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-plus-circle me-2"></i>
                                    Create Workspace
                                </h5>
                            </div>
                            <div class="card-body">
                                <form class="row g-3" method="POST" action="{{ route('dashboard.workspaces.create') }}">
                                    @csrf
                                    <div class="col-12">
                                        <label class="form-label">Name</label>
                                        <input name="name" class="form-control" placeholder="e.g. demo" maxlength="12" required>
                                        <div class="form-text">Lowercase letters/numbers only, max 12.</div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Display name</label>
                                        <input name="display_name" class="form-control" placeholder="e.g. Demo workspace" maxlength="300" required>
                                    </div>
                                    <div class="col-12 d-grid">
                                        <button class="btn btn-primary" type="submit" {{ $workspacesForbidden ? 'disabled' : '' }}>Create</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-folder me-2"></i>
                                    Existing Workspaces
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($workspaces->isEmpty())
                                    <div class="text-center py-4">
                                        <div class="text-muted">No workspaces available</div>
                                        <div class="text-muted small">Create one to start organizing questionnaires and assignments.</div>
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th style="width: 20%">Name</th>
                                                    <th style="width: 35%">Display name</th>
                                                    <th style="width: 15%">Status</th>
                                                    <th style="width: 15%">Description</th>
                                                    <th style="width: 15%" class="text-end">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($workspaces as $workspace)
                                                <tr>
                                                    @php($wsName = data_get($workspace, 'name'))
                                                    <td><code>{{ $wsName }}</code></td>
                                                    <td>
                                                        <form class="d-flex gap-2" method="POST" action="{{ route('dashboard.workspaces.update', ['name' => $wsName]) }}">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input name="display_name" class="form-control form-control-sm" value="{{ data_get($workspace, 'display_name', data_get($workspace, 'displayName', '')) }}" maxlength="300" {{ $workspacesForbidden ? 'disabled' : '' }}>
                                                            <button class="btn btn-sm btn-outline-primary" type="submit" {{ $workspacesForbidden ? 'disabled' : '' }}>Save</button>
                                                        </form>
                                                    </td>
                                                    <td>
                                                        @if(data_get($workspace, 'disabled'))
                                                            <span class="badge bg-danger">Disabled</span>
                                                        @else
                                                            <span class="badge bg-success">Active</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-truncate" style="max-width: 200px">{{ data_get($workspace, 'description', '—') }}</td>
                                                    <td class="text-end">
                                                        @if(data_get($workspace, 'disabled'))
                                                            <form method="POST" action="{{ route('dashboard.workspaces.enable', ['name' => $wsName]) }}" class="d-inline">
                                                                @csrf
                                                                <button class="btn btn-sm btn-success" type="submit" {{ $workspacesForbidden ? 'disabled' : '' }}>Enable</button>
                                                            </form>
                                                        @else
                                                            <form method="POST" action="{{ route('dashboard.workspaces.disable', ['name' => $wsName]) }}" class="d-inline">
                                                                @csrf
                                                                <button class="btn btn-sm btn-outline-danger" type="submit" {{ $workspacesForbidden ? 'disabled' : '' }}>Disable</button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Assignments Section -->
            @if(($activeModule ?? 'overview') === 'assignments')
            <div id="assignments" class="mb-4">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-tasks me-2"></i>
                            Recent Assignments
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($assignments->isEmpty())
                            <p class="text-muted text-center py-3">No assignments available</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Questionnaire</th>
                                            <th>Workspace</th>
                                            <th>Responsible</th>
                                            <th>Status</th>
                                            <th>Progress</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($assignments as $assignment)
                                        <tr>
                                            <td>#{{ data_get($assignment, 'id') }}</td>
                                            <td>{{ data_get($assignment, 'questionnaire_title', data_get($assignment, 'questionnaireTitle', 'N/A')) }}</td>
                                            <td>{{ data_get($assignment, 'workspace', data_get($assignment, 'workspaceName', 'N/A')) }}</td>
                                            <td>{{ data_get($assignment, 'responsible', data_get($assignment, 'responsibleName', 'Unassigned')) }}</td>
                                            <td>
                                                <span class="badge bg-{{ 
                                                    data_get($assignment, 'status') === 'completed' ? 'success' :
                                                    (data_get($assignment, 'status') === 'assigned' ? 'primary' : 'secondary') 
                                                }}">
                                                    {{ ucfirst((string) data_get($assignment, 'status')) }}
                                                </span>
                                            </td>
                                            <td>
                                                @php($qty = (int) data_get($assignment, 'quantity', 0))
                                                @php($cnt = (int) data_get($assignment, 'interviews_count', 0))
                                                @if($qty > 0)
                                                    {{ $cnt }}/{{ $qty }}
                                                    <small class="text-muted">
                                                        ({{ round(($cnt / $qty) * 100, 1) }}%)
                                                    </small>
                                                @else
                                                    {{ $cnt }}
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Interviews Section -->
            @if(($activeModule ?? 'overview') === 'interviews')
            <div id="interviews" class="mb-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard-check me-2"></i>
                            Recent Interviews
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($interviews->isEmpty())
                            <p class="text-muted text-center py-3">No interviews available</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Interview ID</th>
                                            <th>Assignment</th>
                                            <th>Interviewer</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($interviews as $interview)
                                        <tr>
                                            <td><code>{{ data_get($interview, 'interview_id', data_get($interview, 'id')) }}</code></td>
                                            <td>#{{ data_get($interview, 'assignment_id', data_get($interview, 'assignmentId')) }}</td>
                                            <td>{{ data_get($interview, 'interviewer', data_get($interview, 'interviewerName', 'Unassigned')) }}</td>
                                            <td>
                                                <span class="badge bg-{{ 
                                                    str_contains((string) data_get($interview, 'status'), 'approved') ? 'success' :
                                                    (str_contains((string) data_get($interview, 'status'), 'rejected') ? 'danger' :
                                                    (data_get($interview, 'status') === 'completed' ? 'primary' : 'secondary'))
                                                }}">
                                                    {{ ucfirst(str_replace('_', ' ', (string) data_get($interview, 'status'))) }}
                                                </span>
                                            </td>
                                            @php($created = data_get($interview, 'created_at', data_get($interview, 'createdAt')))
                                            <td>{{ $created ? \Illuminate\Support\Carbon::parse($created)->format('M d, Y H:i') : 'N/A' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Users Section -->
            @if(($activeModule ?? 'overview') === 'users')
            <div id="users" class="mb-4">
                @php($usersForbidden = !empty($apiWarnings) && collect($apiWarnings)->contains(fn ($w) => str_contains($w, 'Users: HQ returned HTTP 403')))
                @php($preferredRoleOrder = ['ApiUser', 'Headquarter', 'Supervisor', 'Interviewer', 'Observer', 'Administrator'])
                @php($usersByRole = $users->groupBy(fn ($u) => data_get($u, 'role', 'Unknown')))

                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <h4 class="mb-1">Users</h4>
                        <div class="text-muted small">Create and view headquarters users</div>
                    </div>
                    <div class="text-muted small pt-2">
                        Total: <strong>{{ $users->count() }}</strong>
                    </div>
                </div>

                @if($usersForbidden)
                    <div class="alert alert-warning">
                        <strong>Access denied.</strong> Headquarters returned <code>403</code> for listing supervisors. You may still be connected, but your account lacks permission to view users.
                    </div>
                @endif

                <div class="row g-3">
                    <div class="col-lg-5">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-user-plus me-2"></i>
                                    Create User
                                </h5>
                            </div>
                            <div class="card-body">
                                <form class="row g-3" method="POST" action="{{ route('dashboard.users.create') }}">
                                    @csrf
                                    <div class="col-12">
                                        <label class="form-label">Role</label>
                                        <select id="createUserRole" name="role" class="form-select" required>
                                            <option value="ApiUser" selected>ApiUser</option>
                                            <option value="Headquarter">Headquarter</option>
                                            <option value="Supervisor">Supervisor</option>
                                            <option value="Interviewer">Interviewer</option>
                                            <option value="Observer">Observer</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Username</label>
                                        <input name="username" class="form-control" placeholder="e.g. apiuser1" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Password</label>
                                        <input name="password" type="password" class="form-control" required>
                                    </div>
                                    <div id="createUserSupervisorGroup" class="col-12 d-none">
                                        <label class="form-label">Supervisor</label>
                                        <input id="createUserSupervisor" name="supervisor" class="form-control" placeholder="supervisor login" disabled>
                                        <div class="form-text">Required only when Role is Interviewer.</div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Full name (optional)</label>
                                        <input name="full_name" class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Email (optional)</label>
                                        <input name="email" type="email" class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Phone (optional)</label>
                                        <input name="phone_number" class="form-control">
                                    </div>
                                    <div class="col-12 d-grid">
                                        <button class="btn btn-info text-white" type="submit">Create</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="card mb-3">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-users me-2"></i>
                                    Existing Users
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($usersForbidden)
                                    <div class="text-muted">User list unavailable due to permissions.</div>
                                @elseif($users->isEmpty())
                                    <div class="text-center py-4">
                                        <div class="text-muted">No users available</div>
                                        <div class="text-muted small">Create users in HQ to see them listed here.</div>
                                    </div>
                                @else
                                    @php($otherRoles = $usersByRole->keys()->diff($preferredRoleOrder)->values())
                                    @php($renderRoles = collect($preferredRoleOrder)->filter(fn ($r) => $usersByRole->has($r))->concat($otherRoles))

                                    @foreach($renderRoles as $roleName)
                                        @php($roleUsers = $usersByRole->get($roleName, collect())->values())

                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <strong>{{ $roleName }}</strong>
                                            <span class="text-muted small">{{ $roleUsers->count() }}</span>
                                        </div>

                                        <div class="table-responsive mb-3">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 22%">Login name</th>
                                                        <th style="width: 14%">Role</th>
                                                        <th style="width: 16%">Workspace</th>
                                                        <th style="width: 20%">Full name</th>
                                                        <th style="width: 14%">Email</th>
                                                        <th style="width: 14%">Phone number</th>
                                                        <th style="width: 10%">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($roleUsers as $user)
                                                    <tr>
                                                        <td><code>{{ data_get($user, 'username', '—') }}</code></td>
                                                        <td>
                                                            @php($r = (string) data_get($user, 'role', ''))
                                                            @php($badge = $r === 'Supervisor' ? 'warning' : ($r === 'Interviewer' ? 'secondary' : ($r === 'Headquarter' ? 'primary' : 'info')))
                                                            <span class="badge bg-{{ $badge }}">{{ $r !== '' ? $r : '—' }}</span>
                                                        </td>
                                                        <td class="text-truncate" style="max-width: 160px">{{ data_get($user, 'workspace', '—') }}</td>
                                                        <td class="text-truncate" style="max-width: 200px">{{ data_get($user, 'name', '—') }}</td>
                                                        <td class="text-truncate" style="max-width: 160px">{{ data_get($user, 'email', '—') }}</td>
                                                        <td class="text-truncate" style="max-width: 160px">{{ data_get($user, 'phone', '—') }}</td>
                                                        <td>
                                                            @if(data_get($user, 'is_archived'))
                                                                <span class="badge bg-secondary">Archived</span>
                                                            @elseif(data_get($user, 'is_locked'))
                                                                <span class="badge bg-danger">Locked</span>
                                                            @else
                                                                <span class="badge bg-success">Active</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        @if(!$loop->last)
                                            <hr>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (function () {
        const roleSelect = document.getElementById('createUserRole');
        const supervisorGroup = document.getElementById('createUserSupervisorGroup');
        const supervisorInput = document.getElementById('createUserSupervisor');

        if (!roleSelect || !supervisorGroup || !supervisorInput) return;

        function applyRoleUi() {
            const isInterviewer = roleSelect.value === 'Interviewer';
            supervisorGroup.classList.toggle('d-none', !isInterviewer);
            supervisorInput.disabled = !isInterviewer;
            if (!isInterviewer) supervisorInput.value = '';
        }

        roleSelect.addEventListener('change', applyRoleUi);
        applyRoleUi();
    })();
</script>
@endsection
