<?php

/**
 * Route definitions.
 * Attention: parsing is made in the opposite order as listed here.
 */

// Default
Router::addRoute(':controller/:action/*', []);

// Home
Router::addRoute('', ['controller' => 'Pages', 'action' => 'index',]);
Router::addRoute('archivo/*', ['controller' => 'Files', 'action' => 'view', 'force_ending_slash' => false]);
Router::addRoute('imagen/*', ['controller' => 'Pictures', 'action' => 'view', 'force_ending_slash' => false]);
Router::addRoute('enhorabuena/*', ['controller' => 'Pages', 'action' => 'thanks']);
Router::addRoute('ganadores/*', ['controller' => 'Pages', 'action' => 'winners']);
Router::addRoute('establecimientos/*', ['controller' => 'Pages', 'action' => 'establishments']);
Router::addRoute('legal/terminos-y-condiciones/*', ['controller' => 'Pages', 'action' => 'termsConditions']);
Router::addRoute('legal/politica-de-privacidad/*', ['controller' => 'Pages', 'action' => 'privacyPolicy']);
Router::addRoute('legal/politica-de-cookies/*', ['controller' => 'Pages', 'action' => 'cookiePolicy']);
Router::addRoute('legal/aviso-legal/*', ['controller' => 'Pages', 'action' => 'legalNotice']);
Router::addRoute('preguntas-frecuentes/*', ['controller' => 'Pages', 'action' => 'faq']);
Router::addRoute('ocultar-popup/*', ['controller' => 'Pages', 'action' => 'hidePopup']);
Router::addRoute('email/*', ['controller' => 'Pages', 'action' => 'email']);
Router::addRoute('validar/*', ['controller' => 'QrValidator', 'action' => 'validate']);
Router::addRoute('confirmar/*', ['controller' => 'QrValidator', 'action' => 'confirm']);

foreach (GestyMVC::config('restControllers') as $controller_name => $model_name) {
    $controller_name_no_prefix = $controller_name;

    if (starts_with($controller_name_no_prefix, 'Rest' . substr($model_name, 0, 2))) {
        $controller_name_no_prefix = substr($controller_name_no_prefix, 4);
    }

    Router::addRoute('rest/' . lcfirst($controller_name_no_prefix) . '/*', ['controller' => $controller_name, 'action' => 'rest', 'force_ending_slash' => false]);
}
