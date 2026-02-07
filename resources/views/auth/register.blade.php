<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register - PlotMGMT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }
        .float-1 { animation: float 6s ease-in-out infinite; }
        .float-2 { animation: float 8s ease-in-out infinite 1s; }
        .float-3 { animation: float 7s ease-in-out infinite 2s; }
        .input-focus:focus { box-shadow: 0 0 0 3px rgba(99,102,241,0.3); }
        @keyframes slideUp { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
        .slide-up { animation: slideUp 0.6s ease; }
        .strength-bar { transition: width 0.3s ease, background 0.3s ease; }
    </style>
</head>
<body class="min-h-screen flex">

<!-- LEFT: Visual Panel -->
<div class="hidden lg:flex lg:w-1/2 relative bg-gradient-to-br from-slate-900 via-purple-900 to-indigo-900 overflow-hidden">
    <div class="absolute inset-0 flex items-center justify-center">
        <div class="float-1 w-64 h-64 bg-purple-500/20 rounded-full blur-3xl"></div>
        <div class="float-2 w-48 h-48 bg-indigo-500/20 rounded-full blur-2xl absolute top-20 right-20"></div>
        <div class="float-3 w-56 h-56 bg-pink-500/15 rounded-full blur-3xl absolute bottom-20 left-20"></div>
    </div>
    <div class="absolute inset-0 opacity-10" style="background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 50px 50px;"></div>

    <div class="relative z-10 flex flex-col justify-center items-center p-12 text-white w-full">
        <div class="w-20 h-20 bg-purple-500/30 backdrop-blur rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-user-plus text-3xl text-purple-300"></i>
        </div>
        <h1 class="text-4xl font-extrabold text-center">Join PlotMGMT</h1>
        <p class="text-purple-200 text-center mt-2 text-lg">Create your account today</p>
        <div class="max-w-sm w-full mt-8 space-y-3">
            <div class="flex items-center gap-3 bg-white/5 backdrop-blur rounded-xl p-4 border border-white/10">
                <i class="fas fa-shield-alt text-yellow-400"></i>
                <div>
                    <div class="font-semibold text-sm">Secure & Safe</div>
                    <div class="text-purple-200 text-xs">Your data is encrypted & protected</div>
                </div>
            </div>
            <div class="flex items-center gap-3 bg-white/5 backdrop-blur rounded-xl p-4 border border-white/10">
                <i class="fas fa-bolt text-green-400"></i>
                <div>
                    <div class="font-semibold text-sm">Instant Access</div>
                    <div class="text-purple-200 text-xs">Get started in seconds</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- RIGHT: Register Form -->
<div class="w-full lg:w-1/2 flex items-center justify-center bg-gray-50 p-6 overflow-y-auto">
    <div class="w-full max-w-md slide-up py-8">
        <!-- Logo (Mobile) -->
        <div class="lg:hidden text-center mb-6">
            <div class="w-14 h-14 bg-indigo-600 rounded-xl flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-map-marker-alt text-xl text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">PlotMGMT</h1>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8">
            <div class="mb-5">
                <h2 class="text-2xl font-bold text-gray-800">Create Account</h2>
                <p class="text-gray-500 text-sm mt-1">Fill in the details below to register</p>
            </div>

            <!-- Flash Messages -->
            @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3 mb-4 flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
            @endif

            <form method="POST" action="{{ route('register.submit') }}">
                @csrf

                <!-- Name -->
                <div class="mb-3.5">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Full Name</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="text" name="name" value="{{ old('name') }}"
                            placeholder="John Doe"
                            class="input-focus w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-indigo-400 bg-gray-50 transition {{ $errors->has('name') ? 'border-red-400 bg-red-50' : '' }}" required />
                    </div>
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @endError
                </div>

                <!-- Phone -->
                <div class="mb-3.5">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Phone Number</label>
                    <div class="relative">
                        <i class="fas fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="tel" name="phone" value="{{ old('phone') }}"
                            placeholder="+91 9876543210"
                            class="input-focus w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-indigo-400 bg-gray-50 transition {{ $errors->has('phone') ? 'border-red-400 bg-red-50' : '' }}" required />
                    </div>
                    @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @endError
                </div>

                <!-- Email -->
                <div class="mb-3.5">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email Address</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="email" name="email" value="{{ old('email') }}"
                            placeholder="john@example.com"
                            class="input-focus w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-indigo-400 bg-gray-50 transition {{ $errors->has('email') ? 'border-red-400 bg-red-50' : '' }}" required />
                    </div>
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @endError
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="password" name="password" id="regPassword"
                            placeholder="Min 6 characters"
                            oninput="checkStrength(this.value)"
                            class="input-focus w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-indigo-400 bg-gray-50 transition {{ $errors->has('password') ? 'border-red-400 bg-red-50' : '' }}" required />
                        <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" onclick="togglePassword('regPassword', this)">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                    <!-- Strength Bar -->
                    <div class="mt-2 h-1 bg-gray-200 rounded-full overflow-hidden">
                        <div id="strengthBar" class="strength-bar h-full w-0 rounded-full"></div>
                    </div>
                    <p id="strengthText" class="text-xs text-gray-400 mt-0.5"></p>
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @endError
                </div>

                <!-- Confirm Password -->
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Confirm Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="password" name="re_password" id="rePassword"
                            placeholder="Re-enter password"
                            class="input-focus w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-indigo-400 bg-gray-50 transition {{ $errors->has('re_password') ? 'border-red-400 bg-red-50' : '' }}" required />
                        <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" onclick="togglePassword('rePassword', this)">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                    @error('re_password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @endError
                </div>

                <!-- Register Button -->
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg transition shadow-md hover:shadow-lg">
                    <i class="fas fa-user-plus mr-2"></i> Create Account
                </button>
            </form>

            <div class="flex items-center gap-3 my-5">
                <div class="flex-1 h-px bg-gray-200"></div>
                <span class="text-xs text-gray-400 font-medium">or</span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>

            <p class="text-center text-sm text-gray-500">
                Already have an account?
                <a href="{{ route('login') }}" class="text-indigo-600 font-semibold hover:underline ml-1">Sign In</a>
            </p>
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

function checkStrength(pw) {
    const bar = document.getElementById('strengthBar');
    const text = document.getElementById('strengthText');
    let score = 0;
    if (pw.length >= 6) score++;
    if (pw.length >= 8) score++;
    if (/[A-Z]/.test(pw)) score++;
    if (/[0-9]/.test(pw)) score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;

    const colors = ['', '#ef4444', '#f59e0b', '#3b82f6', '#10b981', '#10b981'];
    const labels = ['', 'Very Weak', 'Weak', 'Medium', 'Strong', 'Very Strong'];
    const widths = ['0%', '20%', '40%', '60%', '80%', '100%'];

    bar.style.width = widths[score];
    bar.style.background = colors[score];
    text.textContent = labels[score];
    text.style.color = colors[score];
}
</script>
</body>
</html>