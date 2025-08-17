<?php
require_once 'Entidad.php';
class Libro extends Entidad {
    public $titulo;
    public $autor_id;
    public $categoria_id;
    public $estado;
    public function listar() { return $this->db->query("SELECT libros.id, libros.titulo, libros.estado, autores.nombre AS autor, categorias.nombre AS categoria, libros.autor_id, libros.categoria_id FROM libros LEFT JOIN autores ON libros.autor_id = autores.id LEFT JOIN categorias ON libros.categoria_id = categorias.id"); }
    public function buscar($texto) { $stmt = $this->db->prepare("SELECT libros.id, libros.titulo, libros.estado, autores.nombre AS autor, categorias.nombre AS categoria, libros.autor_id, libros.categoria_id FROM libros LEFT JOIN autores ON libros.autor_id = autores.id LEFT JOIN categorias ON libros.categoria_id = categorias.id WHERE libros.titulo LIKE ? OR autores.nombre LIKE ? OR categorias.nombre LIKE ?"); $like="%$texto%"; $stmt->execute([$like,$like,$like]); return $stmt; }
    public function agregar() { $stmt = $this->db->prepare("INSERT INTO libros(titulo, autor_id, categoria_id, estado) VALUES(?,?,?,?)"); $stmt->execute([$this->titulo,$this->autor_id,$this->categoria_id,$this->estado]); }
    public function editar($id, $datos) { $stmt = $this->db->prepare("UPDATE libros SET titulo=?, autor_id=?, categoria_id=?, estado=? WHERE id=?"); $stmt->execute([$datos['titulo'],$datos['autor_id'],$datos['categoria_id'],$datos['estado'],$id]); }
    public function eliminar($id) { $stmt = $this->db->prepare("DELETE FROM libros WHERE id=?"); $stmt->execute([$id]); }
}
?>
