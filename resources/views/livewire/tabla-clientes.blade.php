<div>
    {{-- Filtros --}}
    <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap">
        <input
            wire:model.live="buscar"
            type="text"
            placeholder="Buscar por código o municipio..."
            style="flex:1;min-width:200px;padding:8px 12px;border:1px solid #e8e8e8;border-radius:8px;font-size:13px;outline:none;transition:border .2s"
            onfocus="this.style.borderColor='#0099D6'" onblur="this.style.borderColor='#e8e8e8'"
        >
        <select
            wire:model.live="municipio"
            style="padding:8px 12px;border:1px solid #e8e8e8;border-radius:8px;font-size:13px;outline:none;background:#fff;color:#444">
            <option value="">Todos los municipios</option>
            @foreach($municipios as $m)
                <option value="{{ $m }}">{{ $m }}</option>
            @endforeach
        </select>
        <select
            wire:model.live="estado"
            style="padding:8px 12px;border:1px solid #e8e8e8;border-radius:8px;font-size:13px;outline:none;background:#fff;color:#444">
            <option value="">Todos los estados</option>
            <option value="moroso">En mora</option>
            <option value="al_dia">Al día</option>
        </select>
    </div>

    {{-- Tabla --}}
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:12px">
            <thead>
                <tr style="background:#F0F4F8">
                    <th style="padding:10px 12px;text-align:left;color:#888;font-weight:500;border-bottom:1px solid #e8e8e8;white-space:nowrap">Código</th>
                    <th style="padding:10px 12px;text-align:left;color:#888;font-weight:500;border-bottom:1px solid #e8e8e8;white-space:nowrap">Municipio</th>
                    <th style="padding:10px 12px;text-align:left;color:#888;font-weight:500;border-bottom:1px solid #e8e8e8;white-space:nowrap">Megas</th>
                    <th style="padding:10px 12px;text-align:left;color:#888;font-weight:500;border-bottom:1px solid #e8e8e8;white-space:nowrap">Mensualidad</th>
                    <th style="padding:10px 12px;text-align:left;color:#888;font-weight:500;border-bottom:1px solid #e8e8e8;white-space:nowrap">Antigüedad</th>
                    <th style="padding:10px 12px;text-align:left;color:#888;font-weight:500;border-bottom:1px solid #e8e8e8;white-space:nowrap">Moras hist.</th>
                    <th style="padding:10px 12px;text-align:left;color:#888;font-weight:500;border-bottom:1px solid #e8e8e8;white-space:nowrap">Tasa mora</th>
                    <th style="padding:10px 12px;text-align:left;color:#888;font-weight:500;border-bottom:1px solid #e8e8e8;white-space:nowrap">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientes as $cliente)
                <tr style="border-bottom:1px solid #f5f5f5;transition:background .15s" onmouseover="this.style.background='#f9fbff'" onmouseout="this.style.background=''">
                    <td style="padding:10px 12px;color:#1a1a2e;font-weight:600">#{{ $cliente->codigo_cliente }}</td>
                    <td style="padding:10px 12px;color:#555">{{ $cliente->municipio }}</td>
                    <td style="padding:10px 12px;color:#555">{{ $cliente->megas }}</td>
                    <td style="padding:10px 12px;color:#555">${{ number_format($cliente->mensualidad, 0, ',', '.') }}</td>
                    <td style="padding:10px 12px;color:#555">{{ $cliente->antiguedad_meses }} meses</td>
                    <td style="padding:10px 12px;color:#555">{{ $cliente->n_moras_historicas }}</td>
                    <td style="padding:10px 12px">
                        <span style="font-weight:600;color:{{ $cliente->tasa_mora_historica > 0.05 ? '#E74C3C' : '#27AE60' }}">
                            {{ number_format($cliente->tasa_mora_historica * 100, 1) }}%
                        </span>
                    </td>
                    <td style="padding:10px 12px">
                        @if($cliente->es_moroso)
                            <span style="background:#FDEDEC;color:#A32D2D;padding:3px 10px;border-radius:20px;font-size:10px;font-weight:600">En mora</span>
                        @else
                            <span style="background:#EAF3DE;color:#27500A;padding:3px 10px;border-radius:20px;font-size:10px;font-weight:600">Al día</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="padding:30px;text-align:center;color:#aaa">
                        No se encontraron clientes con los filtros aplicados
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    <div style="margin-top:14px;display:flex;align-items:center;justify-content:space-between;font-size:11px;color:#aaa">
        <span>Mostrando {{ $clientes->firstItem() ?? 0 }} - {{ $clientes->lastItem() ?? 0 }} de {{ $clientes->total() }} clientes</span>
        <div style="display:flex;gap:4px">
            @if($clientes->onFirstPage())
                <span style="padding:5px 12px;border:1px solid #eee;border-radius:6px;color:#ccc">←</span>
            @else
                <a wire:click="previousPage" style="padding:5px 12px;border:1px solid #ddd;border-radius:6px;color:#555;cursor:pointer">←</a>
            @endif
            @if($clientes->hasMorePages())
                <a wire:click="nextPage" style="padding:5px 12px;border:1px solid #0099D6;border-radius:6px;color:#0099D6;cursor:pointer;background:#E6F5FC">→</a>
            @else
                <span style="padding:5px 12px;border:1px solid #eee;border-radius:6px;color:#ccc">→</span>
            @endif
        </div>
    </div>
</div>