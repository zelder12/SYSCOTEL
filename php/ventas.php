<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['nombre'])) {
    header('Location: login.php');
    exit();
}
if (empty($_SESSION['es_admin']) || $_SESSION['es_admin'] !== true) {
    echo "No tienes permiso para acceder.";
    exit();
}

if (isset($_GET['id']) && isset($_GET['estado'])) {
    $id = $_GET['id'];
    $estado = $_GET['estado'];
    
    $sql = "UPDATE pedidos SET estado = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $estado, $id);
    $stmt->execute();
    
    header("Location: ventas.php");
    exit();
}

$sql = "SELECT p.*, u.nombre as nombre_usuario 
        FROM pedidos p 
        LEFT JOIN usuarios u ON p.usuario_id = u.id 
        ORDER BY p.fecha DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Ventas - SYSCOTEL</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="shortcut icon" href="../img/syscotel.png" />
    <style>
        .pedidos-container {
            padding: 20px;
        }
        .pedido-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            padding: 20px;
        }
        .pedido-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .pedido-productos {
            margin-bottom: 15px;
        }
        .producto-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            padding: 5px 0;
            border-bottom: 1px dashed #eee;
        }
        .pedido-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .estado-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        .estado-pendiente { background: #ffeeba; color: #856404; }
        .estado-procesando { background: #b8daff; color: #004085; }
        .estado-enviado { background: #c3e6cb; color: #155724; }
        .estado-entregado { background: #d4edda; color: #155724; }
        .estado-cancelado { background: #f5c6cb; color: #721c24; }
        .acciones-btn {
            padding: 5px 10px;
            border-radius: 4px;
            margin-right: 5px;
            text-decoration: none;
            color: white;
            font-size: 14px;
        }
        .btn-procesar { background: #007bff; }
        .btn-enviar { background: #28a745; }
        .btn-entregar { background: #17a2b8; }
        .btn-cancelar { background: #dc3545; }
        .btn-ver { background: #6c757d; }
        .tipo-compra {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            margin-left: 10px;
        }
        .compra-online { background: #e3f2fd; color: #0d47a1; }
        .compra-local { background: #f1f8e9; color: #33691e; }
        .filtros {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .filtro-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            background: #f0f0f0;
            cursor: pointer;
        }
        .filtro-btn.active {
            background: #1abc9c;
            color: white;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <a href="../index.php">
                <img src="../img/syscotel.png" alt="Logo SYSCOTEL">
            </a>
        </div>
        <nav>
            <ul>
                <li><a href="../index.php">Inicio</a></li>
                <li><a href="../admin.php">Panel Admin</a></li>
                <li><a href="agregar.php">Productos</a></li>
                <li><a href="logout.php">Cerrar Sesión</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="pedidos-container">
            <h1>Administración de Ventas y Pedidos</h1>
            
            <div class="filtros">
                <button class="filtro-btn active" data-filtro="todos">Todos</button>
                <button class="filtro-btn" data-filtro="pendiente">Pendientes</button>
                <button class="filtro-btn" data-filtro="procesando">Procesando</button>
                <button class="filtro-btn" data-filtro="enviado">Enviados</button>
                <button class="filtro-btn" data-filtro="entregado">Entregados</button>
                <button class="filtro-btn" data-filtro="cancelado">Cancelados</button>
            </div>

            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="pedido-card">
                    <div class="pedido-header">
                        <div>
                            <h2>Pedido #<?php echo $row['id']; ?></h2>
                            <p>Usuario: <?php echo htmlspecialchars($row['nombre_usuario']); ?></p>
                            <p>Fecha: <?php echo date('d/m/Y H:i', strtotime($row['fecha'])); ?></p>
                        </div>
                        <div class="estado-badge <?php echo 'estado-' . strtolower($row['estado']); ?>">
                            <?php echo htmlspecialchars($row['estado']); ?>
                        </div>
                    </div>
                    <div class="pedido-productos">
                        <?php
                        $productos = json_decode($row['productos'], true);
                        foreach ($productos as $producto):
                        ?>
                            <div class="producto-item">
                                <span><?php echo htmlspecialchars($producto['nombre']); ?></span>
                                <span><?php echo htmlspecialchars($producto['cantidad']); ?> x $<?php echo number_format($producto['precio'], 2, '.', ''); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="pedido-footer">
                        <div class="tipo-compra <?php echo $row['compra_online'] ? 'compra-online' : 'compra-local'; ?>">
                            <?php echo $row['compra_online'] ? 'Compra Online' : 'Compra Local'; ?>
                        </div>
                        <div class="acciones">
                            <a href="ver_pedido.php?id=<?php echo $row['id']; ?>" class="acciones-btn btn-ver">Ver Detalles</a>
                            <?php if ($row['estado'] === 'Pendiente'): ?>
                                <a href="ventas.php?id=<?php echo $row['id']; ?>&estado=Procesando" class="acciones-btn btn-procesar">Procesar</a>
                            <?php elseif ($row['estado'] === 'Procesando'): ?>
                                <a href="ventas.php?id=<?php echo $row['id']; ?>&estado=Enviado" class="acciones-btn btn-enviar">Marcar como Enviado</a>
                            <?php elseif ($row['estado'] === 'Enviado'): ?>
                                <a href="ventas.php?id=<?php echo $row['id']; ?>&estado=Entregado" class="acciones-btn btn-entregar">Marcar como Entregado</a>
                            <?php endif; ?>
                            <?php if ($row['estado'] !== 'Entregado'): ?>
                                <a href="ventas.php?id=<?php echo $row['id']; ?>&estado=Cancelado" class="acciones-btn btn-cancelar">Cancelar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </main>
</body>
</html>

