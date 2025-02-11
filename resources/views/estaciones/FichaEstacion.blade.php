<!-- resources/views/estaciones/FichaEstacion.blade.php -->
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha de la Estación</title>

    <!-- Preload de la fuente -->
    <link rel="preload" href="{{ asset('fonts/LEMONMILK-Regular.otf') }}" as="font" type="font/otf"
        crossorigin="anonymous">

    <!-- CSS -->
    <link href="{{ asset('css/fichaEstacion.css') }}" rel="stylesheet">
</head>

<body>
    <h1>Ficha de la Estación</h1>

    <div id="contenedorP">
        <div id="contenedorH">
            <h1 id="estacion">{{ $estacion->nombre }}</h1>
            <p><strong>ID:</strong> <span>{{ $estacion->id }}</span></p>
            <p><strong>Provincia:</strong> <span>{{ $estacion->provincia }}</span></p>
            <p><strong>Latitud:</strong> <span>{{ $estacion->latitud }}</span></p>
            <p><strong>Longitud:</strong> <span>{{ $estacion->longitud }}</span></p>
            <p><strong>Altitud:</strong> <span>{{ $estacion->altitud }}</span></p>
            <p><strong>Estado:</strong> <span>{{ \App\Helpers\EstadoHelper::obtenerEstado($estacion->estado) }}</p>
        </div>
    </div>
    <a class="button" href="{{ route('estaciones.index') }}">Volver a la lista de estaciones</a>

</body>

</html>
