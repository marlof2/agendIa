<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\AbilityController;
use App\Http\Controllers\Api\ProfileController;

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

// Rotas protegidas por autenticação
Route::middleware('auth:sanctum')->group(function () {
    // Rotas de autenticação (protegidas)
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });

    // Rotas do sistema
    Route::group([], function () {


        Route::prefix('appointments')->group(function () {
            Route::get('/', function () {
                return response()->json([
                    'success' => true,
                    'message' => 'Lista de agendamentos',
                    'data' => []
                ]);
            })->middleware('ability:appointments.index');

            Route::post('/', function (Request $request) {
                return response()->json([
                    'success' => true,
                    'message' => 'Agendamento criado com sucesso',
                    'data' => $request->all()
                ], 201);
            })->middleware('ability:appointments.create');

            Route::put('/{appointment}', function (Request $request, $id) {
                return response()->json([
                    'success' => true,
                    'message' => 'Agendamento atualizado com sucesso',
                    'data' => $request->all()
                ]);
            })->middleware('ability:appointments.edit');

            Route::delete('/{appointment}', function ($id) {
                return response()->json([
                    'success' => true,
                    'message' => 'Agendamento deletado com sucesso'
                ]);
            })->middleware('ability:appointments.delete');
        });


        // Rotas para admin e secretária
        Route::middleware('ability:users.index')->group(function () {

            Route::prefix('dashboard')->group(function () {
                Route::get('/', function () {
                    return response()->json([
                        'success' => true,
                        'message' => 'Dashboard carregado com sucesso',
                        'data' => [
                            'stats' => [
                                'total_appointments' => 0,
                                'today_appointments' => 0,
                                'pending_appointments' => 0,
                                'total_clients' => 0,
                            ]
                        ]
                    ]);
                });
            });

            // Rotas para gerenciar empresas
            Route::prefix('companies')->group(function () {
                Route::get('/', [CompanyController::class, 'index']);
                Route::get('/all', [CompanyController::class, 'all']);
                Route::get('/{company}', [CompanyController::class, 'show']);
                Route::post('/{company}/deactivate', [CompanyController::class, 'deactivate']);
                Route::post('/{id}/activate', [CompanyController::class, 'activate']);
                Route::delete('/{company}', [CompanyController::class, 'destroy']);
            });

            // Rotas para gerenciar usuários (com abilities)
            Route::prefix('users')->group(function () {
                Route::get('/', function () {
                    return response()->json([
                        'success' => true,
                        'message' => 'Lista de usuários',
                        'data' => []
                    ]);
                })->middleware('ability:users.index');

                Route::post('/', function (Request $request) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Usuário criado com sucesso',
                        'data' => $request->all()
                    ], 201);
                })->middleware('ability:users.create');

                Route::get('/{user}', function ($id) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Usuário encontrado',
                        'data' => ['id' => $id]
                    ]);
                })->middleware('ability:users.index');

                Route::put('/{user}', function (Request $request, $id) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Usuário atualizado com sucesso',
                        'data' => $request->all()
                    ]);
                })->middleware('ability:users.edit');

                Route::delete('/{user}', function ($id) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Usuário deletado com sucesso'
                    ]);
                })->middleware('ability:users.delete');

                // Rotas para abilities do usuário
                Route::get('/{user}/abilities', [AbilityController::class, 'userAbilities']);
                Route::post('/{user}/check-ability', [AbilityController::class, 'checkAbility']);
            });

            // Rotas para abilities
            Route::prefix('abilities')->group(function () {
                Route::get('/', [AbilityController::class, 'index']);
                Route::get('/by-profile/{profile}', [AbilityController::class, 'abilitiesByProfile']);
            });

            // Rotas para profiles
            Route::prefix('profiles')->group(function () {
                Route::get('/', [ProfileController::class, 'index']);
                Route::post('/', [ProfileController::class, 'store'])->middleware('ability:profiles.create');
                Route::get('/{profile}', [ProfileController::class, 'show']);
                Route::put('/{profile}', [ProfileController::class, 'update'])->middleware('ability:profiles.edit');
                Route::delete('/{profile}', [ProfileController::class, 'destroy'])->middleware('ability:profiles.delete');
                Route::get('/abilities/all', [ProfileController::class, 'abilities']);
                Route::patch('/{profile}/abilities', [ProfileController::class, 'updateAbilities'])->middleware('ability:profiles.edit');
            });


            Route::prefix('clients')->group(function () {
                Route::get('/', function () {
                    return response()->json([
                        'success' => true,
                        'message' => 'Lista de clientes',
                        'data' => []
                    ]);
                });

                Route::post('/', function (Request $request) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Cliente criado com sucesso',
                        'data' => $request->all()
                    ], 201);
                });
            });
        });

        // Rotas para clientes
        Route::middleware('ability:appointments.index')->group(function () {
            Route::prefix('my-appointments')->group(function () {
                Route::get('/', function () {
                    return response()->json([
                        'success' => true,
                        'message' => 'Meus agendamentos',
                        'data' => []
                    ]);
                });
            });
        });

        // Rotas para todos os usuários autenticados
        Route::prefix('profile')->group(function () {
            Route::get('/', function (Request $request) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'user' => $request->user()
                    ]
                ]);
            });

            Route::put('/', function (Request $request) {
                return response()->json([
                    'success' => true,
                    'message' => 'Perfil atualizado com sucesso',
                    'data' => $request->all()
                ]);
            });
        });
    });
});
