@extends('layouts.app')

@section('title', 'Survey Solutions - Headquarters Login')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card login-card">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h4 class="mb-0">
                        <i class="fas fa-key me-2"></i>
                        API User Login
                    </h4>
                    <small>API User account required</small>
                </div>
                <div class="card-body p-4">
                    @if(session('error'))
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('login') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="headquarters_url" class="form-label">
                                <i class="fas fa-server me-1"></i>
                                Headquarters URL
                            </label>
                            <input type="url" 
                                   class="form-control @error('headquarters_url') is-invalid @enderror" 
                                   id="headquarters_url" 
                                   name="headquarters_url" 
                                   placeholder="http://localhost:9700 or http://localhost:9700/lpsh" 
                                   value="{{ old('headquarters_url', 'http://localhost:9700') }}"
                                   required>
                            <div class="form-text">
                                Tip: If your HQ uses workspaces, include the workspace in the URL (e.g., http://localhost:9700/lpsh), or enter it below.
                            </div>
                            @error('headquarters_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="workspace" class="form-label">
                                <i class="fas fa-folder-tree me-1"></i>
                                Workspace (optional)
                            </label>
                            <input type="text" 
                                   class="form-control @error('workspace') is-invalid @enderror" 
                                   id="workspace" 
                                   name="workspace" 
                                   placeholder="primary or lpsh" 
                                   value="{{ old('workspace') }}">
                            <div class="form-text">
                                If provided, the app will connect to {URL}/{workspace} (e.g., http://localhost:9700/lpsh).
                            </div>
                            @error('workspace')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label">
                                <i class="fas fa-user me-1"></i>
                                API Username
                            </label>
                            <input type="text" 
                                   class="form-control @error('username') is-invalid @enderror" 
                                   id="username" 
                                   name="username" 
                                   placeholder="DemoObs" 
                                   value="{{ old('username', 'DemoObs') }}"
                                   required>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-1"></i>
                                API Password
                            </label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Enter your password"
                                   value="password123"
                                   required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Connect to Headquarters
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection