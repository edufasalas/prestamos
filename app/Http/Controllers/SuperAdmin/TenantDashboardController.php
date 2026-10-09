<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\UserExtension;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TenantDashboardController extends Controller
{
    /**
     * Muestra el panel ejecutivo de la PBX Virtual para un Tenant específico o global.
     */
    public function show($id)
    {
        // 1. Buscamos la empresa con sus datos de telefonía que acabamos de crear (DID, CallerID, IVR, etc.)
        $company = Company::with(['users.extensions'])->findOrFail($id);

        return view('superadmin.tenants.dashboard', compact('company'));
    }

    /**
     * Consulta en tiempo real el estado de las extensiones de la empresa mediante la API de Issabel
     */
    public function getRealtimeStatus($id)
    {
        $company = Company::with('users.extensions')->findOrFail($id);

        // Extraer los números de extensión que le pertenecen a esta empresa
        $extensionNumbers = [];
        foreach ($company->users as $user) {
            foreach ($user->extensions as $ext) {
                $extensionNumbers[] = $ext->extension_number;
            }
        }

        try {
            // Petición HTTP al script API de Issabel (ajusta tu URL base según corresponda)
            $response = Http::timeout(5)->get('https://pbx.voiparkas.com/cdrweb/llamacrm/api_estado_pbx.php');

            if ($response->successful()) {
                $data = $response->json();
                $allExtensions = $data['extensions'] ?? [];

                // Cruzamos los datos de Issabel únicamente con las extensiones de esta empresa
                $companyExtensionsStatus = [];
                foreach ($extensionNumbers as $extNum) {
                    if (isset($allExtensions[$extNum])) {
                        $companyExtensionsStatus[$extNum] = $allExtensions[$extNum];
                    } else {
                        $companyExtensionsStatus[$extNum] = [
                            'extension' => $extNum,
                            'status'    => 'offline',
                            'raw_detail'=> 'Sin registro activo'
                        ];
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'company' => [
                        'name' => $company->name,
                        'incoming_did' => $company->incoming_did,
                        'max_concurrent_calls' => $company->max_concurrent_calls,
                        'has_ivr' => $company->has_ivr,
                    ],
                    'extensions' => $companyExtensionsStatus
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo comunicar con el motor de telefonía.'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}