<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Agent\SoftphoneController;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Agent\UserExtensionController;
use App\Http\Controllers\Agent\CdrController;
use App\Http\Controllers\Agent\DashboardController;
use App\Http\Controllers\Agent\UserController;
use App\Http\Controllers\Admin\BrandingController;
use App\Http\Controllers\Agent\ProfileController;
use App\Http\Controllers\Agent\Fop2Controller;
use App\Http\Controllers\SuperAdmin\CompanyController;
use App\Http\Controllers\Admin\ExtensionController;
use App\Http\Controllers\Admin\CompanyPbxController;
use App\Http\Controllers\Agent\VoipController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\SmtpController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\LeadController;






// 1. Rutas públicas (Afuera, sin requerir login)
Route::get('/solicita', [LeadController::class, 'precreate'])->name('leads.precreate');
Route::post('/solicita', [LeadController::class, 'storeStep1'])->name('leads.store.step1');

// 2. Rutas del segundo paso (Contraseña / Verificación)
Route::get('/solicita/seguridad', [LeadController::class, 'showPasswordForm'])->name('leads.password');
Route::post('/solicita/seguridad', [LeadController::class, 'verifyPassword'])->name('leads.verify-password');

// 3. Formulario completo final y guardado
Route::get('/solicitud', [LeadController::class, 'create'])->name('leads.create');
Route::post('/solicitud', [LeadController::class, 'store'])->name('leads.store');

// 2. Rutas protegidas (Dashboard dentro del panel con tu login)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard/leads', [LeadController::class, 'index'])->name('admin.leads.index');
    // NUEVA RUTA PARA MÉTRICAS
    Route::get('/dashboard/leads/metrics', [LeadController::class, 'metrics'])->name('admin.leads.metrics');
});

//CLICKTOCALL
Route::post('/agent/cliente/{id}/click-to-call', [ClienteController::class, 'triggerClickToCall'])->name('agent.cliente.clickToCall');

// Colócala aquí arriba para que Laravel la lea de inmediato
Route::get('messages/incoming', [ChatController::class, 'storeIncoming']);
Route::get('chat/history', [ChatController::class, 'getHistory']);
Route::post('chat/send', [ChatController::class, 'sendOutgoing']);
Route::get('chat/contacts', [ChatController::class, 'getContacts']);
Route::get('chat/history/{extension}', [ChatController::class, 'getHistory']);
Route::get('chat/check-unread', [ChatController::class, 'checkUnread']);
//Route::get('chat/mark-read', [ChatController::class, 'markAsRead']);

// Grupo de rutas exclusivo para el Super Administrador
Route::middleware(['auth'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
    Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
    Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
    Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');
});

// Redirigir la raíz al login
Route::get('/', function () {
    return redirect()->route('login');
});

// Rutas de Autenticación (Invitados)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});


    
// Ruta a la que llamará tu servidor remoto (cURL) para validar el token
Route::get('/voip/resolver-token', [SoftphoneController::class, 'resolverToken'])->name('voip.resolverToken');
//FUERA DE AUTH PARA MENSAJES
Route::post('/voip/recibir-mensaje', [SoftphoneController::class, 'recibirMensaje']);
    
