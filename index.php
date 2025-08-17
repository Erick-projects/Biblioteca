<?php
try {
    $db = new PDO('sqlite:database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("CREATE TABLE IF NOT EXISTS autores (id INTEGER PRIMARY KEY AUTOINCREMENT,nombre TEXT NOT NULL UNIQUE)");
    $db->exec("CREATE TABLE IF NOT EXISTS categorias (id INTEGER PRIMARY KEY AUTOINCREMENT,nombre TEXT NOT NULL UNIQUE)");
    $db->exec("CREATE TABLE IF NOT EXISTS libros (id INTEGER PRIMARY KEY AUTOINCREMENT,titulo TEXT NOT NULL,autor_id INTEGER,categoria_id INTEGER,estado TEXT DEFAULT 'disponible',FOREIGN KEY(autor_id) REFERENCES autores(id),FOREIGN KEY(categoria_id) REFERENCES categorias(id))");
} catch(PDOException $e) { die("Error SQLite: ".$e->getMessage()); }

require_once 'classes/Entidad.php';
require_once 'classes/Autor.php';
require_once 'classes/Categoria.php';
require_once 'classes/Libro.php';
require_once 'classes/Biblioteca.php';

$autorObj=new Autor($db);
$categoriaObj=new Categoria($db);
$libroObj=new Libro($db);
$biblioteca=new Biblioteca($db);

if($_SERVER['REQUEST_METHOD']==='POST'){
    if(isset($_POST['agregar_todo'])){
        $autorObj->nombre=trim($_POST['autor']);
        $stmt=$db->prepare("SELECT id FROM autores WHERE nombre=?"); $stmt->execute([$autorObj->nombre]);
        $autor_id=$stmt->fetchColumn(); if(!$autor_id){$autorObj->agregar(); $autor_id=$db->lastInsertId();}
        $categoriaObj->nombre=trim($_POST['categoria']);
        $stmt=$db->prepare("SELECT id FROM categorias WHERE nombre=?"); $stmt->execute([$categoriaObj->nombre]);
        $categoria_id=$stmt->fetchColumn(); if(!$categoria_id){$categoriaObj->agregar(); $categoria_id=$db->lastInsertId();}
        $libroObj->titulo=trim($_POST['titulo']); $libroObj->autor_id=$autor_id; $libroObj->categoria_id=$categoria_id; $libroObj->estado='disponible'; $libroObj->agregar();
        header("Location:index.php"); exit;
    }
    if(isset($_POST['editar_libro'])){
        $id_libro=$_POST['id_libro']; $autorObj->editar($_POST['autor_id'],['nombre'=>$_POST['autor']]); $categoriaObj->editar($_POST['categoria_id'],['nombre'=>$_POST['categoria']]);
        $libroObj->editar($id_libro,['titulo'=>$_POST['titulo'],'autor_id'=>$_POST['autor_id'],'categoria_id'=>$_POST['categoria_id'],'estado'=>$_POST['estado']]);
        header("Location:index.php"); exit;
    }
}

$editar=false; $libro_editar=null;

if(isset($_GET['accion'],$_GET['id'])){
    $id=$_GET['id'];
    switch($_GET['accion']){
        case 'prestar': $biblioteca->prestarLibro($id); header("Location:index.php"); exit;
        case 'devolver': $biblioteca->devolverLibro($id); header("Location:index.php"); exit;
        case 'eliminar_libro': $libroObj->eliminar($id); header("Location:index.php"); exit;
        case 'editar':
            $stmt=$db->prepare("SELECT libros.id, libros.titulo, libros.estado, libros.autor_id, libros.categoria_id, autores.nombre AS autor, categorias.nombre AS categoria FROM libros LEFT JOIN autores ON libros.autor_id=autores.id LEFT JOIN categorias ON libros.categoria_id=categorias.id WHERE libros.id=?");
            $stmt->execute([$id]);
            $libro_editar=$stmt->fetch(PDO::FETCH_ASSOC);
            $editar=true;
            break;
    }
}

$buscar=$_GET['buscar']??''; 
$libros=$buscar?$libroObj->buscar($buscar)->fetchAll(PDO::FETCH_ASSOC):$libroObj->listar()->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Biblioteca</title>
<style>
body{font-family:Arial;background:#f7f9fc;margin:0;padding:0;}
.container{width:90%;max-width:900px;margin:40px auto;background:#fff;padding:20px;box-shadow:0 0 15px rgba(0,0,0,0.1);border-radius:10px;}
h1,h2{text-align:center;color:#333;}
table{width:100%;border-collapse:collapse;margin-top:20px;}
th,td{padding:10px;text-align:left;border-bottom:1px solid #ddd;}
th{background:#f0f0f0;}
tr:hover{background:#f9f9f9;}
form{margin-top:20px;display:flex;gap:10px;flex-wrap:wrap;}
input,select{padding:8px;border:1px solid #ccc;border-radius:5px;flex:1;}
button{padding:8px 12px;border:none;border-radius:5px;background:#007BFF;color:#fff;cursor:pointer;}
button:hover{background:#0056b3;}
</style>
</head>
<body>
<div class="container">
<h1>Biblioteca</h1>

<h2>Agregar Libro, Autor y Categoría</h2>
<form method="POST">
<input type="text" name="titulo" placeholder="Título del libro" required>
<input type="text" name="autor" placeholder="Nombre del autor" required>
<input type="text" name="categoria" placeholder="Nombre de la categoría" required>
<button type="submit" name="agregar_todo">Agregar Todo</button>
</form>

<?php if($editar && $libro_editar): ?>
<h2>Editar Libro</h2>
<form method="POST">
<input type="hidden" name="id_libro" value="<?= $libro_editar['id'] ?>">
<input type="hidden" name="autor_id" value="<?= $libro_editar['autor_id'] ?>">
<input type="hidden" name="categoria_id" value="<?= $libro_editar['categoria_id'] ?>">
<input type="text" name="titulo" value="<?= htmlspecialchars($libro_editar['titulo']) ?>" required>
<input type="text" name="autor" value="<?= htmlspecialchars($libro_editar['autor']) ?>" required>
<input type="text" name="categoria" value="<?= htmlspecialchars($libro_editar['categoria']) ?>" required>
<select name="estado">
<option value="disponible" <?= $libro_editar['estado']=='disponible'?'selected':'' ?>>Disponible</option>
<option value="prestado" <?= $libro_editar['estado']=='prestado'?'selected':'' ?>>Prestado</option>
</select>
<button type="submit" name="editar_libro">Guardar Cambios</button>
</form>
<?php endif; ?>

<form method="GET" style="margin-top:30px;">
<input type="text" name="buscar" placeholder="Buscar por título, autor o categoría" value="<?= htmlspecialchars($buscar) ?>">
<button type="submit">Buscar</button>
</form>

<table>
<thead>
<tr>
<th>Título</th>
<th>Autor</th>
<th>Categoría</th>
<th>Estado</th>
<th>Acciones</th>
</tr>
</thead>
<tbody>
<?php foreach($libros as $l): ?>
<tr>
<td><?= htmlspecialchars($l['titulo']) ?></td>
<td><?= htmlspecialchars($l['autor']) ?></td>
<td><?= htmlspecialchars($l['categoria']) ?></td>
<td><?= htmlspecialchars($l['estado']) ?></td>
<td>
<?php if($l['estado']=='disponible'): ?>
<a href="?accion=prestar&id=<?= $l['id'] ?>"><button>Prestar</button></a>
<?php else: ?>
<a href="?accion=devolver&id=<?= $l['id'] ?>"><button>Devolver</button></a>
<?php endif; ?>
<a href="?accion=editar&id=<?= $l['id'] ?>"><button style="background:orange;">Editar</button></a>
<a href="?accion=eliminar_libro&id=<?= $l['id'] ?>"><button style="background:red;">Eliminar</button></a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</body>
</html>
