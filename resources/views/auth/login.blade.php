<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - IDC Portal</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .gradient-bg {
            background: radial-gradient(circle at 10% 20%, rgba(37, 99, 235, 0.15) 0%, rgba(243, 244, 246, 0.5) 90%);
        }
        .hero-pattern {
            background-color: #0f172a;
            background-image: radial-gradient(rgba(59, 130, 246, 0.25) 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-900 flex items-center justify-center p-4 selection:bg-blue-500 selection:text-white">

    <div class="w-full max-w-5xl bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-100 dark:border-slate-800 overflow-hidden grid lg:grid-cols-12 min-h-[640px]">
        
        <!-- Left Side: Brand Visual (Desktop) -->
        <div class="hidden lg:flex lg:col-span-5 hero-pattern p-10 flex-col justify-between relative overflow-hidden border-r border-slate-800">
            <!-- Ambient Glow -->
            <div class="absolute -top-24 -left-24 w-80 h-80 bg-blue-600/30 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-80 h-80 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Top Brand -->
            <div class="relative z-10">
                <div class="inline-flex items-center gap-3 bg-white/10 backdrop-blur-md px-4 py-2 rounded-2xl border border-white/10">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                        <i class="fa-solid fa-cube text-lg"></i>
                    </div>
                    <div>
                        <span class="text-white font-extrabold text-lg tracking-wider">IDC</span>
                        <span class="text-blue-400 text-xs font-semibold uppercase tracking-widest block -mt-1">PORTAL</span>
                    </div>
                </div>
            </div>

            <!-- Middle Highlights -->
            <div class="relative z-10 my-auto py-8">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-400/20 text-blue-400 text-xs font-semibold mb-4">
                    <span class="w-2 h-2 rounded-full bg-blue-400 animate-ping"></span>
                    Hệ thống quản trị tập trung
                </div>
                <h1 class="text-3xl font-bold text-white tracking-tight leading-tight">
                    Nền tảng Quản trị & Điều hành <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-300">IDC Tool</span>
                </h1>
                <p class="text-slate-400 text-sm mt-3 leading-relaxed">
                    Đăng nhập để quản lý người dùng, theo dõi số liệu thống kê thời gian thực và cấu hình các công cụ tự động hóa.
                </p>

                <!-- Features Badges -->
                <div class="mt-8 space-y-3">
                    <div class="flex items-center gap-3 text-slate-300 text-xs font-medium">
                        <div class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                            <i class="fa-solid fa-shield-check text-xs"></i>
                        </div>
                        <span>Bảo mật 2 lớp & Mã hóa mật khẩu chuẩn Laravel</span>
                    </div>
                    <div class="flex items-center gap-3 text-slate-300 text-xs font-medium">
                        <div class="w-6 h-6 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center">
                            <i class="fa-solid fa-gauge-high text-xs"></i>
                        </div>
                        <span>Hiệu suất tối ưu, xử lý nhanh chóng</span>
                    </div>
                </div>
            </div>

            <!-- Bottom Footer info -->
            <div class="relative z-10 text-xs text-slate-400 flex items-center justify-between pt-6 border-t border-slate-800">
                <span>© 2026 IDC Portal v1.0</span>
                <span class="flex items-center gap-1.5 text-emerald-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Server Online
                </span>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="lg:col-span-7 bg-white dark:bg-slate-900 p-8 sm:p-12 lg:p-14 flex flex-col justify-center">
            
            <div class="max-w-md w-full mx-auto">
                
                <!-- Mobile brand icon -->
                <div class="lg:hidden flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-lg">
                        <i class="fa-solid fa-cube text-lg"></i>
                    </div>
                    <div>
                        <span class="font-extrabold text-xl text-slate-800 dark:text-white">IDC PORTAL</span>
                    </div>
                </div>

                <!-- Header Title -->
                <div class="mb-8">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        Chào mừng bạn trở lại! 👋
                    </h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-2">
                        Vui lòng nhập tài khoản quản trị để truy cập trang điều hành.
                    </p>
                </div>

                <!-- Flash Status / Errors -->
                @if (session('info'))
                    <div class="mb-5 p-4 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900/60 text-blue-800 dark:text-blue-300 text-sm flex items-center gap-3">
                        <i class="fa-solid fa-circle-info text-blue-600 dark:text-blue-400"></i>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 text-rose-800 dark:text-rose-300 text-sm flex items-start gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-rose-500 mt-0.5"></i>
                        <div>
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Demo Credentials Quick-Fill Pill -->
                <div class="mb-6 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                            <i class="fa-solid fa-key text-xs"></i>
                        </div>
                        <div class="text-xs">
                            <span class="text-slate-500 dark:text-slate-400">Tài khoản mẫu: </span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">admin@idc.vn</span> | <span class="font-semibold text-slate-800 dark:text-slate-200">admin123</span>
                        </div>
                    </div>
                    <button type="button" onclick="fillDemo()" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                        Điền nhanh
                    </button>
                </div>

                <!-- Form -->
                <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                    @csrf

                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                            Địa chỉ Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-regular fa-envelope"></i>
                            </div>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="{{ old('email') }}"
                                required 
                                autofocus
                                placeholder="ten@idc.vn"
                                class="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition"
                            >
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                Mật khẩu
                            </label>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required 
                                placeholder="••••••••"
                                class="w-full pl-11 pr-11 py-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition"
                            >
                            <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">
                                <i id="eyeIcon" class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember me -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500 dark:bg-slate-800 dark:border-slate-700">
                            <span class="text-xs text-slate-600 dark:text-slate-400 font-medium">Ghi nhớ đăng nhập</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-sm shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 focus:ring-4 focus:ring-blue-500/20 transition-all flex items-center justify-center gap-2"
                    >
                        <span>Đăng nhập hệ thống</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>

                    <div class="pt-2 text-center">
                        <a href="{{ route('home') }}" class="text-xs text-slate-500 hover:text-blue-600 transition inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-arrow-left text-[10px]"></i>
                            <span>Quay về Trang chủ BĐS</span>
                        </a>
                    </div>
                </form>

            </div>

        </div>

    </div>

    <!-- Script to toggle password and fill demo -->
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }

        function fillDemo() {
            document.getElementById('email').value = 'admin@idc.vn';
            document.getElementById('password').value = 'admin123';
        }
    </script>
</body>
</html>
