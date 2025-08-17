<?php
require_once 'Entidad.php';
class Autor extends Entidad {
    public $nombre;
    public function listar() { return $this->db->query("SELECT * FROM autores"); }
    public function agregar() { $stmt = $this->db->prepare("INSERT INTO autores(nombre) VALUES(?)"); $stmt->execute([$this->nombre]); }
    public function editar($id, $datos) { $stmt = $this->db->prepare("UPDATE autores SET nombre=? WHERE id=?"); $stmt->execute([$datos['nombre'], $id]); }
    public function eliminar($id) { $stmt = $this->db->prepare("DELETE FROM autores WHERE id=?"); $stmt->execute([$id]); }
}
?>
