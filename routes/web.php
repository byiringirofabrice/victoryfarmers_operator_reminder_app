<?php

use App\Http\Controllers\OperatorController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PushSubscriptionController;
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/login', function () {
    return view('auth.login');
})->name('login')->middleware('guest');

Route::post('/logout', function () {
    Auth::guard('web')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login')->with('status', 'Logged out successfully.');
})->name('logout');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:operator'])->group(function () {
    Route::get('/operator', [OperatorController::class, 'index'])->name('operator.index');
    Route::get('/operator/manage', [OperatorController::class, 'manage'])->name('operator.manage');
});


Route::post('/subscribe', [PushSubscriptionController::class, 'subscribe'])->middleware('auth');
Route::post('/unsubscribe', [PushSubscriptionController::class, 'unsubscribe'])->middleware('auth');


// Route::post('/subscribe', function (Request $request) {
//     $user = auth()->user();
//     $user->updatePushSubscription(
//         $request->input('endpoint'),
//         $request->input('keys.p256dh'),
//         $request->input('keys.auth'),
//         $request->input('contentEncoding')
//     );
//     return response()->json(['success' => true]);
// })->middleware('auth')->name('subscribe');
Route::get('/generate-camera-template', function() {
    $headers = [
        'Country', 
        'Control Room',
        'Branch',
        'Site',
        'Camera Name',
        'Camera Type (fixed/ptz)',
        'Is Priority (1/0)',
        'Sort Order',
        'Is Active (1/0)',
        'Is Online (1/0)'
    ];
    
    $sampleData = [
        implode(',', $headers),
        'UAE,Dubai Central,Mall,Site A,Entrance Cam,fixed,1,1,1,1',
        'KSA,Riyadh Main,Central,Site B,Parking Cam,ptz,0,2,1,0'
    ];

    $content = implode("\n", $sampleData);
    
    return response($content)
        ->header('Content-Type', 'text/csv')
        ->header('Content-Disposition', 'attachment; filename="cameras_import_template.csv"');
});

require __DIR__.'/auth.php';