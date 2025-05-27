// Variables para almacenar el estado de los filtros
let filtrosActivos = {
  apartado: '',
  seccion: '',
  stock: '',
  popular: '',
  busqueda: ''
};

// Función para guardar el estado actual de los filtros
function guardarFiltrosActuales() {
  filtrosActivos = {
    apartado: document.getElementById('filterApartado').value,
    seccion: document.getElementById('filterSeccion').value,
    stock: document.getElementById('filterStock').value,
    popular: document.getElementById('filterPopular').value,
    busqueda: document.getElementById('searchInput').value
  };
  
  // También guardar en localStorage como respaldo
  localStorage.setItem('adminFiltros', JSON.stringify(filtrosActivos));
  
  console.log('Filtros guardados:', filtrosActivos);
}

// Función para restaurar los filtros guardados
function restaurarFiltrosGuardados() {
  // Intentar recuperar de localStorage si es necesario
  if (Object.values(filtrosActivos).every(v => v === '')) {
    const savedFilters = localStorage.getItem('adminFiltros');
    if (savedFilters) {
      try {
        filtrosActivos = JSON.parse(savedFilters);
      } catch (e) {
        console.error('Error al parsear filtros guardados:', e);
      }
    }
  }
  
  document.getElementById('filterApartado').value = filtrosActivos.apartado;
  document.getElementById('filterSeccion').value = filtrosActivos.seccion;
  document.getElementById('filterStock').value = filtrosActivos.stock;
  document.getElementById('filterPopular').value = filtrosActivos.popular;
  document.getElementById('searchInput').value = filtrosActivos.busqueda;
  
  // Si se cambió el apartado, actualizar las opciones de sección
  if (filtrosActivos.apartado) {
    actualizarOpcionesFiltroSeccion(filtrosActivos.apartado);
    
    // Asegurarse de que la sección seleccionada se mantenga después de actualizar las opciones
    setTimeout(() => {
      document.getElementById('filterSeccion').value = filtrosActivos.seccion;
    }, 50);
  }
  
  console.log('Filtros restaurados:', filtrosActivos);
}

