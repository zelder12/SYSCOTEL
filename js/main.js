function showQuickView(title, description, price, image, section, id) {
    var imagePath = 'img/';
    
    switch (section) {
        case 'Perifericos':
            imagePath += 'perifericos/';
            break;
        case 'Consolas':
            imagePath += 'consolas/';
            break;
        case 'Equipos':
            imagePath += 'equipos/';
            break;
        case 'Audifonos':
            imagePath += 'audifonos/';
            break;
        case 'Celulares':
            imagePath += 'celulares/';
            break;
        case 'Gadgets':
            imagePath += 'gadgets/';
            break;
        case 'Seguridad':
            imagePath += 'seguridad/';
            break;
        case 'Unidades':
            imagePath += 'unidades/';
            break;
        case 'Varios':
            imagePath += 'varios/';
            break;
        default:
            imagePath += section.toLowerCase() + '/';
            break;
    }
    
    var quickViewImage = document.getElementById('quickViewImage');
    quickViewImage.innerHTML = "<img class='dynamic-image' src='" + imagePath + image + "' alt='" + title + "' />";
    document.getElementById('quickViewTitle').innerText = title;
    document.getElementById('quickViewDescription').innerText = description;
    document.getElementById('quickViewPrice').innerText = 'Precio: $' + price;
    document.getElementById('quickViewProductId').value = id;
    document.getElementById('quickView').style.display = 'block';
}

function closeQuickView() {
    document.getElementById('quickView').style.display = 'none';
}

function quickViewAddToCart() {
    const id = document.getElementById('quickViewProductId').value;
    addToCart(id);
}

function addToCart(id) {
    alert('Producto agregado al carrito');
}

