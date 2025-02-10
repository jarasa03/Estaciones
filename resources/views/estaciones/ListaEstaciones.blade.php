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

    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Provincia</th>
                <th>Latitud</th>
                <th>Longitud</th>
                <th>Altitud</th>
                <th>Estado</th>
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
                    <td>
                        <!-- Lógica para mostrar 'inactive' o 'active' -->
                        @php
                            $estado =
                                is_object($estacion->estado) || is_array($estacion->estado)
                                    ? $estacion->estado['estado']
                                    : $estacion->estado;
                        @endphp

                        <!-- Mostrar 'inactive' si estado es 0 o null, 'active' si es 1 -->
                        @if ($estado === 0 || $estado === null)
                            Inactive
                        @elseif($estado === 1)
                            Active
                        @else
                            {{ $estado }} <!-- En caso de que sea otro valor inesperado -->
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No hay estaciones disponibles.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    {{ $estaciones->links() }} <!-- Esto agregará los controles de paginación -->
</body>

</html>
