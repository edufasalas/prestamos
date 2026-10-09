@forelse($clientes as $cli)
    <tr class="hover:bg-slate-50/65 transition-colors">
        <td class="py-3.5 px-4">
            <div class="font-bold text-slate-800">{{ $cli->nombre }}</div>
            <div class="text-[11px] text-slate-400">{{ $cli->empresa ?? 'Sin empresa' }}</div>
        </td>
        <td class="py-3.5 px-4 font-mono font-bold text-slate-700">
            {{ $cli->telefono }}
        </td>
        <td class="py-3.5 px-4 text-slate-500">
            {{ $cli->correo ?? 'No registrado' }}
        </td>
        <td class="py-3.5 px-4">
            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 inline-block">
                {{ $cli->estado_crm ?? 'Prospecto' }}
            </span>
        </td>
        <td class="py-3.5 px-4 text-right">
            <a href="{{ route('agent.cliente.ficha', ['telefono' => $cli->telefono]) }}" class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-indigo-600 text-slate-700 hover:text-white px-3 py-1.5 rounded-xl text-xs font-bold transition-all border border-slate-200 hover:border-indigo-600 shadow-2xs" title="Abrir Ficha CRM">
                <i class="fa-solid fa-address-book"></i> Ficha
            </a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="text-center py-12 text-slate-400 italic text-xs">
            No se encontraron clientes registrados con los filtros actuales.
        </td>
    </tr>
@endforelse