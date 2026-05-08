// OurMarketplace - Main JavaScript

document.addEventListener('DOMContentLoaded', function() {

    // Highlight active nav link based on current URL
    const currentPath = window.location.pathname;
    document.querySelectorAll('.navbar-nav .nav-link').forEach(function(link) {
        if (link.getAttribute('href') && currentPath.includes(link.getAttribute('href'))) {
            link.classList.add('active');
        }
    });

    // Rating input visual feedback
    const ratingInputs = document.querySelectorAll('.rating-input input');
    ratingInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            const value = this.value;
            const labels = this.closest('.rating-input').querySelectorAll('label');
            labels.forEach(function(label) {
                const forAttr = label.getAttribute('for');
                const starNum = parseInt(forAttr.replace('star', ''));
                if (starNum <= value) {
                    label.querySelector('i').className = 'fas fa-star';
                } else {
                    label.querySelector('i').className = 'far fa-star';
                }
            });
        });
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
});