// Función para aplicar todos los filtros
function aplicarFiltros() {
  // Guardar el estado actual de los filtros antes de aplicarlos
  guardarFiltrosActuales();
  
  const apartado = document.getElementById('filterApartado').value.toLowerCase();
  const seccion = document.getElementById('filterSeccion').value.toLowerCase();
  const stock = document.getElementById('filterStock').value;
  const popular = document.getElementById('filterPopular').value;
  const searchTerm = document.getElementById('searchInput').value.toLowerCase();

  const rows = document.getElementById('productList').getElementsByTagName('tr');

  Array.from(rows).forEach(row => {
    // Si no hay celdas (como en filas de mensaje), saltar
    if (row.cells.length <= 1) return;
    
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

// Función para cargar los productos con filtros aplicados
function cargarProductos(mantenerFiltros = true) {
  // Si debemos mantener los filtros, guardarlos antes de recargar
  if (mantenerFiltros) {
    guardarFiltrosActuales();
  } else {
    // Si no mantenemos filtros, limpiar los guardados
    filtrosActivos = {
      apartado: '',
      seccion: '',
      stock: '',
      popular: '',
      busqueda: ''
    };
    localStorage.removeItem('adminFiltros');
  }

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

      // Restaurar los filtros después de cargar los productos
      if (mantenerFiltros) {
        setTimeout(() => {
          restaurarFiltrosGuardados();
          setTimeout(aplicarFiltros, 100); // Aplicar filtros después de restaurarlos
        }, 100);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      const tbody = document.getElementById('productList');
      tbody.innerHTML = '<tr><td colspan="10" style="text-align: center;">Error al cargar productos: ' + error.message + '</td></tr>';
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
    
    // Guardar el estado actual de los filtros antes de mostrar el diálogo
    guardarFiltrosActuales();
    
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
              cargarProductos(true); // Recargar manteniendo los filtros
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

// Modificar el evento de cambio para el filtro de apartado
document.addEventListener('DOMContentLoaded', function() {
  const filterApartado = document.getElementById('filterApartado');
  const filterSeccion = document.getElementById('filterSeccion');
  const filterStock = document.getElementById('filterStock');
  const filterPopular = document.getElementById('filterPopular');
  const searchInput = document.getElementById('searchInput');
  
  // Cargar productos al iniciar
  cargarProductos(false);
  
  // Agregar event listeners para todos los filtros
  filterApartado.addEventListener('change', function() {
    const apartado = this.value;
    actualizarOpcionesFiltroSeccion(apartado);
    setTimeout(aplicarFiltros, 100);
  });
  
  filterSeccion.addEventListener('change', aplicarFiltros);
  filterStock.addEventListener('change', aplicarFiltros);
  filterPopular.addEventListener('change', aplicarFiltros);
  
  // Usar debounce para el campo de búsqueda para evitar demasiadas actualizaciones
  let searchTimeout;
  searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(aplicarFiltros, 300);
  });
  
  // Resto del código de inicialización...
});

// Manejar envío del formulario de importación
document.getElementById('importForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  // Guardar filtros antes de importar
  guardarFiltrosActuales();
  
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

    if (data.status === 'warning') {
      title = 'Importación con Advertencias';
      icon = 'warning';
      htmlContent = `
        <div class="import-results">
          <div class="summary">
            <p><strong>Resumen de la importación:</strong></p>
            <p>✅ Productos importados: ${data.importados}</p>
            <p>⚠️ Productos omitidos: ${data.omitidos_existentes}</p>
            <p>❌ Errores encontrados: ${data.errores}</p>
          </div>
          ${data.errores_detalle && data.errores_detalle.length > 0 ? `
            <div class="error-details">
              <h4>Detalles de los errores:</h4>
              <div class="error-list">
                ${data.errores_detalle.map(error => `<p>${error}</p>`).join('')}
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
          <div class="summary">
            <p><strong>Resumen de la importación:</strong></p>
            <p>✅ Productos importados: ${data.importados}</p>
            ${data.omitidos_existentes > 0 ? `<p>⚠️ Productos omitidos: ${data.omitidos_existentes}</p>` : ''}
          </div>
        </div>
      `;
    } else {
      title = 'Error en la Importación';
      icon = 'error';
      htmlContent = `
        <div class="import-results">
          <div class="error-details">
            <p>${data.message || 'Ocurrió un error durante la importación'}</p>
            ${data.errores_detalle && data.errores_detalle.length > 0 ? `
              <div class="error-list">
                ${data.errores_detalle.map(error => `<p>${error}</p>`).join('')}
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
      width: '800px',
      customClass: {
        container: 'swal-wide',
        popup: 'swal2-popup',
        content: 'swal2-content',
        confirmButton: 'swal2-confirm'
      }
    });

    if (data.status === 'success' || data.status === 'warning') {
      cargarProductos(true); // Recargar manteniendo los filtros
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

// Manejar envío del formulario de agregar producto
document.getElementById('productForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);

  // Guardar filtros antes de enviar
  guardarFiltrosActuales();

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
        this.reset();
        document.getElementById('preview').style.display = 'none';
        document.querySelector('.preview-placeholder').style.display = 'flex';
        cargarProductos(true); // Recargar manteniendo los filtros
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
        // No recargamos los productos aquí para no perder el estado de la interfaz
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

// Manejar búsqueda de productos
document.getElementById('searchInput').addEventListener('input', function() {
  aplicarFiltros();
});

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
  // Inicializar variables
  const form = document.getElementById('productForm');
  const filterApartado = document.getElementById('filterApartado');
  const filterSeccion = document.getElementById('filterSeccion');
  const filterStock = document.getElementById('filterStock');
  const filterPopular = document.getElementById('filterPopular');
  const searchInput = document.getElementById('searchInput');
  
  // Cargar productos al iniciar
  cargarProductos(false);
  
  // Agregar event listeners para todos los filtros
  filterApartado.addEventListener('change', aplicarFiltros);
  filterSeccion.addEventListener('change', aplicarFiltros);
  filterStock.addEventListener('change', aplicarFiltros);
  filterPopular.addEventListener('change', aplicarFiltros);
  searchInput.addEventListener('input', aplicarFiltros);
  
  // Resto del código de inicialización...
}); 
