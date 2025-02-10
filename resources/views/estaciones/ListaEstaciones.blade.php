<!-- resources/views/estaciones/ListaEstaciones.blade.php -->
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Estaciones</title>
    <link href="{{ asset('css/listaEstaciones.css') }}" rel="stylesheet">

</head>

<body>
    <h1>Lista de Estaciones</h1>

    <table class="estacion-table">
        <thead>
            <tr>
                {{-- Atributo scope para accesibilidad en encabezados para screen readers --}}
                <th scope="col">ID</th>
                <th scope="col">Nombre</th>
                <th scope="col">Provincia</th>
                <th scope="col">Latitud</th>
                <th scope="col">Longitud</th>
                <th scope="col">Altitud</th>
                <th scope="col">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($estaciones as $estacion)
                <tr>
                    <td>{{ $estacion->id }}</td>
                    <td>{{ $estacion->nombre }}</td>
                    <td>{{ $estacion->provincia }}</td>
                    <td>{{ $estacion->latitud }}</td>
                    <td>{{ $estacion->longitud }}</td>
                    <td>{{ $estacion->altitud }}</td>
                    <td>{{ \App\Helpers\EstadoHelper::obtenerEstado($estacion->estado) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No hay estaciones disponibles.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div id="pagination">
        {{ $estaciones->links('pagination::bootstrap-4') }} {{-- Esto agregará los controles de paginación --}}
    </div>
</body>

</html>
