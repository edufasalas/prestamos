<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class TemplateController extends Controller
{
    // Mostrar la vista de envíos y plantillas (opcionalmente con un cliente preseleccionado)
    public function index($cliente_id = null)
    {
        $user = Auth::user();
        
        $templates = DB::table('templates')
            ->where('company_id', $user->company_id)
            ->get();
            
        $clientes = DB::table('clientes')
            ->when(!empty($user->company_id), function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })
            ->get();
    
        // 👇 OBTENEMOS LA CONFIGURACIÓN SMTP GUARDADA DE LA EMPRESA
        $smtp = DB::table('company_smtp_settings')
            ->where('company_id', $user->company_id)
            ->first();
    
        $clienteSeleccionado = $cliente_id ? DB::table('clientes')->where('id', $cliente_id)->first() : null;
    
        // 👇 AGREGAMOS '$smtp' AL COMPACT
        return view('admin.envios.index', compact('templates', 'clientes', 'clienteSeleccionado', 'smtp'));
    }

    // Guardar una nueva plantilla
    public function storeTemplate(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'titulo' => 'required|string|max:255',
            'asunto' => 'required|string|max:255',
            'cuerpo' => 'required|string',
        ]);

        DB::table('templates')->insert([
            'company_id' => $user->company_id,
            'titulo' => $request->titulo,
            'asunto' => $request->asunto,
            'cuerpo' => $request->cuerpo,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Plantilla creada correctamente.');
    }

    public function probarSmtp(Request $request)
    {
        $user = Auth::user();
        
        $smtp = DB::table('company_smtp_settings')
            ->where('company_id', $user->company_id)
            ->first();
    
        if (!$smtp) {
            return back()->with('error', 'Primero debes guardar la configuración SMTP antes de probarla.');
        }
    
        try {
            // Sobrescribimos la configuración de forma explícita
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => $smtp->mail_host,
                'mail.mailers.smtp.port' => (int) $smtp->mail_port,
                'mail.mailers.smtp.encryption' => strtolower($smtp->mail_encryption), // 👈 Forzamos minúsculas ('ssl' en lugar de 'SSL')
                'mail.mailers.smtp.username' => $smtp->mail_username,
                'mail.mailers.smtp.password' => $smtp->mail_password,
                'mail.from.address' => $smtp->mail_from_address,
                'mail.from.name' => $smtp->mail_from_name,
            ]);
    
            // 👇 LA CLAVE: Limpiamos la caché del transporte anterior
            Mail::purge('smtp');
    
            Mail::raw('¡Felicidades! Tu configuración SMTP en el CRM está funcionando correctamente.', function ($message) use ($user, $smtp) {
                $message->to($smtp->mail_from_address, $user->name ?? 'Usuario')
                        ->subject('Prueba de Configuración SMTP - CRM');
            });
    
        } catch (\Exception $e) {
            return back()->with('error', 'Fallo en la prueba SMTP: ' . $e->getMessage());
        }
    
        return back()->with('success', '¡Correo de prueba enviado con éxito a tu cuenta (' . $smtp->mail_from_address . ')!');
    }
    // Ejecutar el envío del correo usando el SMTP de la empresa y la plantilla
    public function enviar(Request $request)
    {
        $request->validate([
            'template_id' => 'required',
            'cliente_id' => 'required',
        ]);
    
        $user = Auth::user();
        
        $smtp = DB::table('company_smtp_settings')
            ->where('company_id', $user->company_id)
            ->first();
    
        if (!$smtp) {
            return back()->with('error', 'Debes configurar primero el SMTP de tu empresa.');
        }
    
        config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => $smtp->mail_host,
                'mail.mailers.smtp.port' => (int) $smtp->mail_port,
                'mail.mailers.smtp.encryption' => strtolower($smtp->mail_encryption), // 👈 Forzamos minúsculas ('ssl' en lugar de 'SSL')
                'mail.mailers.smtp.username' => $smtp->mail_username,
                'mail.mailers.smtp.password' => $smtp->mail_password,
                'mail.from.address' => $smtp->mail_from_address,
                'mail.from.name' => $smtp->mail_from_name,
            ]);
    
        // 👇 LA CLAVE AQUÍ TAMBIÉN
        Mail::purge('smtp');
    
        $template = DB::table('templates')->where('id', $request->template_id)->first();
        $cliente = DB::table('clientes')->where('id', $request->cliente_id)->first();
    
        if (!$template || !$cliente || empty($cliente->correo)) {
            return back()->with('error', 'Plantilla o cliente inválido / el cliente no tiene correo.');
        }
    
        $asunto = str_replace(['{nombre}', '{telefono}'], [$cliente->nombre, $cliente->telefono ?? ''], $template->asunto);
        $cuerpo = str_replace(['{nombre}', '{telefono}'], [$cliente->nombre, $cliente->telefono ?? ''], $template->cuerpo);
    
        try {
            // Cambiamos Mail::raw por Mail::html para que el navegador/correo interprete el diseño
            Mail::html($cuerpo, function ($message) use ($cliente, $asunto) {
                $message->to($cliente->correo, $cliente->nombre)
                        ->subject($asunto);
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Error al enviar: ' . $e->getMessage());
        }
    
        return back()->with('success', '¡Correo enviado exitosamente!');
    }
    
}
