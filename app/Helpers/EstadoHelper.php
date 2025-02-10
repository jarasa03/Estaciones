<?php

namespace App\Helpers;

class EstadoHelper
{
    public static function obtenerEstado($estado)
    {
        if (is_object($estado) || is_array($estado)) {
            $estado = $estado['estado'];
        }

        return $estado === 0 || $estado === null ? 'Inactive' : ($estado === 1 ? 'Active' : $estado);
    }
}
