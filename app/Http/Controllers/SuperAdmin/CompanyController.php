<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CompanyController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Acceso denegado. Solo el Super Administrador puede ver esta sección.');
        }

        // Cargamos la empresa junto con sus usuarios para poder extraer el admin principal en la vista
        $companies = Company::with(['users' => function($query) {
            $query->where('role', 'admin')->limit(1);
        }])->withCount('users')->get();

        return view('superadmin.companies.index', compact('companies'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Acceso denegado.');
        }

        // Validamos usando exactamente el mismo campo que usa tu vista (minutes_to_add)
        $request->validate([
            'company_name'   => 'required|string|max:255',
            'status'         => 'required|in:active,suspended',
            'minutes_to_add' => 'nullable|integer', 
            'admin_name'     => 'required|string|max:255',
            'admin_email'    => 'required|email|max:255|unique:users,email',
            'admin_password' => 'required|string|min:8',
            'incoming_did'         => 'nullable|string|max:30',
            'outbound_callerid'    => 'nullable|string|max:30',
            'max_concurrent_calls' => 'nullable|integer|min:1',
            'has_ivr'              => 'nullable|boolean',            
        ]);

        $minutosIniciales = (int) $request->input('minutes_to_add', 0);
        if ($minutosIniciales < 0) {
            $minutosIniciales = 0;
        }

        try {
            DB::transaction(function () use ($request, $minutosIniciales) {
                // 1. Creamos la empresa usando el valor de minutes_to_add como el límite inicial
                $company = Company::create([
                    'name'                 => $request->company_name,
                    'status'               => $request->status,
                    'minutes_limit'        => $minutosIniciales,
                    'incoming_did'         => $request->incoming_did,
                    'outbound_callerid'    => $request->outbound_callerid,
                    'max_concurrent_calls' => $request->max_concurrent_calls ?? 5,
                    'has_ivr'              => $request->has($request->has_ivr) ? 1 : 0,
                    'primary_color'        => '#2052F7',
                ]);

                // 2. Si puso minutos iniciales, dejamos el registro en el log de minutos
                if ($minutosIniciales > 0) {
                    DB::table('company_minute_logs')->insert([
                        'company_id'    => $company->id,
                        'minutes_added' => $minutosIniciales,
                        'notes'         => 'Bolsa de minutos inicial',
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }

                // 3. Crear el administrador de esa empresa
                User::create([
                    'company_id' => $company->id,
                    'name'       => $request->admin_name,
                    'email'      => $request->admin_email,
                    'password'   => Hash::make($request->admin_password),
                    'role'       => 'admin', 
                    'status'     => 'active',
                ]);
            });

            return redirect()->route('superadmin.companies.index')
                ->with('success', '¡Empresa y administrador creados con éxito!');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Error al registrar: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, Company $company)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Acceso denegado.');
        }
    
        $request->validate([
            'company_name'   => 'required|string|max:255',
            'status'         => 'required|in:active,suspended',
            'minutes_to_add' => 'nullable|integer',
            'admin_name'     => 'required|string|max:255',
            'admin_email'    => 'required|email|max:255|unique:users,email,' . optional($company->users()->where('role', 'admin')->first())->id,
            'admin_password' => 'nullable|string|min:8', 
            'incoming_did'         => 'nullable|string|max:30',
            'outbound_callerid'    => 'nullable|string|max:30',
            'max_concurrent_calls' => 'nullable|integer|min:1',
            'has_ivr'              => 'nullable|boolean',            
        ]);
    
        $minutosASumar = (int) $request->input('minutes_to_add', 0);
        $nuevoLimite = $company->minutes_limit + $minutosASumar;
    
        if ($nuevoLimite < 0) {
            $nuevoLimite = 0;
        }
    
        DB::transaction(function () use ($company, $request, $nuevoLimite, $minutosASumar) {
            // 1. Actualizar datos de la empresa
            $company->update([
                'name'                 => $request->company_name,
                'status'               => $request->status,
                'minutes_limit'        => $nuevoLimite,
                'incoming_did'         => $request->incoming_did,
                'outbound_callerid'    => $request->outbound_callerid,
                'max_concurrent_calls' => $request->max_concurrent_calls ?? 5,
                'has_ivr'              => $request->has('has_ivr') ? 1 : 0,
            ]);
    
            // 2. Actualizar o buscar al administrador principal de la empresa
            $adminUser = $company->users()->where('role', 'admin')->first();
            if ($adminUser) {
                $adminData = [
                    'name'  => $request->admin_name,
                    'email' => $request->admin_email,
                ];
                // Si escribieron una nueva contraseña al editar, la actualizamos cifrada
                if ($request->filled('admin_password')) {
                    $adminData['password'] = Hash::make($request->admin_password);
                }
                $adminUser->update($adminData);
            }

            // 3. Log de minutos si aplica
            if ($minutosASumar != 0) {
                DB::table('company_minute_logs')->insert([
                    'company_id'    => $company->id,
                    'minutes_added' => $minutosASumar,
                    'notes'         => $minutosASumar > 0 ? 'Recarga de minutos' : 'Ajuste / descuento manual',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        });
    
        return redirect()->route('superadmin.companies.index')->with('success', 'Empresa y datos del administrador actualizados correctamente.');
    }
    
    public function destroy(Company $company)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Acceso denegado.');
        }

        DB::transaction(function () use ($company) {
            // Elimina los usuarios asociados a la empresa
            $company->users()->delete();

            // Elimina los registros de logs de minutos de la empresa
            DB::table('company_minute_logs')->where('company_id', $company->id)->delete();

            // Finalmente elimina la empresa
            $company->delete();
        });

        return redirect()->route('superadmin.companies.index')->with('success', 'Empresa y sus usuarios asociados eliminados correctamente.');
    }    
}