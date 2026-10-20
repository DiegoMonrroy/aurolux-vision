<?php
// Credenciales de la demo. Para cambiar la clave, genera un hash nuevo:
//   php -r "echo password_hash('NuevaClave', PASSWORD_DEFAULT);"
return [
    'show_demo_hint' => true, // false = oculta el cuadro con las credenciales en el login
    'users' => [
        'demo@aurolux.cl' => [
            'name' => 'Aurolux Demo',
            'hash' => '$2y$10$pMRJQGhx1qcPthyGR.fACefDoh/IK4e0agqJZGIPMh3EBhCB14Pdi', // clave inicial: Aurolux2026!
        ],
    ],
];
