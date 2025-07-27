document.addEventListener('DOMContentLoaded', function() {
    const starRatingContainer = document.getElementById('starRating');
    const calificacionInput = document.getElementById('calificacionInput');
    const selectedRatingText = document.getElementById('selectedRatingText');
    const reviewForm = document.getElementById('reviewForm');
    const reviewStatusMessage = document.getElementById('reviewStatusMessage');
    const stars = starRatingContainer.querySelectorAll('.star-icon');

    let currentRating = parseInt(calificacionInput.value) || 0;

    function updateStarColors(rating) {
        stars.forEach(star => {
            const starValue = parseInt(star.dataset.value);
            if (starValue <= rating) {
                star.style.fill = '#ffc107'; 
            } else {
                star.style.fill = '#ccc';
            }
        });
    }

    if (currentRating > 0) {
        updateStarColors(currentRating);
        selectedRatingText.textContent = `Tu calificación actual: ${currentRating} estrellas`;
    }

    starRatingContainer.addEventListener('mouseover', function(event) {
        const hoveredStar = event.target.closest('.star-icon');
        if (hoveredStar) {
            const hoverValue = parseInt(hoveredStar.dataset.value);
            updateStarColors(hoverValue);
        }
    });

    starRatingContainer.addEventListener('mouseout', function() {
        updateStarColors(currentRating);
    });

    starRatingContainer.addEventListener('click', function(event) {
        const clickedStar = event.target.closest('.star-icon');
        if (clickedStar) {
            currentRating = parseInt(clickedStar.dataset.value);
            calificacionInput.value = currentRating;
            updateStarColors(currentRating);
            selectedRatingText.textContent = `Tu calificación: ${currentRating} estrellas`;
        }
    });

    function showReviewStatus(message, type = 'info') {
        reviewStatusMessage.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
        setTimeout(() => {
            reviewStatusMessage.innerHTML = '';
        }, 5000); 
    }

    reviewForm.addEventListener('submit', function(event) {
        event.preventDefault(); 

        if (currentRating === 0) {
            showReviewStatus('Por favor, selecciona una calificación.', 'danger');
            return;
        }

        const formData = new FormData(reviewForm);
        formData.append('accion', 'guardar_reseña'); 

        fetch('../controller/controlador_reseña.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            const contentType = response.headers.get("content-type");
            if (response.ok && contentType && contentType.includes("application/json")) {
                return response.json();
            } else {
                return response.text().then(text => {
                    console.error('Respuesta no JSON o error de HTTP:', response.status, text);
                    throw new Error(`Respuesta no válida del servidor. Código HTTP: ${response.status}. Contenido: ${text.substring(0, 200)}...`);
                });
            }
        })
        .then(data => {
            if (data.status === 'success') {
                showReviewStatus('✅ Reseña guardada con éxito. ¡Gracias!', 'success');
                reviewForm.querySelector('button[type="submit"]').textContent = 'Reseña Actualizada';
                reviewForm.querySelector('button[type="submit"]').disabled = true;
            } else {
                showReviewStatus(`❌ Error al guardar reseña: ${data.message}`, 'danger');
            }
        })
        .catch(error => {
            console.error('Error AJAX al enviar reseña:', error);
            showReviewStatus(`❌ Error de red al guardar la reseña.`, 'danger');
        });
    });
});
