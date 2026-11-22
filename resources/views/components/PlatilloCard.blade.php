@props(['platillo'])

<div class="card shadow-sm mb-3 rounded border-0">
    <div class="card-body">
        <h5 class="card-title text-primary fw-bold">{{ $platillo['nombre'] ?? $platillo->nombre }}</h5>
        <p class="card-text text-muted">{{ $platillo['descripcion'] ?? $platillo->descripcion }}</p>
    </div>
</div>