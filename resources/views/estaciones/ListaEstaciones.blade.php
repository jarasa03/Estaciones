<!-- resources/views/estaciones/ListaEstaciones.blade.php -->
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Estaciones</title>

    <!-- Preload de la fuente -->
    <link rel="preload" href="{{ asset('fonts/LEMONMILK-Regular.otf') }}" as="font" type="font/otf"
        crossorigin="anonymous">

    <!-- CSS -->
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
                    <td>{{ $estacion->id }}</td>
                    <td>{{ $estacion->nombre }}</td>
                    <td>{{ $estacion->provincia }}</td>
                    <td>{{ $estacion->x }}</td>
                    <td>{{ $estacion->y }}</td>
                    <td>{{ $estacion->altitud }}</td>
                    <td>{{ \App\Helpers\EstadoHelper::obtenerEstado($estacion->estado) }}</td>
                    <td id="ficha"><a class="button"
                            href="{{ route('estaciones.ficha', ['id' => $estacion->id]) }}">Ver ficha</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">No hay estaciones disponibles.</td>
                </tr>
            @endforelse
        </tbody>        
    </table>
</body>

</html>
