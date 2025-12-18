<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Survey Solutions')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .survey-solutions-header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            padding: 1rem 0;
        }
        .login-card {
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-radius: 15px;
        }
        .dashboard-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .dashboard-card:hover {
            transform: translateY(-2px);
        }
        .sidebar {
            background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: calc(100vh - 140px);
        }
        .nav-link.active {
            background-color: #0d6efd;
            color: white !important;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="survey-solutions-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 class="mb-0">
                        <i class="fas fa-poll me-2"></i>
                        Survey Solutions
                    </h3>
                    <small>Headquarters Management Interface</small>
                </div>
                @if(session('headquarters_url'))
                <div class="col-md-6 text-end">
                    <span class="me-3">
                        <i class="fas fa-server me-1"></i>
                        Connected to: {{ session('headquarters_url') }}
                    </span>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-sign-out-alt me-1"></i>
                            Logout
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-light text-center py-3 mt-5">
        <div class="container">
            <small class="text-muted">
                Survey Solutions API System &copy; {{ date('Y') }} | 
                <a href="https://github.com" target="_blank">GitHub</a> |
                <a href="/api/v1" target="_blank">API Documentation</a>
            </small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>