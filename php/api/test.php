<?php
require_once './clases/entidades/pizza.php';
require_once './clases/AccesoDatos.php';

var_dump(pizza::CheckBBDD());
var_dump(pizza::LlenarBBDD());