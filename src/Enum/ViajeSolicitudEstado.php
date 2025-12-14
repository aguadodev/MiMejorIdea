<?php 
// src/Enum/ViajeSolicitudEstado.php

namespace App\Enum;

enum ViajeSolicitudEstado: string
{
    case PENDIENTE = 'pendiente';
    case ACEPTADA = 'aceptada';
    case RECHAZADA = 'rechazada';
}
