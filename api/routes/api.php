<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\TimezoneController;
use App\Http\Controllers\Api\AbilityController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ClientController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rotas públicas da API
Route::get('/', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API AgendIA está funcionando!',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});

// Rotas de autenticação (públicas)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/google-login', [AuthController::class, 'googleLogin']);
});

// Rotas de registro (públicas)
Route::prefix('register')->group(function () {
    Route::post('/', [RegisterController::class, 'register']);
});

// Rotas de combos públicas (para registro e uso geral)
Route::prefix('combos')->group(function () {
    Route::get('/companies', [CompanyController::class, 'combo']);
    Route::get('/profiles', [ProfileController::class, 'combo']);
});

// Rotas protegidas por autenticação
Route::middleware('auth:sanctum')->group(function () {
    // Rotas de autenticação (protegidas)
    Route::get('/companies/available', [CompanyController::class, 'availableForAssociation']);

    // Rotas do sistema
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });


    // Rotas do sistema
    Route::group([], function () {

        // Rotas para gerenciar usuários
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->middleware('ability:users.index');
            Route::post('/', [UserController::class, 'store'])->middleware('ability:users.create');
            Route::post('/associate-companies', [UserController::class, 'associateCompanies'])->middleware('ability:users.associate_companies');
            Route::post('/update-main-company', [UserController::class, 'updateMainCompany']);
            Route::delete('/detach-company/{companyId}', [UserController::class, 'detachCompany'])->middleware('ability:users.detach_company');
            Route::get('/export', [UserController::class, 'export'])->middleware('ability:users.index');
            Route::get('/available-professionals', [UserController::class, 'availableProfessionals'])->middleware('ability:users.index');
            Route::get('/{user}', [UserController::class, 'show'])->middleware('ability:users.show');
            Route::put('/{user}', [UserController::class, 'update'])->middleware('ability:users.edit');
            Route::delete('/{user}', [UserController::class, 'destroy'])->middleware('ability:users.delete');
        });

        // Rotas para abilities
        Route::prefix('abilities')->group(function () {
            Route::get('/', [AbilityController::class, 'index'])->middleware('ability:abilities.index');
            Route::get('/by-profile/{profile}', [AbilityController::class, 'abilitiesByProfile'])->middleware('ability:abilities.index');
        });

        // Rotas para timezones
        Route::prefix('timezones')->group(function () {
            Route::get('/', [TimezoneController::class, 'index']);
            Route::get('/{timezone}', [TimezoneController::class, 'show']);
        });

        // Rotas para profiles
        Route::prefix('profiles')->group(function () {
            Route::get('/', [ProfileController::class, 'index'])->middleware('ability:profiles.index');
            Route::post('/', [ProfileController::class, 'store'])->middleware('ability:profiles.create');
            Route::get('/export', [ProfileController::class, 'export'])->middleware('ability:profiles.index');
            Route::get('/abilities/all', [ProfileController::class, 'abilities'])->middleware('ability:profiles.list_abilities');
            Route::get('/{profile}', [ProfileController::class, 'show'])->middleware('ability:profiles.show');
            Route::put('/{profile}', [ProfileController::class, 'update'])->middleware('ability:profiles.edit');
            Route::delete('/{profile}', [ProfileController::class, 'destroy'])->middleware('ability:profiles.delete');
            Route::patch('/{profile}/abilities', [ProfileController::class, 'updateAbilities'])->middleware('ability:profiles.update_abilities');
        });

        // Rotas para companies
        Route::prefix('companies')->group(function () {
            Route::get('/', [CompanyController::class, 'index'])->middleware('ability:companies.index');
            Route::post('/', [CompanyController::class, 'store'])->middleware('ability:companies.create');
            Route::get('/export', [CompanyController::class, 'export'])->middleware('ability:companies.index');
            Route::get('/{company}', [CompanyController::class, 'show'])->middleware('ability:companies.show');
            Route::put('/{company}', [CompanyController::class, 'update'])->middleware('ability:companies.edit');
            Route::delete('/{company}', [CompanyController::class, 'destroy'])->middleware('ability:companies.delete');
            Route::patch('/{company}/deactivate', [CompanyController::class, 'deactivate'])->middleware('ability:companies.edit');
            Route::patch('/{id}/activate', [CompanyController::class, 'activate'])->middleware('ability:companies.edit');
            Route::get('/{companyId}/professionals', [CompanyController::class, 'companyUsers'])->middleware('ability:companies.manage_professionals');
            Route::get('/{companyId}/bind-professional/export', [CompanyController::class, 'exportBindProfessional'])->middleware('ability:companies.manage_professionals');
            Route::post('/{companyId}/professionals/{userId}', [CompanyController::class, 'attachProfessional'])->middleware('ability:companies.attach_professional');
            Route::delete('/{companyId}/professionals/{userId}', [CompanyController::class, 'detachProfessional'])->middleware('ability:companies.detach_professional');
        });

        // Rotas para clientes (filtrados por empresa)
        Route::prefix('clients')->group(function () {
            Route::get('/', [ClientController::class, 'index'])->middleware('ability:clients.index');
            Route::post('/', [ClientController::class, 'store'])->middleware('ability:clients.create');
            Route::get('/export', [ClientController::class, 'export'])->middleware('ability:clients.index');
            Route::get('/{user}', [ClientController::class, 'show'])->middleware('ability:clients.show');
            Route::put('/{user}', [ClientController::class, 'update'])->middleware('ability:clients.edit');
            Route::delete('/{user}', [ClientController::class, 'destroy'])->middleware('ability:clients.delete');
        });
    });
});
