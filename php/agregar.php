<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['nombre'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] !== true) {
    header('Location: ../index.php');
    exit();
}

// Asegurarse de que la sesión esté activa
session_regenerate_id(true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Administrar Productos</title>
  <link rel="shortcut icon" href="../img/syscotel.png" />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    :root {
      --brand: #2c3e50;
      --brand-dark: #1a252f;
      --danger: #dc3545;
      --success: #28a745;
      --bg-light: #f8f9fa;
      --bg-white: #fff;
      --text-dark: #2c3e50;
      --text-light: #6c757d;
      --input-bg: #fff;
      --input-border: #dee2e6;
      --card-shadow: 0 2px 15px rgba(0,0,0,0.1);
      --transition: all 0.3s ease;
    }

    * { 
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Roboto', sans-serif;
      background-color: var(--bg-light);
      color: var(--text-dark);
      line-height: 1.6;
    }

    .navbar {
      background: linear-gradient(135deg, #2c3e50, #1a252f);
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    .navbar h1 {
      color: var(--bg-white);
      margin: 0;
      font-size: 1.5rem;
      font-weight: 600;
    }

    .container {
      max-width: 1200px;
      margin: 2rem auto;
      padding: 0 1rem;
    }

    .card {
      background: var(--bg-white);
      border-radius: 10px;
      box-shadow: var(--card-shadow);
      padding: 2rem;
      margin-bottom: 2rem;
    }

    h2 {
      color: var(--brand);
      margin-bottom: 1.5rem;
      font-size: 1.5rem;
      font-weight: 600;
      border-bottom: 2px solid var(--brand);
      padding-bottom: 0.5rem;
    }

    form {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 1.5rem;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }

    .form-group label {
      font-weight: 500;
      color: var(--text-dark);
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
      padding: 0.75rem;
      font-size: 1rem;
      border: 1px solid var(--input-border);
      border-radius: 8px;
      background: var(--input-bg);
      color: var(--text-dark);
      transition: var(--transition);
    }

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
      outline: none;
      border-color: var(--brand);
      box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.1);
    }

    .form-group textarea {
      resize: none;
      height: 100px;
      overflow-y: auto;
    }

    .checkbox-group {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 0;
    }

    .checkbox-group input[type="checkbox"] {
      width: 18px;
      height: 18px;
      cursor: pointer;
    }

    .checkbox-group label {
      cursor: pointer;
      user-select: none;
    }

    .image-preview {
      margin-top: 1rem;
      text-align: center;
      padding: 1rem;
      border: 2px dashed var(--input-border);
      border-radius: 10px;
      transition: var(--transition);
      background: var(--bg-light);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 200px;
    }

    .image-preview img {
      max-width: 100%;
      max-height: 200px;
      object-fit: contain;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .preview-placeholder {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.5rem;
      color: var(--text-light);
      padding: 1rem;
    }

    .preview-placeholder i {
      font-size: 2rem;
      color: var(--brand);
    }

    .preview-placeholder p {
      margin: 0;
      font-size: 0.9rem;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 0.75rem 1.5rem;
      font-size: 1rem;
      font-weight: 500;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: var(--transition);
      text-decoration: none;
      gap: 0.5rem;
    }

    .btn-primary {
      background: var(--brand);
      color: white;
    }

    .btn-primary:hover {
      background: var(--brand-dark);
      transform: translateY(-2px);
    }

    .btn-danger {
      background: var(--danger);
      color: white;
    }

    .btn-danger:hover {
      background: #c82333;
      transform: translateY(-2px);
    }

    .table-container {
      overflow-x: auto;
      margin-top: 1rem;
    }

    table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      background: var(--bg-white);
      border-radius: 8px;
      overflow: hidden;
      box-shadow: var(--card-shadow);
      table-layout: fixed;
    }

    th, td {
      padding: 1rem;
      text-align: left;
      border-bottom: 1px solid var(--input-border);
      vertical-align: middle;
      height: auto;
      min-height: 85px;
    }

    th {
      background: var(--brand);
      color: white;
      font-weight: 500;
      text-transform: uppercase;
      font-size: 0.9rem;
    }

    tr:last-child td {
      border-bottom: none;
    }

    tr:hover {
      background: rgba(30, 136, 229, 0.05);
    }

    .action-cell {
      display: flex;
      gap: 0.5rem;
      justify-content: center;
      align-items: center;
      min-width: 90px;
      white-space: nowrap;
      padding: 0;
      height: auto;
      min-height: 92.8px;
      box-sizing: border-box;
    }

    .action-cell .btn {
      padding: 0.5rem;
      font-size: 0.9rem;
      width: 32px;
      height: auto;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      flex-shrink: 0;
    }

    .action-cell .btn i {
      font-size: 0.9rem;
      line-height: 1;
    }

    .action-cell .btn-primary {
      background: var(--brand);
      color: white;
    }

    .action-cell .btn-primary:hover {
      background: var(--brand-dark);
    }

    .action-cell .btn-danger {
      background: var(--danger);
      color: white;
    }

    .action-cell .btn-danger:hover {
      background: #c82333;
    }

    .search-container {
      margin-bottom: 1.5rem;
    }

    .search-container input {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid var(--input-border);
      border-radius: 8px;
      font-size: 1rem;
      transition: var(--transition);
    }

    .search-container input:focus {
      outline: none;
      border-color: var(--brand);
      box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.1);
    }

    .toggle-btn {
      background: var(--brand);
      color: white;
      border: none;
      padding: 0.75rem 1.5rem;
      border-radius: 8px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 1rem;
      transition: var(--transition);
    }

    .toggle-btn:hover {
      background: var(--brand-dark);
      transform: translateY(-2px);
    }

    .toggle-btn i {
      transition: transform 0.3s ease;
    }

    .toggle-btn:hover i {
      transform: rotate(180deg);
    }

    @media (max-width: 768px) {
      .container {
        padding: 1rem;
      }

      form {
        grid-template-columns: 1fr;
      }

      .image-preview {
        grid-column: span 1;
      }

      .action-cell {
        flex-direction: column;
      }

      .action-cell .btn {
        width: 100%;
      }
    }

    /* Estilos para el checkbox de producto popular */
    .form-group.checkbox-container {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px;
      background: transparent;
      border: none;
      margin: 0;
    }

    .form-group.checkbox-container input[type="checkbox"] {
      width: 16px;
      height: 16px;
      cursor: pointer;
      accent-color: var(--brand);
      margin: 0;
    }

    .form-group.checkbox-container label {
      cursor: pointer;
      user-select: none;
      font-weight: 500;
      color: var(--text-dark);
      margin: 0;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .popular-badge {
      display: inline-block;
      padding: 2px 6px;
      background: var(--brand);
      color: white;
      border-radius: 4px;
      font-size: 0.75rem;
      font-weight: 500;
    }

    .not-popular-text {
      color: var(--text-light);
      font-size: 0.9rem;
    }

    /* Estilos para la tabla de productos */
    .product-image {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 6px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      display: block;
      margin: auto;
    }

    .product-name {
      font-weight: 500;
      color: var(--text-dark);
    }

    .product-price {
      color: var(--brand);
      font-weight: 600;
    }

    .product-description {
      color: var(--text-light);
      font-size: 0.9rem;
      max-width: 100%;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      display: inline-block;
    }

    /* Ajustes para la celda de popular en la tabla */
    td .form-group.checkbox-container {
      padding: 0;
      margin: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 100%;
      height: 100%;
      box-sizing: border-box;
      text-align: center;
    }

    td .form-group.checkbox-container input[type="checkbox"] {
        flex-shrink: 0;
        margin: 0;
    }

    td .form-group.checkbox-container label {
        display: flex;
        align-items: center;
        gap: 4px;
        margin: 0;
    }

    .product-form {
      display: flex;
      flex-direction: column;
      gap: 2rem;
    }

    .form-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 2rem;
      margin-bottom: 2rem;
    }

    .form-section {
      background: var(--bg-white);
      padding: 1.5rem;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .form-section h3 {
      color: var(--brand);
      font-size: 1.2rem;
      margin-bottom: 1.5rem;
      padding-bottom: 0.5rem;
      border-bottom: 2px solid var(--brand);
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-group:last-child {
      margin-bottom: 0;
    }

    .form-group label {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 0.5rem;
      font-weight: 500;
      color: var(--text-dark);
    }

    .form-group label i {
      color: var(--brand);
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid var(--input-border);
      border-radius: 8px;
      font-size: 1rem;
      transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
      outline: none;
      border-color: var(--brand);
      box-shadow: 0 0 0 3px rgba(44, 62, 80, 0.1);
    }

    .image-preview {
      margin-top: 1rem;
      border: 2px dashed var(--input-border);
      border-radius: 10px;
      padding: 1.5rem;
      text-align: center;
      background: var(--bg-light);
      transition: all 0.3s ease;
    }

    .image-preview:hover {
      border-color: var(--brand);
    }

    .preview-placeholder {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 1rem;
      color: var(--text-light);
    }

    .preview-placeholder i {
      font-size: 2.5rem;
      color: var(--brand);
    }

    .form-actions {
      display: flex;
      gap: 1rem;
      justify-content: flex-end;
      margin-top: 2rem;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.75rem 1.5rem;
      border-radius: 8px;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .btn-primary {
      background: var(--brand);
      color: white;
    }

    .btn-primary:hover {
      background: var(--brand-dark);
      transform: translateY(-2px);
    }

    .btn-secondary {
      background: var(--text-light);
      color: white;
    }

    .btn-secondary:hover {
      background: var(--text-dark);
      transform: translateY(-2px);
    }

    @media (max-width: 768px) {
      .form-grid {
        grid-template-columns: 1fr;
      }
      
      .form-actions {
        flex-direction: column;
      }
      
      .form-actions .btn {
        width: 100%;
      }
    }

    /* Ajustar el ancho de las columnas */
    th:nth-child(1), td:nth-child(1) { width: 4%; } /* ID */
    th:nth-child(2), td:nth-child(2) { width: 8%; } /* Imagen */
    th:nth-child(3), td:nth-child(3) { width: 15%; } /* Nombre */
    th:nth-child(4), td:nth-child(4) { width: 10%; } /* Precio */
    th:nth-child(5), td:nth-child(5) { width: 20%; } /* Descripción */
    th:nth-child(6), td:nth-child(6) { width: 10%; } /* Categoría */
    th:nth-child(7), td:nth-child(7) { width: 10%; } /* Apartado */
    th:nth-child(8), td:nth-child(8) { width: 5%; } /* Stock */
    th:nth-child(9), td:nth-child(9) { width: 8%; } /* Popular */
    th:nth-child(10), td:nth-child(10) { width: 10%; } /* Acciones */

    td:nth-child(2) {
      position: relative;
    }
  </style>
</head>
<body>

  <nav class="navbar">
    <h1>Panel de Administración</h1>
    <button class="btn btn-primary" onclick="location.href='../admin.php'" style="display: flex; align-items: center; gap: 8px; padding: 10px 20px; font-weight: 500; transition: all 0.3s ease; background: transparent; border: 2px solid #fff;">
      <i class="fas fa-arrow-left"></i>
      <span>Regresar</span>
    </button>
  </nav>

  <main class="container">
    <section class="card">
      <h2><i class="fas fa-plus-circle"></i> Agregar Nuevo Producto</h2>
      <form id="productForm" enctype="multipart/form-data" class="product-form">
        <div class="form-grid">
          <div class="form-section">
            <h3>Información Básica</h3>
            <div class="form-group">
              <label for="nombre"><i class="fas fa-tag"></i> Nombre del Producto</label>
              <input type="text" id="nombre" name="nombre" required placeholder="Ingrese el nombre del producto">
            </div>
            <div class="form-group">
              <label for="precio"><i class="fas fa-dollar-sign"></i> Precio</label>
              <input type="number" id="precio" name="precio" step="0.01" required placeholder="0.00">
            </div>
            <div class="form-group">
              <label for="stock"><i class="fas fa-boxes"></i> Stock</label>
              <input type="number" id="stock" name="stock" min="0" required placeholder="0">
            </div>
            <div class="form-group">
              <label for="descripcion"><i class="fas fa-align-left"></i> Descripción</label>
              <textarea id="descripcion" name="descripcion" placeholder="Ingrese una descripción detallada del producto"></textarea>
            </div>
          </div>

          <div class="form-section">
            <h3>Categorización</h3>
            <div class="form-group">
              <label for="apartado"><i class="fas fa-folder"></i> Apartado</label>
              <select id="apartado" name="apartado" required>
                <option value="">Seleccione un apartado</option>
                <option value="Gaming">Gaming</option>
                <option value="Moviles">Móviles</option>
                <option value="Varios">Varios</option>
              </select>
            </div>
            <div class="form-group">
              <label for="seccion"><i class="fas fa-tags"></i> Categoría</label>
              <select id="seccion" name="seccion" required>
                <option value="">Seleccione una categoría</option>
              </select>
            </div>
            <div class="form-group checkbox-container">
              <input type="checkbox" id="es_popular" name="es_popular" value="1">
              <label for="es_popular">
                <i class="fas fa-star"></i>
                Marcar como producto popular
              </label>
            </div>
          </div>

          <div class="form-section">
            <h3>Imagen del Producto</h3>
            <div class="form-group">
              <label for="imagen"><i class="fas fa-image"></i> Seleccionar Imagen</label>
              <input type="file" id="imagen" name="imagen" accept="image/*" required>
              <div class="image-preview">
                <img id="preview" src="#" alt="Vista previa" style="display: none;">
                <div class="preview-placeholder">
                  <i class="fas fa-cloud-upload-alt"></i>
                  <p>Vista previa de la imagen</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Agregar Producto
          </button>
          <button type="reset" class="btn btn-secondary">
            <i class="fas fa-undo"></i>
            Limpiar Formulario
          </button>
        </div>
      </form>
    </section>

    <section class="card">
      <h2><i class="fas fa-list"></i> Lista de Productos</h2>
      <div class="import-section" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center;">
        <button class="btn btn-primary" id="downloadTemplate">
          <i class="fas fa-download"></i>
          Descargar Plantilla Excel
        </button>
        <form id="importForm" style="display: flex; gap: 10px; align-items: center;">
          <input type="file" id="excelFile" accept=".xlsx, .xls" style="display: none;">
          <button type="button" class="btn btn-primary" id="selectFile">
            <i class="fas fa-file-excel"></i>
            Seleccionar Archivo Excel
          </button>
          <button type="submit" class="btn btn-success" id="uploadFile" style="display: none;">
            <i class="fas fa-upload"></i>
            Subir Productos
          </button>
        </form>
      </div>
      <div class="search-container">
        <div class="filters-container" style="display: flex; gap: 15px; margin-bottom: 15px; flex-wrap: wrap;">
          <div class="filter-group">
            <label for="filterApartado" style="display: block; margin-bottom: 5px; font-weight: 500;">Filtrar por Apartado:</label>
            <select id="filterApartado" class="filter-select" style="padding: 8px; border-radius: 5px; border: 1px solid #dee2e6;">
              <option value="">Todos los apartados</option>
              <option value="Gaming">Gaming</option>
              <option value="Moviles">Móviles</option>
              <option value="Varios">Varios</option>
            </select>
          </div>
          <div class="filter-group">
            <label for="filterSeccion" style="display: block; margin-bottom: 5px; font-weight: 500;">Filtrar por Categoría:</label>
            <select id="filterSeccion" class="filter-select" style="padding: 8px; border-radius: 5px; border: 1px solid #dee2e6;">
              <option value="">Todas las categorías</option>
            </select>
          </div>
          <div class="filter-group">
            <label for="filterStock" style="display: block; margin-bottom: 5px; font-weight: 500;">Filtrar por Stock:</label>
            <select id="filterStock" class="filter-select" style="padding: 8px; border-radius: 5px; border: 1px solid #dee2e6;">
              <option value="">Todos</option>
              <option value="con_stock">Con stock</option>
              <option value="sin_stock">Sin stock</option>
            </select>
          </div>
          <div class="filter-group">
            <label for="filterPopular" style="display: block; margin-bottom: 5px; font-weight: 500;">Filtrar por Popularidad:</label>
            <select id="filterPopular" class="filter-select" style="padding: 8px; border-radius: 5px; border: 1px solid #dee2e6;">
              <option value="">Todos</option>
              <option value="popular">Populares</option>
              <option value="no_popular">No populares</option>
            </select>
          </div>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
          <i class="fas fa-search"></i>
          <input type="text" id="searchInput" placeholder="Buscar productos por nombre, categoría o descripción...">
        </div>
      </div>
      <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Descripción</th>
                <th>Categoría</th>
                <th>Apartado</th>
                <th>Stock</th>
                <th>Popular</th>
                <th>Acciones</th>
              </tr>
            </thead>
          <tbody id="productList">
            <!-- Los productos se cargarán aquí dinámicamente -->
            </tbody>
          </table>
      </div>
      <div style="margin-top: 20px; text-align: right;">
        <button class="btn btn-primary" id="downloadInventory">
          <i class="fas fa-file-pdf"></i>
          Descargar Inventario
        </button>
      </div>
    </section>
  </main>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('productForm');
    const searchInput = document.getElementById('searchInput');
    const productList = document.getElementById('productList');
    const seccionSelect = document.getElementById('seccion');
    const apartadoSelect = document.getElementById('apartado');

    // Función para cargar los productos
    function cargarProductos() {
      fetch('listar_productos.php')
        .then(response => {
          if (!response.ok) {
            throw new Error('Error en la respuesta del servidor: ' + response.status);
          }
          return response.text().then(text => {
            try {
              return JSON.parse(text);
            } catch (e) {
              console.error('Error al parsear JSON:', e);
              console.error('Respuesta recibida:', text);
              throw new Error('Respuesta no válida del servidor');
            }
          });
        })
        .then(productos => {
          const tbody = document.getElementById('productList');
          tbody.innerHTML = '';
          
          if (productos.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" style="text-align: center;">No hay productos disponibles</td></tr>';
            return;
          }

          productos.forEach(producto => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
              <td>${producto.id}</td>
              <td>
                <img src="${producto.imagen}" alt="${producto.nombre}" class="product-image">
                ${producto.stock == 0 ? '<img src="../img/out_of_stock.png" alt="Sin stock" style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:contain;background-color:rgba(255,255,255,0.7);z-index:10;">' : ''}
              </td>
              <td>
                <span class="product-name">${producto.nombre.length > 12 ? producto.nombre.substring(0, 12) + '...' : producto.nombre}</span>
              </td>
              <td>
                <span class="product-price">$${producto.precio}</span>
              </td>
              <td>
                <span class="product-description">${producto.descripcion}</span>
              </td>
              <td>${producto.seccion}</td>
              <td>${producto.apartado}</td>
              <td>
                <span class="product-stock" style="color: ${producto.stock > 0 ? '#28a745' : '#dc3545'}">
                  ${producto.stock}
                </span>
              </td>
              <td>
                <div class="form-group checkbox-container">
                  <input type="checkbox" 
                         id="popular-${producto.id}"
                         class="popular-checkbox" 
                         data-id="${producto.id}" 
                         ${parseInt(producto.es_popular) === 1 ? 'checked' : ''}>
                  <label for="popular-${producto.id}">
                    <span class="popular-badge" style="display: ${parseInt(producto.es_popular) === 1 ? 'inline-block' : 'none'}">Popular</span>
                  </label>
                </div>
              </td>
              <td class="action-cell">
                <a href="modificar.php?id=${producto.id}" class="btn btn-primary" title="Editar">
                  <i class="fas fa-pencil-alt"></i>
                </a>
                <button class="btn btn-danger" data-id="${producto.id}" title="Eliminar">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            `;
            tbody.appendChild(tr);
          });
        })
        .catch(error => {
          console.error('Error:', error);
          const tbody = document.getElementById('productList');
          tbody.innerHTML = '<tr><td colspan="10" style="text-align: center;">Error al cargar productos: ' + error.message + '</td></tr>';
        });
    }

    // Cargar productos al iniciar
    cargarProductos();

    // Manejar búsqueda de productos
    searchInput.addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase();
      const rows = productList.getElementsByTagName('tr');

      Array.from(rows).forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
      });
  });

    // Manejar envío del formulario
    form.addEventListener('submit', function(e) {
    e.preventDefault();
      const formData = new FormData(this);

      // Primero verificar si el producto ya existe
      fetch('verificar_producto.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.existe) {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Ya existe un producto con el mismo nombre en esta sección y categoría',
            confirmButtonColor: '#2c3e50'
          });
          return;
        }

        // Si no existe, proceder con la creación
        fetch('agregar_producto.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            Swal.fire({
              icon: 'success',
              title: '¡Éxito!',
              text: 'Producto agregado correctamente',
              confirmButtonColor: '#2c3e50',
              timer: 2000,
              showConfirmButton: false
            });
            form.reset();
            document.getElementById('preview').style.display = 'none';
            document.querySelector('.preview-placeholder').style.display = 'flex';
            cargarProductos();
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: data.message || 'Hubo un error al agregar el producto',
              confirmButtonColor: '#2c3e50'
            });
          }
        })
        .catch(error => {
          console.error('Error:', error);
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al agregar el producto',
            confirmButtonColor: '#2c3e50'
          });
        });
      })
      .catch(error => {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Error al verificar el producto',
          confirmButtonColor: '#2c3e50'
        });
      });
  });

    // Limpiar vista previa al resetear el formulario
    form.addEventListener('reset', function() {
      const preview = document.getElementById('preview');
      const placeholder = document.querySelector('.preview-placeholder');
      preview.style.display = 'none';
      preview.src = '#';
      placeholder.style.display = 'flex';
    });

    // Vista previa de imagen
    document.getElementById('imagen').addEventListener('change', function() {
      const preview = document.getElementById('preview');
      const placeholder = document.querySelector('.preview-placeholder');
      const file = this.files[0];
      
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          preview.src = e.target.result;
          preview.style.display = 'block';
          placeholder.style.display = 'none';
        }
        reader.readAsDataURL(file);
      } else {
        preview.style.display = 'none';
        preview.src = '#';
        placeholder.style.display = 'flex';
      }
    });

    // Actualizar secciones según el apartado
    apartadoSelect.addEventListener('change', function() {
      const apartado = this.value;
      seccionSelect.innerHTML = '<option value="">Seleccione una categoría</option>';

      switch(apartado) {
        case 'Gaming':
          agregarOpciones(['Perifericos', 'Consolas', 'Equipos']);
          break;
        case 'Moviles':
          agregarOpciones(['Audifonos', 'Celulares', 'Gadgets']);
          break;
        case 'Varios':
          agregarOpciones(['Seguridad', 'Unidades', 'Varios']);
          break;
      }
    });

    function agregarOpciones(opciones) {
      opciones.forEach(opcion => {
        const option = document.createElement('option');
        option.value = opcion;
        option.textContent = opcion;
        seccionSelect.appendChild(option);
      });
    }

    // Manejar eliminación de productos
    document.addEventListener('click', function(e) {
      if (e.target.classList.contains('btn-danger') || e.target.closest('.btn-danger')) {
        const deleteButton = e.target.classList.contains('btn-danger') ? e.target : e.target.closest('.btn-danger');
        const id = deleteButton.dataset.id;
        
        if (!id) {
          console.error('Error: No se encontró el ID del producto a eliminar');
          return;
        }
        
        Swal.fire({
          title: '¿Estás seguro?',
          text: 'Esta acción no se puede deshacer',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#dc3545',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Sí, eliminar',
          cancelButtonText: 'Cancelar'
        }).then((result) => {
          if (result.isConfirmed) {
            // Mostrar indicador de carga
            Swal.fire({
              title: 'Eliminando...',
              text: 'Procesando tu solicitud',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });
            
            // Primero obtener la información del producto
            fetch('obtener_producto.php?id=' + id)
              .then(response => response.json())
              .then(producto => {
                // Luego eliminar el producto
                return fetch('productos_manager.php', {
                  method: 'POST',
                  headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                  },
                  body: `action=delete&id=${id}&imagen=${producto.imagen}&apartado=${producto.apartado}&seccion=${producto.seccion}`
                });
              })
              .then(response => {
                if (!response.ok) {
                  throw new Error('Error en la respuesta del servidor: ' + response.status);
                }
                return response.json();
              })
              .then(data => {
                if (data.status === 'success') {
                  Swal.fire({
                    icon: 'success',
                    title: '¡Eliminado!',
                    text: 'El producto ha sido eliminado correctamente',
                    timer: 1500,
                    showConfirmButton: false
                  });
                  cargarProductos(); // Recargar la lista de productos
                } else {
                  Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Hubo un error al eliminar el producto'
                  });
                }
              })
              .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                  icon: 'error',
                  title: 'Error',
                  text: 'Hubo un error al procesar la solicitud: ' + error.message
                });
              });
          }
        });
      }
    });

    // Manejar actualización de popular
    document.addEventListener('change', function(e) {
      if (e.target.classList.contains('popular-checkbox')) {
        const id = e.target.dataset.id;
        const esPopular = e.target.checked ? 1 : 0;
        const label = e.target.nextElementSibling;
        const badge = label.querySelector('.popular-badge');
        
        console.log('Actualizando producto ID:', id, 'Popular:', esPopular);

        fetch('actualizar_popular.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `id=${id}&es_popular=${esPopular}`
        })
        .then(response => response.json())
        .then(data => {
          console.log('Respuesta del servidor:', data);
          if (data.status === 'success') {
            badge.style.display = esPopular ? 'inline-block' : 'none';
            console.log('Actualización exitosa');
          } else {
            console.error('Error:', data.message);
            alert('Error: ' + data.message);
            e.target.checked = !e.target.checked;
          }
        })
        .catch(error => {
          console.error('Error en la petición:', error);
          alert('Error de conexión');
          e.target.checked = !e.target.checked;
        });
      }
    });

    // Funcionalidad para importación masiva de productos
    document.getElementById('downloadTemplate').addEventListener('click', function() {
      window.location.href = 'descargar_plantilla.php';
    });
  
    document.getElementById('selectFile').addEventListener('click', function() {
      document.getElementById('excelFile').click();
    });
  
    document.getElementById('excelFile').addEventListener('change', function() {
      const uploadButton = document.getElementById('uploadFile');
      if (this.files.length > 0) {
        uploadButton.style.display = 'inline-flex';
      } else {
        uploadButton.style.display = 'none';
      }
    });
  
    document.getElementById('importForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData();
      const fileInput = document.getElementById('excelFile');
      
      if (fileInput.files.length === 0) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Por favor, seleccione un archivo Excel'
        });
        return;
      }

      formData.append('excelFile', fileInput.files[0]);

      // Mostrar indicador de carga
      Swal.fire({
        title: 'Procesando archivo...',
        text: 'Por favor espere mientras se importan los productos',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      fetch('importar_productos.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        console.log('Respuesta del servidor:', data); // Para depuración
        
        let htmlContent = '';
        let title = '';
        let icon = '';
        
        // Filtrar errores reales (excluir los que contienen "ya existe" o "Omitido")
        const erroresReales = data.errores_detalle ? data.errores_detalle.filter(error => 
          !error.includes('ya existe') && !error.includes('Omitido')
        ) : [];

        // Resumen básico para todos los casos
        const resumenBasico = `
          <div class="summary" style="margin-bottom: 15px;">
            <p style="font-size: 16px;"><strong>Resumen de la importación:</strong></p>
            <p>✅ Productos importados: ${data.importados}</p>
            <p>⚠️ Productos omitidos: ${data.omitidos_existentes}</p>
            <p>❌ Errores encontrados: ${erroresReales.length}</p>
          </div>
        `;

        if (data.status === 'warning') {
          title = 'Importación con Advertencias';
          icon = 'warning';
          htmlContent = `
            <div class="import-results">
              ${resumenBasico}
              ${erroresReales.length > 0 ? `
                <div class="error-details">
                  <h4>Detalles de los errores:</h4>
                  <div class="error-list">
                    ${erroresReales.map(error => `<p>${error}</p>`).join('')}
                  </div>
                </div>
              ` : ''}
            </div>
          `;
        } else if (data.status === 'success') {
          title = 'Importación Exitosa';
          icon = 'success';
          htmlContent = `
            <div class="import-results">
              ${resumenBasico}
            </div>
          `;
        } else {
          title = 'Error en la Importación';
          icon = 'error';
          htmlContent = `
            <div class="import-results">
              ${resumenBasico}
              <div class="error-details">
                <p>${data.message || 'Ocurrió un error durante la importación'}</p>
                ${erroresReales.length > 0 ? `
                  <div class="error-list">
                    ${erroresReales.map(error => `<p>${error}</p>`).join('')}
                  </div>
                ` : ''}
              </div>
            </div>
          `;
        }

        Swal.fire({
          icon: icon,
          title: title,
          html: htmlContent,
          width: '600px'
        });

        if (data.status === 'success' || data.status === 'warning') {
          cargarProductos();
        }
      })
      .catch(error => {
        console.error('Error en importación:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Ocurrió un error al procesar el archivo: ' + error.message
        });
      })
      .finally(() => {
        // Limpiar el formulario
        document.getElementById('importForm').reset();
        document.getElementById('uploadFile').style.display = 'none';
      });
    });

    // Agregar manejador para el botón de descarga de inventario
    document.getElementById('downloadInventory').addEventListener('click', function() {
      window.location.href = 'generar_inventario.php';
    });

    // Manejar los filtros
    const filterApartado = document.getElementById('filterApartado');
    const filterSeccion = document.getElementById('filterSeccion');
    const filterStock = document.getElementById('filterStock');
    const filterPopular = document.getElementById('filterPopular');

    // Actualizar categorías según el apartado seleccionado
    filterApartado.addEventListener('change', function() {
      const apartado = this.value;
      filterSeccion.innerHTML = '<option value="">Todas las categorías</option>';

      if (apartado) {
        switch(apartado) {
          case 'Gaming':
            agregarOpcionesFiltro(['Perifericos', 'Consolas', 'Equipos']);
            break;
          case 'Moviles':
            agregarOpcionesFiltro(['Audifonos', 'Celulares', 'Gadgets']);
            break;
          case 'Varios':
            agregarOpcionesFiltro(['Seguridad', 'Unidades', 'Varios']);
            break;
        }
      }
      aplicarFiltros();
    });

    function agregarOpcionesFiltro(opciones) {
      opciones.forEach(opcion => {
        const option = document.createElement('option');
        option.value = opcion;
        option.textContent = opcion;
        filterSeccion.appendChild(option);
      });
    }

    // Función para aplicar todos los filtros
    function aplicarFiltros() {
      const apartado = filterApartado.value.toLowerCase();
      const seccion = filterSeccion.value.toLowerCase();
      const stock = filterStock.value;
      const popular = filterPopular.value;
      const searchTerm = searchInput.value.toLowerCase();

      const rows = productList.getElementsByTagName('tr');

      Array.from(rows).forEach(row => {
        const rowApartado = row.cells[6].textContent.toLowerCase();
        const rowSeccion = row.cells[5].textContent.toLowerCase();
        const rowStock = parseInt(row.cells[7].textContent);
        const rowPopular = row.cells[8].querySelector('.popular-checkbox').checked;
        const rowText = row.textContent.toLowerCase();

        const matchApartado = !apartado || rowApartado === apartado;
        const matchSeccion = !seccion || rowSeccion === seccion;
        const matchStock = !stock || 
          (stock === 'con_stock' && rowStock > 0) || 
          (stock === 'sin_stock' && rowStock === 0);
        const matchPopular = !popular || 
          (popular === 'popular' && rowPopular) || 
          (popular === 'no_popular' && !rowPopular);
        const matchSearch = !searchTerm || rowText.includes(searchTerm);

        row.style.display = matchApartado && matchSeccion && matchStock && matchPopular && matchSearch ? '' : 'none';
      });
    }

    // Agregar event listeners para todos los filtros
    filterSeccion.addEventListener('change', aplicarFiltros);
    filterStock.addEventListener('change', aplicarFiltros);
    filterPopular.addEventListener('change', aplicarFiltros);
    searchInput.addEventListener('input', aplicarFiltros);

    // Aplicar filtros iniciales
    aplicarFiltros();
  });
</script>
</body>
</html>







