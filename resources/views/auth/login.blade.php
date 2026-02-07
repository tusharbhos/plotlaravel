<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - PlotMGMT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }
        @keyframes pulse-slow { 0%,100% { opacity: 0.4; } 50% { opacity: 0.7; } }
        .float-1 { animation: float 6s ease-in-out infinite; }
        .float-2 { animation: float 8s ease-in-out infinite 1s; }
        .float-3 { animation: float 7s ease-in-out infinite 2s; }
        .pulse-slow { animation: pulse-slow 4s ease-in-out infinite; }
        .input-focus:focus { box-shadow: 0 0 0 3px rgba(99,102,241,0.3); }
        @keyframes slideUp { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
        .slide-up { animation: slideUp 0.6s ease; }
    </style>
</head>
<body class="min-h-screen flex">

<!-- LEFT: Visual Panel -->
<div class="hidden lg:flex lg:w-1/2 relative bg-gradient-to-br from-slate-900 via-indigo-900 to-purple-900 overflow-hidden">
    <!-- Decorative Shapes -->
    <div class="absolute inset-0 flex items-center justify-center">
        <div class="float-1 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl"></div>
        <div class="float-2 w-48 h-48 bg-purple-500/20 rounded-full blur-2xl absolute top-20 left-20"></div>
        <div class="float-3 w-56 h-56 bg-blue-500/15 rounded-full blur-3xl absolute bottom-20 right-20"></div>
    </div>
    <!-- Grid pattern overlay -->
    <div class="absolute inset-0 opacity-10" style="background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 50px 50px;"></div>

    <!-- Content -->
    <div class="relative z-10 flex flex-col justify-center items-center p-12 text-white w-full">
        <div class="mb-8">
            <div class="w-20 h-20 bg-indigo-500/30 backdrop-blur rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-map-marker-alt text-3xl text-indigo-300"></i>
            </div>
            <h1 class="text-4xl font-extrabold text-center">PlotMGMT</h1>
            <p class="text-indigo-200 text-center mt-2 text-lg">Plot Management System</p>
        </div>
        <div class="max-w-sm w-full space-y-4 mt-6">
            <div class="flex items-start gap-3 bg-white/5 backdrop-blur rounded-xl p-4 border border-white/10">
                <i class="fas fa-check-circle text-green-400 mt-0.5"></i>
                <div>
                    <div class="font-semibold text-sm">CRUD Operations</div>
                    <div class="text-indigo-200 text-xs">Create, Read, Update, Delete plots with ease</div>
                </div>
            </div>
            <div class="flex items-start gap-3 bg-white/5 backdrop-blur rounded-xl p-4 border border-white/10">
                <i class="fas fa-check-circle text-green-400 mt-0.5"></i>
                <div>
                    <div class="font-semibold text-sm">Polygon Mapping</div>
                    <div class="text-indigo-200 text-xs">Add multiple polygon points for each plot</div>
                </div>
            </div>
            <div class="flex items-start gap-3 bg-white/5 backdrop-blur rounded-xl p-4 border border-white/10">
                <i class="fas fa-check-circle text-green-400 mt-0.5"></i>
                <div>
                    <div class="font-semibold text-sm">Excel Import / Export</div>
                    <div class="text-indigo-200 text-xs">Bulk import plots from templates or export data</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- RIGHT: Login Form -->
<div class="w-full lg:w-1/2 flex items-center justify-center bg-gray-50 p-6">
    <div class="w-full max-w-md slide-up">
        <!-- Logo (Mobile) -->
        <div class="lg:hidden text-center mb-8">
            <div class="w-14 h-14 bg-indigo-600 rounded-xl flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-map-marker-alt text-xl text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">PlotMGMT</h1>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Welcome back</h2>
                <p class="text-gray-500 text-sm mt-1">Sign in to your account</p>
            </div>

            <!-- Flash Messages -->
            @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3 mb-4 flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3 mb-4 flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
            @endif

            <!-- Form -->
            <form method="POST" action="{{ route('login.submit') }}">
                @csrf
                <!-- Email -->
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email Address</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="email" name="email" value="{{ old('email') }}"
                            placeholder="you@example.com"
                            class="input-focus w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-indigo-400 bg-gray-50 transition {{ $errors->has('email') ? 'border-red-400 bg-red-50' : '' }}" required />
                    </div>
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @endError
                </div>

                <!-- Password -->
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="password" name="password" id="passwordField"
                            placeholder="••••••••"
                            class="input-focus w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-indigo-400 bg-gray-50 transition {{ $errors->has('password') ? 'border-red-400 bg-red-50' : '' }}" required />
                        <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" onclick="togglePassword('passwordField', this)">
                            <i class="fas fa-eye text-sm" id="eyeIcon"></i>
                        </button>
                    </div>
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @endError
                </div>

                <!-- Login Button -->
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg transition shadow-md hover:shadow-lg">
                    <i class="fas fa-sign-in-alt mr-2"></i> Sign In
                </button>
            </form>

            <!-- Divider -->
            <div class="flex items-center gap-3 my-5">
                <div class="flex-1 h-px bg-gray-200"></div>
                <span class="text-xs text-gray-400 font-medium">or</span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>

            <!-- Register Link -->
            <p class="text-center text-sm text-gray-500">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-indigo-600 font-semibold hover:underline ml-1">Sign Up</a>
            </p>

            <!-- Demo Credentials -->
            <div class="mt-5 bg-indigo-50 border border-indigo-100 rounded-lg p-3">
                <p class="text-xs text-indigo-600 font-semibold mb-1 flex items-center gap-1"><i class="fas fa-info-circle"></i> Demo Credentials</p>
                <p class="text-xs text-indigo-500">Admin: <span class="font-mono">admin@plotmgmt.com</span> / <span class="font-mono">admin123</span></p>
                <p class="text-xs text-indigo-500">User: <span class="font-mono">john@plotmgmt.com</span> / <span class="font-mono">john123</span></p>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId, btn) {
    const field = document.getElementById(fieldId);
    const icon = btn.querySelector('i');
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>
</body>
</html>