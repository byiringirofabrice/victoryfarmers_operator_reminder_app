<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Victory Farmers Operator Reminder App</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .fade-in {
            opacity: 0;
            animation: fadeIn 1.5s ease-in-out forwards;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }
    </style>
</head>
<body class="bg-gradient-to-tr from-green-100 to-green-300 text-gray-800 antialiased">
    

    <div class="min-h-screen flex flex-col items-center justify-center px-4 fade-in text-center">
        
        <h1 class="text-2xl sm:text-4xl md:text-5xl font-extrabold mb-4">
            {{ __('welcome to Victory farmers operators reminder app') }}
        </h1>

        <p class="text-base sm:text-lg md:text-xl text-gray-700 mb-10 max-w-xl">
            
        </p>

        <div class="flex flex-col sm:flex-row gap-4">
            <a href="{{ route('login') }}" class="px-6 py-2 bg-green-700 text-white rounded-lg hover:bg-green-800 transition">
                {{ __('Login') }}
            </a>

            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="px-6 py-2 bg-white border border-green-600 text-green-600 rounded-lg hover:bg-green-50 transition">
                    {{ __('Register') }}
                </a>
            @endif
        </div>
    </div>
</body>
</html>
