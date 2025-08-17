<?php
require_once 'Entidad.php';
class Categoria extends Entidad {
    public $nombre;
    public function listar() { return $this->db->query("SELECT * FROM categorias"); }
    public function agregar() { $stmt = $this->db->prepare("INSERT INTO categorias(nombre) VALUES(?)"); $stmt->execute([$this->nombre]); }
    public function editar($id, $datos) { $stmt = $this->db->prepare("UPDATE categorias SET nombre=? WHERE id=?"); $stmt->execute([$datos['nombre'], $id]); }
    public function eliminar($id) { $stmt = $this->db->prepare("DELETE FROM categorias WHERE id=?"); $stmt->execute([$id]); }
}
?>
