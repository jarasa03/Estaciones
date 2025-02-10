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
            @foreach($estaciones as $estacion)
            <tr>
                <td>{{ $estacion->id }}</td>
                <td>{{ $estacion->nombre }}</td>
                <td>{{ $estacion->provincia }}</td>
                <td>{{ $estacion->latitud }}</td>
                <td>{{ $estacion->longitud }}</td>
                <td>{{ $estacion->altitud }}</td>
                <td>{{ $estacion->estado }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $estaciones->links() }}  <!-- Esto agregará los controles de paginación -->
</body>

</html>