const direccionInput = document.getElementById('direccion');
const suggestionsDiv = document.getElementById('address-suggestions');

direccionInput.addEventListener('input', async () => {
    const query = direccionInput.value;
    if (query.length < 3) { // No buscar hasta que haya al menos 3 caracteres
        suggestionsDiv.innerHTML = '';
        return;
    }

    try {
        const response = await fetch('/controller/address_suggestions.php', { // Crea este archivo
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ query: query })
        });
        const data = await response.json();
        displaySuggestions(data.places); // Asegúrate de que la respuesta tenga un campo 'places'
    } catch (error) {
        console.error('Error fetching suggestions:', error);
        suggestionsDiv.innerHTML = '<div class="alert alert-danger">Error al obtener sugerencias.</div>';
    }
});

function displaySuggestions(places) {
suggestionsDiv.innerHTML = '';
if (!places || places.length === 0) {
    suggestionsDiv.innerHTML = '<div class="alert alert-info">No se encontraron sugerencias.</div>';
    return;
}

const list = document.createElement('ul');
list.className = 'list-group';
places.forEach(place => {
    const item = document.createElement('li');
    item.className = 'list-group-item list-group-item-action';
    item.textContent = place.address; // Esto mostrará la 'description' de Google Places API
    // Podrías almacenar el place.place_id en un atributo de datos si lo necesitas más tarde
    item.setAttribute('data-place-id', place.place_id || ''); // Si decides usar place_id

    item.addEventListener('click', () => {
        direccionInput.value = place.address;
        // Si guardas el place_id, podrías usarlo para un campo oculto:
        // document.getElementById('direccion_place_id').value = place.place_id;
        suggestionsDiv.innerHTML = '';
    });
    list.appendChild(item);
});
suggestionsDiv.appendChild(list);
}