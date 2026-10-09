<?php
namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LeadController extends Controller
{
    public function precreate()
    {
        return view('leads.precreate');
    }
    
    public function storeStep1(Request $request)
    {
        $request->validate([
            'referencia' => 'required|string|max:255',
        ]);
    
        // Guardar el dato temporalmente en la sesión
        session(['pre_referencia' => $request->referencia]);
    
        // Redirigir a la pantalla de contraseña que acabamos de crear
        return redirect()->route('leads.password');
    }   
    
    // Muestra la vista del password
    public function showPasswordForm()
    {
        return view('leads.password'); // Asegúrate de guardar la vista en resources/views/leads/password.blade.php
    }

    // Procesa el password ingresado
    public function verifyPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:4',
        ]);

        // Puedes guardar la contraseña temporalmente en sesión si la vas a usar después,
        // o realizar alguna validación de seguridad aquí mismo.
        session(['pre_password' => $request->password]);

        // Una vez validado y guardado, lo mandamos al formulario completo final (/solicitud)
        return redirect()->route('leads.create');
    }    
    // Mostrar el formulario público afuera
    public function create()
    {
        return view('leads.create');
    }

    // Vista del Dashboard para administradores (protegida con auth)
    public function index()
    {
        $leads = Lead::latest()->get();
        return view('admin.leads.index', compact('leads'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'curp' => 'required|string|max:255',
            'monto' => 'required|numeric',
            'plazo' => 'required|string|max:100',
            'correo' => 'required|email|max:255',
            'telefono' => 'required|string|max:50',
            'tipo_prestamo' => 'required|string|max:100',
            'ingreso' => 'required|string|max:100',
            'laboral' => 'required|string|max:100',
            'destino' => 'required|string|max:100',
            'contacto' => 'required|string|max:100',
            'horario' => 'required|string|max:100',
        ]);
    
        $validated['ip'] = $request->ip();
        $lead = Lead::create($validated);
    
        // Armamos el texto del mensaje codificado para URL
        $mensaje = "🚨 *SIMULADOR / NUEVO LEAD* 🚨%0A%0A" .
                   "CURP: {$lead->curp}%0A" .
                   "MONTO: \${$lead->monto}%0A" .
                   "PLAZO: {$lead->plazo}%0A" .
                   "CORREO: {$lead->correo}%0A" .
                   "TELÉFONO: {$lead->telefono}%0A" .
                   "TIPO PRÉSTAMO: {$lead->tipo_prestamo}%0A" .
                   "INGRESO: {$lead->ingreso}%0A" .
                   "LABORAL: {$lead->laboral}%0A" .
                   "DESTINO: {$lead->destino}%0A" .
                   "CONTACTO: {$lead->contacto}%0A" .
                   "HORARIO: {$lead->horario}%0A" .
                   "IP: {$lead->ip}";
    
        // Aquí colocas el número de WhatsApp a donde quieres que llegue la info (ej: tu número de negocio con código de país, sin el +)
        $numeroDestino = "5213313734186"; // Reemplázalo por tu número
    
        $whatsappUrl = "https://wa.me/{$numeroDestino}?text=" . $mensaje;
    
        // Retornamos con una sesión que contenga el enlace de WhatsApp
        return redirect()->back()->with([
            'success' => '¡Solicitud registrada con éxito!',
            'whatsapp_url' => $whatsappUrl
        ]);
    }
    public function metrics()
    {
        // Obtener todos los leads
        $leads = Lead::all();
    
        // Métricas generales
        $totalLeads = $leads->count();
        $montoTotal = $leads->sum('monto');
        $montoPromedio = $totalLeads > 0 ? $leads->avg('monto') : 0;
        $leadsHoy = $leads->where('created_at', '>=', today())->count();
    
        // Agrupaciones para los gráficos
        // 1. Por Tipo de Préstamo
        $porTipo = $leads->groupBy('tipo_prestamo')->map->count();
        
        // 2. Por Destino del Crédito
        $porDestino = $leads->groupBy('destino')->map->count();
    
        // 3. Por Ingreso
        $porIngreso = $leads->groupBy('ingreso')->map->count();
    
        return view('admin.leads.metrics', compact(
            'totalLeads', 
            'montoTotal', 
            'montoPromedio', 
            'leadsHoy', 
            'porTipo', 
            'porDestino', 
            'porIngreso'
        ));
    }    
}


