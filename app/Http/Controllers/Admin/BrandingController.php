<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BrandingController extends Controller
{
    public function index()
    {
        // Obtenemos la compañía del usuario autenticado actual y la nombramos globalCompany
        $globalCompany = auth()->user()->company;
        
        return view('admin.branding.index', compact('globalCompany'));
    }
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
        ]);

        // Obtenemos la compañía del usuario actual
        $company = auth()->user()->company;
        
        if (!$company) {
            return back()->withErrors(['error' => 'No se encontró una compañía asociada a tu usuario.']);
        }

        $company->name = $request->name;
        $company->primary_color = $request->primary_color ?? '#2052F7';
        
        if ($request->filled('secondary_color')) {
            $company->secondary_color = $request->secondary_color;
        }

        // Manejo de la subida del logo
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            
            $file->move(public_path('uploads/logos'), $filename);
            
            $company->logo_path = 'uploads/logos/' . $filename;
        }

        $company->save();

        return redirect()->route('admin.branding.index')
            ->with('success', '¡Branding actualizado con éxito!');
    }
}