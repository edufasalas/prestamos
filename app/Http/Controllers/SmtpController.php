<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SmtpController extends Controller
{
    // Mostrar la vista de configuración SMTP de la empresa
    public function index()
    {
        $user = Auth::user();
        
        $smtp = DB::table('company_smtp_settings')
            ->where('company_id', $user->company_id)
            ->first();

        return view('admin.smtp.index', compact('smtp'));
    }

    // Guardar o actualizar la configuración SMTP
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'mail_host' => 'required|string',
            'mail_port' => 'required|integer',
            'mail_username' => 'required|string',
            'mail_password' => 'required|string',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
        ]);

        DB::table('company_smtp_settings')->updateOrInsert(
            ['company_id' => $user->company_id],
            [
                'mail_host' => $request->mail_host,
                'mail_port' => $request->mail_port,
                'mail_username' => $request->mail_username,
                'mail_password' => $request->mail_password,
                'mail_encryption' => $request->mail_encryption ?? 'tls',
                'mail_from_address' => $request->mail_from_address,
                'mail_from_name' => $request->mail_from_name,
                'updated_at' => now(),
            ]
        );

        return back()->with('success', 'Configuración SMTP guardada correctamente.');
    }
}