// Rutas Protegidas (Requieren sesión activa)
Route::middleware(['auth', \App\Http\Middleware\CheckCompanyStatus::class])->group(function () {
    // Todas tus rutas protegidas de la app van aquí


    // Ruta para procesar el formulario de creación de campaña y subida del CSV
    Route::get('/agent/campaigns', [CampaignController::class, 'index'])->name('agent.campaigns.index');
    Route::post('/campaigns/store', [CampaignController::class, 'store'])->name('campaigns.store');
    Route::post('/campaigns/{id}/start', [CampaignController::class, 'start'])->name('campaigns.start');
    Route::post('/campaigns/{id}/stop', [CampaignController::class, 'stop'])->name('campaigns.stop');
    
    // Si necesitas la vista o el formulario en el futuro, puedes agregar su respectiva ruta GET aquí:
    // Route::get('/campaigns/create', [CampaignController::class, 'create'])->name('campaigns.create');
    
    // Clientes
    Route::get('/agent/cliente', [ClienteController::class, 'index'])->name('agent.cliente.index');
    Route::get('/agent/cliente/ficha/{telefono}', [ClienteController::class, 'showByTelefono'])->name('agent.cliente.ficha');
    Route::put('/agent/cliente/{id}', [ClienteController::class, 'update'])->name('agent.cliente.update');
    Route::post('/agent/cliente/{id}/nota', [ClienteController::class, 'storeNota'])->name('agent.cliente.nota.store');
    Route::get('/agent/cliente/exportar', [ClienteController::class, 'export'])->name('agent.cliente.export');
    Route::post('/agent/cliente/importar', [ClienteController::class, 'import'])->name('agent.cliente.import');
    Route::post('/cliente/store', [ClienteController::class, 'store'])->name('agent.cliente.store');    

    // Ruta que genera el token temporal y devuelve la URL del iframe
    Route::get('/voip/marcador-libre', [SoftphoneController::class, 'marcadorLibre'])->name('voip.marcadorLibre');
    
    // Marcador ClickTocall
    Route::get('/agent/softphone/token', [SoftphoneController::class, 'marcadorLibre'])->name('agent.softphone.token');


    //Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    // Dashboard principal
    Route::get('/dashboard', [SoftphoneController::class, 'dashboard'])->name('dashboard');
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');    

    // Rutas agrupadas con el prefijo y nombre 'agent.'
    Route::prefix('agent')->name('agent.')->group(function () {
        
        // Extensiones SIP
        Route::get('/extensions', [UserExtensionController::class, 'index'])->name('extensions.index');
        Route::post('/extensions', [UserExtensionController::class, 'store'])->name('extensions.store');
        
        // Historial CDR
        Route::get('/cdr', [CdrController::class, 'index'])->name('cdr.index');

        // --- NUEVAS RUTAS AGREGADAS ---
        Route::get('/directory', [CdrController::class, 'directory'])->name('directory.index');

        // Métricas & Reportes
        //Route::view('/reports', 'agent.reports.index')->name('reports.index');
        // Métricas & Reportes (Cambiado a Controlador)
        Route::get('/reports', [CdrController::class, 'reports'])->name('reports.index');

        // Audio Config
        Route::view('/audio', 'agent.audio.index')->name('audio.index');
        
        
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');      
        
        // === RUTA DE ACTUALIZACIÓN (POST DIRECTO) ===
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        // === RUTA DE ELIMINACION (POST DIRECTO) ===
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
      
        
        // Rutas de Perfil del Agente
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        
        //ruta Fop2
        Route::get('/fop2', [Fop2Controller::class, 'index'])->name('fop2.index');
        
    });
    
    // === RUTAS DE ADMIN / BRANDING FUERA DEL PREFIX AGENT ===
    Route::prefix('admin/branding')->name('admin.branding.')->group(function () {
        Route::get('/', [BrandingController::class, 'index'])->name('index');
        Route::put('/', [BrandingController::class, 'update'])->name('update');
    });    
    Route::prefix('admin')->middleware(['auth'])->group(function () {
        Route::get('/extensions/create', [ExtensionController::class, 'create'])->name('admin.extensions.create');
        Route::post('/extensions/store', [ExtensionController::class, 'store'])->name('admin.extensions.store');
        Route::post('/extensions/reload', [ExtensionController::class, 'reload'])->name('admin.extensions.reload');

        Route::get('/smtp', [SmtpController::class, 'index'])->name('admin.smtp.index');
        Route::post('/smtp', [SmtpController::class, 'update'])->name('admin.smtp.update');
        
        Route::get('/envios/{cliente_id?}', [TemplateController::class, 'index'])->name('admin.envios.index');
        Route::post('/envios/plantilla', [TemplateController::class, 'storeTemplate'])->name('admin.envios.storeTemplate');
        Route::post('/envios/enviar', [TemplateController::class, 'enviar'])->name('admin.envios.enviar');       
        Route::post('/smtp/probar', [TemplateController::class, 'probarSmtp'])->name('admin.smtp.probar');
    });    
    // Agrupamos el prefijo pbx y le asignamos el name 'admin.pbx.' para que route('admin.pbx.realtime-status') funcione perfecto
    Route::prefix('admin/pbx')->middleware(['auth'])->name('admin.pbx.')->group(function () {
        Route::get('/companypbx', [CompanyPbxController::class, 'index'])->name('companypbx');
        Route::get('/realtime-status', [CompanyPbxController::class, 'getRealtimeStatus'])->name('realtime-status');
        Route::post('/control-action', [CompanyPbxController::class, 'controlAction'])->name('control-action'); // <-- Nueva ruta agregada de forma limpia
        
        // --- NUEVAS RUTAS PARA LA VISTA DE CREACIÓN / CONFIGURACIÓN PBX ---
        Route::get('/creacionpbx', [CompanyPbxController::class, 'creacion'])->name('creacionpbx'); // Si manejas la vista por controlador
        Route::post('/store-ivrdid', [CompanyPbxController::class, 'storeIvrDid'])->name('store-ivrdid');
    });

});