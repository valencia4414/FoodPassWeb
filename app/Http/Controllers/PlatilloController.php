<?php

namespace App\Http\Controllers;

use App\Models\Platillo;
use App\Models\Restaurante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlatilloController extends Controller
{
    /**
     * Listar exclusivamente los platillos del día.
     * GET /admin/platillos
     */
    public function index(Request $request)
    {
        $query = Platillo::query();

        // Filtro por búsqueda de nombre
        if ($request->filled('buscar')) {
            $query->where('nombre', 'like', '%' . trim($request->buscar) . '%');
        }

        // Filtro por estado de disponibilidad (1 = disponible, 0 = agotado)
        if ($request->filled('disponible')) {
            $query->where('disponible', (bool) $request->disponible);
        }

        $platillos  = $query->orderBy('creado_en', 'desc')->paginate(15);
        $totalCount = Platillo::count();
        $dispCount  = Platillo::where('disponible', 1)->count();
        $agotCount  = Platillo::where('disponible', 0)->count();

        return view('admin.platillos.index', compact('platillos', 'totalCount', 'dispCount', 'agotCount'));
    }

    /**
     * Mostrar formulario para agregar un platillo del día.
     * GET /admin/platillos/create
     */
    public function create()
    {
        return view('admin.platillos.create');
    }

    /**
     * Guardar nuevo platillo del día.
     * Solo gestiona: Foto, Nombre, Descripción y Disponibilidad.
     * POST /admin/platillos
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'required|string',
            'imagen'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'disponible'  => 'nullable',
        ], [
            'nombre.required'      => 'El nombre del platillo es obligatorio.',
            'descripcion.required' => 'La descripción del platillo es obligatoria.',
            'imagen.image'         => 'El archivo seleccionado debe ser una imagen válida.',
            'imagen.mimes'         => 'La imagen debe ser formato JPG, JPEG, PNG o WEBP.',
            'imagen.max'           => 'La imagen no debe superar los 2MB.',
        ]);

        // Guardar imagen si fue proporcionada
        if ($request->hasFile('imagen')) {
            $validated['imagen'] = $request->file('imagen')->store('platillos', 'public');
        }

        // Determinar estado de disponibilidad (disponible vs agotado)
        $dispInput = $request->input('disponible');
        $validated['disponible'] = !($dispInput === '0' || $dispInput === 'agotado' || $dispInput === false);

        // Campos por defecto de compatibilidad con la base de datos
        $validated['restaurante_id'] = Restaurante::first()?->id ?? 1;
        $validated['categoria_id']   = 1; // Menú del día
        $validated['stock']          = null;

        $platillo = Platillo::create($validated);

        return redirect()
            ->route('admin.platillos.index')
            ->with('success', "Platillo del día \"{$platillo->nombre}\" guardado correctamente.");
    }

    /**
     * Mostrar detalle de un platillo del día.
     * GET /admin/platillos/{platillo}
     */
    public function show(Platillo $platillo)
    {
        return view('admin.platillos.show', compact('platillo'));
    }

    /**
     * Mostrar formulario de edición del platillo del día.
     * Solo gestiona: Foto, Nombre, Descripción y Disponibilidad.
     * GET /admin/platillos/{platillo}/edit
     */
    public function edit(Platillo $platillo)
    {
        return view('admin.platillos.edit', compact('platillo'));
    }

    /**
     * Actualizar platillo del día.
     * Solo gestiona: Foto, Nombre, Descripción y Disponibilidad.
     * PUT/PATCH /admin/platillos/{platillo}
     */
    public function update(Request $request, Platillo $platillo)
    {
        $validated = $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'required|string',
            'imagen'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'disponible'  => 'nullable',
        ], [
            'nombre.required'      => 'El nombre del platillo es obligatorio.',
            'descripcion.required' => 'La descripción del platillo es obligatoria.',
            'imagen.image'         => 'El archivo seleccionado debe ser una imagen válida.',
            'imagen.mimes'         => 'La imagen debe ser formato JPG, JPEG, PNG o WEBP.',
            'imagen.max'           => 'La imagen no debe superar los 2MB.',
        ]);

        // Reemplazar imagen si se subió una nueva
        if ($request->hasFile('imagen')) {
            if ($platillo->imagen && !str_starts_with($platillo->imagen, 'http') && Storage::disk('public')->exists($platillo->imagen)) {
                Storage::disk('public')->delete($platillo->imagen);
            }
            $validated['imagen'] = $request->file('imagen')->store('platillos', 'public');
        }

        // Determinar estado de disponibilidad
        $dispInput = $request->input('disponible');
        $isDisponible = !($dispInput === '0' || $dispInput === 'agotado' || $dispInput === false);

        $platillo->update([
            'nombre'      => $validated['nombre'],
            'descripcion' => $validated['descripcion'],
            'imagen'      => $validated['imagen'] ?? $platillo->imagen,
            'disponible'  => $isDisponible,
        ]);

        return redirect()
            ->route('admin.platillos.index')
            ->with('success', "Platillo \"{$platillo->nombre}\" actualizado correctamente.");
    }

    /**
     * Cambiar disponibilidad del platillo (Disponible / Agotado).
     * PATCH /admin/platillos/{platillo}/disponibilidad
     */
    public function toggleDisponibilidad(Request $request, Platillo $platillo)
    {
        if ($request->has('disponible')) {
            $val = $request->input('disponible');
            $platillo->disponible = !($val === '0' || $val === 'agotado' || $val === false);
        } else {
            $platillo->disponible = ! $platillo->disponible;
        }

        $platillo->save();

        $estado = $platillo->disponible ? 'Disponible' : 'Agotado';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'    => true,
                'disponible' => $platillo->disponible,
                'estado'     => $estado,
                'mensaje'    => "El platillo \"{$platillo->nombre}\" ahora está marcado como {$estado}.",
            ]);
        }

        return back()->with('success', "El platillo \"{$platillo->nombre}\" ahora está marcado como {$estado}.");
    }

    /**
     * Eliminar platillo del día.
     * DELETE /admin/platillos/{platillo}
     */
    public function destroy(Platillo $platillo)
    {
        if ($platillo->imagen && !str_starts_with($platillo->imagen, 'http') && Storage::disk('public')->exists($platillo->imagen)) {
            Storage::disk('public')->delete($platillo->imagen);
        }

        $nombre = $platillo->nombre;
        $platillo->delete();

        return redirect()
            ->route('admin.platillos.index')
            ->with('success', "Platillo \"{$nombre}\" eliminado correctamente.");
    }
}
