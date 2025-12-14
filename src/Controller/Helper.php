<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// trait para codigo reutilizable en los controladores.
trait Helper {

    // Muestra un mensaje flash y vuelve atrás
    public function denyAndBack(Request $request, string $message, string $type = 'warning'): Response
    {
        $this->addFlash($type, $message);

        return $this->redirect(
            $request->headers->get('referer')
                ?? $this->generateUrl('app_index')
        );
    }    

}