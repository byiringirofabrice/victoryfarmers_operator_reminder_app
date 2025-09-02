<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Operator Dashboard - VictoryFarmers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1),
                0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .pulse-dot {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .notification-badge {
            animation: bounce 1s infinite;
        }
        @keyframes bounce {
            0%,20%,53%,80%,100% { transform: translateY(0); }
            40%,43% { transform: translateY(-10px); }
            70% { transform: translateY(-5px); }
            90% { transform: translateY(-2px); }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

<nav class="bg-gradient-to-r from-indigo-600 to-purple-700 shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center space-x-4">
                <i class="fas fa-seedling text-white text-2xl"></i>
                <div>
                    <h1 class="text-white text-xl font-bold">VictoryFarmers</h1>
                    <p class="text-blue-200 text-sm">Operator Control Center</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <i class="fas fa-bell text-white text-lg"></i>
                    <span
                        class="notification-badge absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"
                    >3</span>
                </div>
                <div class="flex items-center space-x-2 text-white">
                    <i class="fas fa-user-circle text-lg"></i>
                    <span class="text-sm font-medium">Operator</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center space-x-2 text-white hover:text-red-400">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Welcome -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">
                    Welcome to Control Center
                </h2>
                <p class="text-gray-600">
                    Monitor farm operations and receive real-time notifications from your selected region.
                </p>
            </div>
            <div class="hidden md:block">
                <div class="bg-gradient-to-r from-green-400 to-blue-500 rounded-full p-4">
                    <i class="fas fa-tractor text-white text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Control Rooms -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <div class="mb-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-2 flex items-center">
                <i class="fas fa-satellite-dish text-blue-500 mr-3"></i>
                Select Control Room
            </h3>
            <p class="text-gray-600">
                Choose your operational region to receive targeted notifications and updates.
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            @foreach ($controlRooms as $room)
                <div
                    class="card-hover rounded-xl p-6 border-2 cursor-pointer"
                    style="background: {{ $room->bg_gradient }}; border-color: {{ $room->border_color }};"
                    onclick="subscribeToRoom('{{ $room->id }}')"
                >
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="rounded-full p-3" style="background-color: {{ $room->icon_bg_color }};">
                                <i class="fas {{ $room->icon_class }} text-white text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900">
                                    {{ $room->name }}
                                </h4>
                                <p class="text-sm text-gray-600">
                                    {{ $room->description }}
                                </p>
                            </div>
                        </div>
                        <div class="pulse-dot bg-green-500 rounded-full h-3 w-3"></div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">
                                {{ $room->active_farms_count }}
                            </div>
                            <div class="text-xs text-gray-600">Active Farms</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">
                                {{ $room->alerts_today_count }}
                            </div>
                            <div class="text-xs text-gray-600">Alerts Today</div>
                        </div>
                    </div>

                    <button
                        class="w-full hover:bg-opacity-90 text-white py-2 px-4 rounded-lg transition-colors duration-200 font-medium"
                        style="background-color: {{ $room->button_color }}"
                    >
                        <i class="fas fa-bell mr-2"></i>Subscribe to Notifications
                    </button>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Toast -->
<div
    id="toast"
    class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 z-50"
>
    <div class="flex items-center space-x-2">
        <i class="fas fa-check-circle"></i>
        <span id="toast-message">Subscribed successfully!</span>
    </div>
</div>

<script>
    const publicKey = "{{ env('VAPID_PUBLIC_KEY') }}";

    async function subscribeToRoom(controlRoomId) {
        const toast = document.getElementById('toast');
        const message = document.getElementById('toast-message');

        message.textContent = `Subscribing to notifications...`;
        toast.classList.remove('translate-x-full');

        // Remove ring highlight from all cards
        const cards = document.querySelectorAll('.card-hover');
        cards.forEach(card => card.classList.remove('ring-2', 'ring-blue-500', 'ring-green-500'));

        // Highlight clicked card
        const clickedCard = Array.from(cards).find(card => card.getAttribute('onclick').includes(controlRoomId));
        if (clickedCard) {
            clickedCard.classList.add('ring-2');
        }

        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            alert('Push notifications are not supported in your browser.');
            return;
        }

        try {
            const registration = await navigator.serviceWorker.register('/service-worker.js');
            const permission = await Notification.requestPermission();

            if (permission !== 'granted') {
                alert('Notification permission denied');
                return;
            }

            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(publicKey)
            });

            // Send full subscription object + control_room_id to backend
            await fetch('/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    control_room_id: controlRoomId,
                    subscription: subscription.toJSON ? subscription.toJSON() : subscription
                })
            });

            message.textContent = `Subscribed to notifications!`;
            setTimeout(() => {
                toast.classList.add('translate-x-full');
            }, 3000);
        } catch (err) {
            alert('Subscription failed: ' + err.message);
            toast.classList.add('translate-x-full');
        }
    }

    // Helper function to convert VAPID key
    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }
</script>

</body>
</html>
