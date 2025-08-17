<?php
class Biblioteca {
    private $db;
    public function __construct($db){$this->db=$db;}
    public function prestarLibro($id){$stmt=$this->db->prepare("UPDATE libros SET estado='prestado' WHERE id=?");$stmt->execute([$id]);}
    public function devolverLibro($id){$stmt=$this->db->prepare("UPDATE libros SET estado='disponible' WHERE id=?");$stmt->execute([$id]);}
}
?>
