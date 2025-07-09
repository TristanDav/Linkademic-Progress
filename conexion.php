<?php
// Archivo de conexión a la base de datos
$host = 'localhost:3307';
$usuario = 'root';
$contrasena = '';
$base_de_datos = 'linkademic';

// Crear conexión
$conexion = new mysqli($host, $usuario, $contrasena, $base_de_datos);

// Verificar conexión
if ($conexion->connect_error) {
    die('Error de conexión: ' . $conexion->connect_error);
}
// Opcional: establecer el charset a utf8
$conexion->set_charset('utf8');
?> 