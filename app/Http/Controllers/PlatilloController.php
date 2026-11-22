<?php

namespace App\Http\Controllers;

use App\Models\Platillo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlatilloController extends Controller
{
    /**
     * RF04 – Listar todos los platillos con su restaurante.
     * GET /admin/platillos
     */
    public function index(Request $request)
    {
        $query = Platillo::with('restaurante');

        if ($request->filled('disponible')) {
            $query->where('disponible', (bool) $request->disponible);
        }

        $platillos = $query
            ->orderByDesc('disponible')
            ->orderBy('nombre')
            ->paginate(20);

        return view('admin.platillos.index', compact('platillos'));
    }

    /**
     * RF04 – Mostrar formulario de edición.
     * GET /admin/platillos/{platillo}/edit
     */
    public function edit(Platillo $platillo)
    {
        return view('admin.platillos.edit', compact('platillo'));
    }

    /**
     * RF04/RF05 – Actualizar platillo con imagen.
     * PUT/PATCH /admin/platillos/{platillo}
     */
    public function update(Request $request, Platillo $platillo)
    {
        $validated = $request->validate([
            'nombre'         => 'required|string|max:255',
            'descripcion'    => 'required|string|max:2000',
            'imagen'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'disponible'     => 'required|boolean',
        ]);

        if ($request->hasFile('imagen')) {
            $imagenAnterior = $platillo->imagen;
            $validated['imagen'] = $request->file('imagen')->store('platillos', 'public');

            if ($imagenAnterior) {
                Storage::disk('public')->delete($imagenAnterior);
            }
        }

        $platillo->update($validated);

        return redirect()
            ->route('admin.platillos.index')
            ->with('success', "Platillo \"{$platillo->nombre}\" actualizado correctamente.");
    }

    /**
     * RF06 – Cambiar disponibilidad de un platillo (toggle).
     * PATCH /admin/platillos/{platillo}/disponibilidad
     */
    public function toggleDisponibilidad(Platillo $platillo)
    {
        $platillo->disponible = ! $platillo->disponible;
        $platillo->save();

        $estado = $platillo->disponible ? 'disponible' : 'no disponible';

        if (request()->expectsJson()) {
            return response()->json([
                'success'    => true,
                'disponible' => $platillo->disponible,
                'mensaje'    => "Platillo marcado como {$estado}.",
            ]);
        }

        return back()->with('success', "Platillo marcado como {$estado}.");
    }
}
