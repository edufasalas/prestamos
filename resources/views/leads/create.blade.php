<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Simulador de Crédito</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-8">

    <div class="max-w-2xl w-full bg-white p-5 sm:p-8 rounded-2xl border border-slate-200/80 shadow-xs space-y-6">
        
        <!-- Cabecera -->
        <div>
            <h3 class="text-base font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-calculator text-indigo-600"></i> Simulador de Préstamo
            </h3>
            <p class="text-xs text-slate-400 mt-0.5">Ingresa toda tu información para procesar tu solicitud.</p>
        </div>

        <!-- Formulario -->
        <form action="{{ route('leads.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- CURP -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">CURP</label>
                <input type="text" name="curp" required placeholder="Ej. CEFM930108HCSLNR03" class="w-full text-xs rounded-xl border border-slate-200 p-3 text-slate-800 focus:outline-none focus:border-indigo-500">
            </div>

            <!-- Monto y Plazo -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Monto ($)</label>
                    <input type="number" step="0.01" name="monto" required placeholder="10000" class="w-full text-xs rounded-xl border border-slate-200 p-3 text-slate-800 focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Plazo</label>
                    <input type="text" name="plazo" required placeholder="18 meses" class="w-full text-xs rounded-xl border border-slate-200 p-3 text-slate-800 focus:outline-none focus:border-indigo-500">
                </div>
            </div>

            <!-- Correo y Teléfono -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Correo Electrónico</label>
                    <input type="email" name="correo" required placeholder="correo@gmail.com" class="w-full text-xs rounded-xl border border-slate-200 p-3 text-slate-800 focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Teléfono</label>
                    <input type="text" name="telefono" required placeholder="+52 9611221808" class="w-full text-xs rounded-xl border border-slate-200 p-3 text-slate-800 focus:outline-none focus:border-indigo-500">
                </div>
            </div>

            <!-- Tipo de Préstamo e Ingreso -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tipo de Préstamo</label>
                    <select name="tipo_prestamo" required class="w-full text-xs rounded-xl border border-slate-200 p-3 text-slate-800 bg-white focus:outline-none focus:border-indigo-500">
                        <option value="dni">DNI</option>
                        <option value="nomina">Nómina</option>
                        <option value="personal">Personal</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ingreso Mensual</label>
                    <select name="ingreso" required class="w-full text-xs rounded-xl border border-slate-200 p-3 text-slate-800 bg-white focus:outline-none focus:border-indigo-500">
                        <option value="menos_10000">Menos de $10,000</option>
                        <option value="10000_20000">$10,000 - $20,000</option>
                        <option value="mas_20000">Más de $20,000</option>
                    </select>
                </div>
            </div>

            <!-- Situación Laboral y Destino -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Situación Laboral</label>
                    <select name="laboral" required class="w-full text-xs rounded-xl border border-slate-200 p-3 text-slate-800 bg-white focus:outline-none focus:border-indigo-500">
                        <option value="dependiente">Dependiente</option>
                        <option value="independiente">Independiente / Autónomo</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Destino del Crédito</label>
                    <select name="destino" required class="w-full text-xs rounded-xl border border-slate-200 p-3 text-slate-800 bg-white focus:outline-none focus:border-indigo-500">
                        <option value="consumo">Consumo</option>
                        <option value="negocio">Negocio</option>
                        <option value="vivienda">Vivienda</option>
                    </select>
                </div>
            </div>

            <!-- Método de Contacto y Horario -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Medio de Contacto Preferido</label>
                    <select name="contacto" required class="w-full text-xs rounded-xl border border-slate-200 p-3 text-slate-800 bg-white focus:outline-none focus:border-indigo-500">
                        <option value="whatsapp">WhatsApp</option>
                        <option value="llamada">Llamada telefónica</option>
                        <option value="correo">Correo electrónico</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Horario de Contacto</label>
                    <select name="horario" required class="w-full text-xs rounded-xl border border-slate-200 p-3 text-slate-800 bg-white focus:outline-none focus:border-indigo-500">
                        <option value="9-12">9:00 AM - 12:00 PM</option>
                        <option value="12-15">12:00 PM - 3:00 PM</option>
                        <option value="15-18">3:00 PM - 6:00 PM</option>
                    </select>
                </div>
            </div>

            <!-- Botón Enviar -->
            <div class="pt-2">
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white p-3.5 rounded-xl text-xs font-bold transition-all shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-paper-plane"></i> Enviar Solicitud y Contactar por WhatsApp
                </button>
            </div>
        </form>

    </div>

    <!-- SweetAlert Redirección a WhatsApp -->
    @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: '¡Solicitud Enviada!',
                text: '{{ session('success') }}',
                confirmButtonColor: '#4f46e5',
                confirmButtonText: '<i class="fa-brands fa-whatsapp"></i> Continuar a WhatsApp',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open("{{ session('whatsapp_url') }}", '_blank');
                    window.location.reload();
                }
            });
        </script>
    @endif
</body>
</html>