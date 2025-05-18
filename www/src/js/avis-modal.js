document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('review-modal');
    const closeBtn = document.querySelector('.close');
    const stars = document.querySelectorAll('.rating-stars .rating-star');
    const ratingInput = document.getElementById('rating-value');
    const reviewForm = document.getElementById('review-form');
    const reviewMessage = document.getElementById('review-message');

    window.openReviewModal = function(voyageId) {
        document.getElementById('voyage-id').value = voyageId;
        if (modal) modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    };

    function closeModal() {
        if (modal) modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        if (reviewForm) reviewForm.reset();
        if (stars) stars.forEach(star => star.classList.remove('active'));
        if (ratingInput) ratingInput.value = 0;
        if (reviewMessage) {
            reviewMessage.style.display = 'none';
            reviewMessage.className = '';
        }
    }

    if (closeBtn) closeBtn.addEventListener('click', closeModal);

    if (stars) {
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                if (ratingInput) ratingInput.value = value;
                stars.forEach(s => {
                    if (s.getAttribute('data-value') <= value) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
            });
        });
    }

    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const messageDiv = document.getElementById('review-message');
            if (messageDiv) {
                messageDiv.textContent = 'Avis soumis avec succès ! (Simulation)';
                messageDiv.className = 'success';
                messageDiv.style.display = 'block';
            }
            setTimeout(() => {
                closeModal();
                if (messageDiv) messageDiv.style.display = 'none';
            }, 2000);
        });
    }

    window.addEventListener('click', function(e) {
        if (modal && e.target === modal) {
            if (closeBtn) closeBtn.click();
        }
    });
}); 