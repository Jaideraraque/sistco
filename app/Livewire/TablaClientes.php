<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Cliente;

class TablaClientes extends Component
{
    use WithPagination;

    public string $buscar = '';
    public string $municipio = '';
    public string $estado = '';

    public function updatingBuscar()
    {
        $this->resetPage();
    }

    public function updatingMunicipio()
    {
        $this->resetPage();
    }

    public function updatingEstado()
    {
        $this->resetPage();
    }

    public function render()
    {
        $clientes = Cliente::query()
            ->when($this->buscar, function ($query) {
                $query->where('codigo_cliente', 'like', '%' . $this->buscar . '%')
                      ->orWhere('municipio', 'like', '%' . $this->buscar . '%');
            })
            ->when($this->municipio, function ($query) {
                $query->where('municipio', $this->municipio);
            })
            ->when($this->estado, function ($query) {
                if ($this->estado === 'moroso') {
                    $query->where('es_moroso', true);
                } else {
                    $query->where('es_moroso', false);
                }
            })
            ->orderBy('tasa_mora_historica', 'desc')
            ->paginate(10);

        $municipios = Cliente::select('municipio')
            ->distinct()
            ->orderBy('municipio')
            ->pluck('municipio');

        return view('livewire.tabla-clientes', [
            'clientes'   => $clientes,
            'municipios' => $municipios,
        ]);
    }
}