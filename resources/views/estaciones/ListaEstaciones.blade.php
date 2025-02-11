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
                <th scope="col">Ver ficha</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($estaciones as $estacion)
                <tr>
                    <td class="num">{{ $estacion->id }}</td>
                    <td class="letra">{{ $estacion->nombre }}</td>
                    <td class="letra">{{ $estacion->provincia }}</td>
                    <td class="num">{{ $estacion->latitud }}</td>
                    <td class="num">{{ $estacion->longitud }}</td>
                    <td class="num">{{ $estacion->altitud }}</td>
                    <td class="letra">{{ \App\Helpers\EstadoHelper::obtenerEstado($estacion->estado) }}</td>
                    <td id="ficha"><a class="button" href="{{ route('estaciones.ficha', ['id' => $estacion->id]) }}">Ver ficha</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">No hay estaciones disponibles.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div id="pagination">
        {{ $estaciones->links('pagination::bootstrap-4') }} {{-- Esto agregará los controles de paginación --}}
    </div>
</body>

</html>
