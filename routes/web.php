<?php
/*
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';*/

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminEstudianteController;
use App\Http\Controllers\Admin\AdminMateriaController;
use App\Http\Controllers\StudentController;

Route::get('/', function () {
    return view('welcome');
});

// Grupo de rutas para administrador
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::resource('/admin/estudiantes', AdminEstudianteController::class);
    Route::resource('/admin/materias', AdminMateriaController::class);
});

// Grupo de rutas para estudiantes
Route::middleware(['auth', 'student'])->group(function () {
    Route::get('/dashboard', [StudentController::class, 'index'])->name('student.dashboard');
    Route::get('/materias', [StudentController::class, 'materias'])->name('student.materias');
});

