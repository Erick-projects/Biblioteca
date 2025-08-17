<?php
abstract class Entidad {
    protected $db;
    public function __construct($db) { $this->db = $db; }
    abstract public function listar();
    abstract public function agregar();
    abstract public function editar($id, $datos);
    abstract public function eliminar($id);
}
?>
