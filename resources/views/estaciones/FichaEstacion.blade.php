<!-- resources/views/estaciones/FichaEstacion.blade.php -->
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha de la Estación</title>
    <link href="{{ asset('css/fichaEstacion.css') }}" rel="stylesheet">
</head>

<body>
    <h1>Ficha de la Estación</h1>

    <div>
        <h2>Nombre: {{ $estacion->nombre }}</h2>
        <p><strong>ID:</strong> {{ $estacion->id }}</p>
        <p><strong>Provincia:</strong> {{ $estacion->provincia }}</p>
        <p><strong>Latitud:</strong> {{ $estacion->latitud }}</p>
        <p><strong>Longitud:</strong> {{ $estacion->longitud }}</p>
        <p><strong>Altitud:</strong> {{ $estacion->altitud }}</p>
        <p><strong>Estado:</strong> {{ \App\Helpers\EstadoHelper::obtenerEstado($estacion->estado) }}</p>
    </div>
    <a href="{{ route('estaciones.index') }}">Volver a la lista de estaciones</a>

</body>

</html>
