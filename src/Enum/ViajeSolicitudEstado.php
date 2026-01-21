<?php 
// src/Enum/ViajeSolicitudEstado.php

namespace App\Enum;

enum ViajeSolicitudEstado: string
{
    case PENDIENTE = 'Pendiente';
    case ACEPTADA = 'Aceptada';
    case RECHAZADA = 'Rechazada';
    case RECHAZADA_POR_CANCELACION_VIAJE = 'Rechazada por Cancelación de Viaje';
    case CANCELADA_POR_CONDUCTOR = 'Cancelada por Conductor';
    case CANCELADA_POR_PASAJERO = 'Cancelada por Pasajero';
}
