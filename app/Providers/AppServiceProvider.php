<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Company;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */

    public function boot(): void
    {
        // En lugar de '*', limítalo a los layouts principales o vistas de autenticación donde realmente se usa
        View::composer(['layouts.*', 'auth.*'], function ($view) {
            $company = null;

            if (auth()->check()) {
                // Usar caché opcionalmente o confiar en la relación si ya está cargada
                $company = auth()->user()->company;
            }

            $view->with('globalCompany', $company);
        });
    }    

    public function enviar(Request $request)
    {
        $request->validate([
            'template_id' => 'required',
            'cliente_id' => 'required',
        ]);
    
        $user = Auth::user();
        
        // 1. Buscamos el SMTP exclusivo de la empresa del usuario logueado
        $smtp = DB::table('company_smtp_settings')
            ->where('company_id', $user->company_id)
            ->first();
    
        if (!$smtp) {
            return back()->with('error', 'Tu empresa no ha configurado un servidor SMTP propio.');
        }
    
        // 2. Configuramos dinámicamente el mailer de Laravel en caliente
        Config::set('mail.mailers.smtp.host', $smtp->mail_host);
        Config::set('mail.mailers.smtp.port', $smtp->mail_port);
        Config::set('mail.mailers.smtp.username', $smtp->mail_username);
        Config::set('mail.mailers.smtp.password', $smtp->mail_password);
        Config::set('mail.mailers.smtp.encryption', $smtp->mail_encryption);
        Config::set('mail.from.address', $smtp->mail_from_address);
        Config::set('mail.from.name', $smtp->mail_from_name);
    
        $template = DB::table('templates')->where('id', $request->template_id)->first();
        $cliente = DB::table('clientes')->where('id', $request->cliente_id)->first();
    
        if (!$template || !$cliente || empty($cliente->email)) {
            return back()->with('error', 'Plantilla o cliente inválido / sin correo.');
        }
    
        // 3. Reemplazo de variables
        $asunto = str_replace(['{nombre}', '{telefono}'], [$cliente->nombre, $cliente->telefono], $template->asunto);
        $cuerpo = str_replace(['{nombre}', '{telefono}'], [$cliente->nombre, $cliente->telefono], $template->cuerpo);
    
        // 4. Envío usando el SMTP de su empresa
        try {
            Mail::raw($cuerpo, function ($message) use ($cliente, $asunto) {
                $message->to($cliente->email, $cliente->nombre)
                        ->subject($asunto);
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Error al enviar el correo: Verifique las credenciales SMTP de su empresa.');
        }
    
        return back()->with('success', '¡Correo enviado exitosamente con el SMTP de tu empresa!');
    }    
}
