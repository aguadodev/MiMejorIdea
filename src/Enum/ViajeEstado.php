<?php

namespace App\Enum;

enum ViajeEstado: string
{
    case CREADO = 'Creado';
    case PUBLICADO = 'Publicado';
    case CANCELADO = 'Cancelado';
    case FINALIZADO = 'Finalizado';

    public function esActivo(): bool
    {
        return $this === self::PUBLICADO;
    }

    public function esCancelable(): bool
    {
        return in_array($this, [
            self::CREADO,
            self::PUBLICADO,
        ], true);
    }
}
