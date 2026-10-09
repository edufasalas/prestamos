<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo - Clientes</title>
    
    <!-- Tailwind CSS & FontAwesome -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- DataTables CSS CDN (Globales) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>    
    
    <!-- Jssip CDN -->
    <script src="https://cdn.jsdelivr.net/npm/jssip/dist/jssip.min.js"></script>

    <!-- sweetalert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* Estilos globales para DataTables en Tailwind */
        .dataTables_wrapper .dataTables_length select {
            border-radius: 0.5rem;
            padding: 0.35rem 2rem 0.35rem 0.75rem;
            border-color: #e5e7eb;
            font-size: 0.875rem;
        }
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 0.5rem;
            padding: 0.4rem 0.75rem;
            border: 1px solid #e5e7eb;
            font-size: 0.875rem;
            outline: none;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
        }
        .dt-buttons {
            margin-bottom: 1rem;
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .dt-button {
            border: none !important;
            border-radius: 0.5rem !important;
            padding: 0.5rem 1rem !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            transition: all 0.2s ease-in-out !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
        }
        button.dt-button.buttons-excel { background-color: #10b981 !important; color: white !important; }
        button.dt-button.buttons-excel:hover { background-color: #059669 !important; }
        button.dt-button.buttons-pdf { background-color: #ef4444 !important; color: white !important; }
        button.dt-button.buttons-pdf:hover { background-color: #dc2626 !important; }
        button.dt-button.buttons-copy { background-color: #6366f1 !important; color: white !important; }
        button.dt-button.buttons-copy:hover { background-color: #4f46e5 !important; }
        button.dt-button.buttons-print { background-color: #6b7280 !important; color: white !important; }
        button.dt-button.buttons-print:hover { background-color: #4b5563 !important; }
        table.dataTable.no-footer { border-bottom: 1px solid #f3f4f6 !important; }

        /* REGLAS PARA COLAPSAR EN ESCRITORIO (PC) */
        @media (min-width: 1024px) {
            body.sidebar-collapsed #app-sidebar {
                width: 5rem !important; 
            }
            body.sidebar-collapsed #app-sidebar .sidebar-text {
                display: none !important; 
            }
            body.sidebar-collapsed #main-wrapper {
                padding-left: 5rem !important; 
            }
        }
    </style>

    @stack('styles')
</head>
<body class="bg-slate-50 font-sans antialiased overflow-x-hidden">

    <div class="min-h-screen flex w-full">
        
        <!-- Sidebar -->
        @include('layouts.sidebar')

        <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-950/50 z-40 hidden lg:hidden transition-opacity backdrop-blur-xs"></div>

        <!-- Contenedor Principal corregido con w-0 y flex-1 para evitar desbordes -->
        <div id="main-wrapper" class="flex-1 w-full lg:pl-64 flex flex-col transition-all duration-300 min-w-0">
            <main class="flex-1 w-full">
                @yield('content')
            </main>
        </div>

    </div>
    <!-- Componente de Chat VoIP -->
    <x-chat-widget />

    <!-- DataTables & Extensiones JS (Globales) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('app-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            if (window.innerWidth >= 1024) {
                document.body.classList.toggle('sidebar-collapsed');
            } else {
                if (sidebar) {
                    sidebar.classList.toggle('-translate-x-full');
                }
                if (overlay) {
                    overlay.classList.toggle('hidden');
                }
            }
        }
    </script>
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Alerta de Éxito
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: '{{ session('success') }}',
                    timer: 3000,
                    showConfirmButton: false,
                    customClass: {
                        popup: 'rounded-2xl'
                    }
                });
            @if(session('error') || $errors->any())
            // Alerta de Error general o de validación
            @endif
            @endif
    
            @if($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Atención',
                    html: '<ul class="text-left text-xs space-y-1">@foreach($errors->all() as $error)<li>• {{ $error }}</li>@endforeach</ul>',
                    confirmButtonColor: '#0f172a',
                    customClass: {
                        popup: 'rounded-2xl'
                    }
                });
            @endif
        });
    </script>    

    @stack('scripts')
</body>
</html>