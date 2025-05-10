/**
 * DriveEasy Rentals - Main JavaScript File
 */

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileNav = document.querySelector('.mobile-nav');
    
    if (mobileMenuToggle && mobileNav) {
        mobileMenuToggle.addEventListener('click', function() {
            mobileNav.classList.toggle('active');
            document.body.classList.toggle('menu-open');
        });
    }

    // Hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    if (alerts.length > 0) {
        setTimeout(function() {
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 500);
            });
        }, 5000);
    }

    // Date validation for booking form
    const pickupDateInput = document.getElementById('pickup_date');
    const returnDateInput = document.getElementById('return_date');
    
    if (pickupDateInput && returnDateInput) {
        // Set minimum date for pickup as today
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        const todayString = `${yyyy}-${mm}-${dd}`;
        
        pickupDateInput.setAttribute('min', todayString);
        
        // Update return date minimum when pickup date changes
        pickupDateInput.addEventListener('change', function() {
            if (pickupDateInput.value) {
                returnDateInput.setAttribute('min', pickupDateInput.value);
                
                // If return date is before pickup date, reset it
                if (returnDateInput.value && returnDateInput.value < pickupDateInput.value) {
                    returnDateInput.value = '';
                }
            }
        });
    }

    // Car selection in booking form
    const carSelect = document.getElementById('car_id');
    const selectedCarInfo = document.querySelector('.selected-car-info');
    const selectCarPrompt = document.querySelector('.select-car-prompt');
    
    if (carSelect && selectedCarInfo && selectCarPrompt) {
        carSelect.addEventListener('change', function() {
            if (carSelect.value) {
                // In a real application, this would make an AJAX request to get car details
                // For this example, we'll assume the car details are loaded with PHP
                selectedCarInfo.style.display = 'block';
                selectCarPrompt.style.display = 'none';
            } else {
                selectedCarInfo.style.display = 'none';
                selectCarPrompt.style.display = 'flex';
            }
        });
    }

    // Confirm delete actions
    const deleteForms = document.querySelectorAll('.delete-form');
    if (deleteForms.length > 0) {
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
        });
    }

    // Image preview for car forms
    const imageUrlInput = document.getElementById('image_url');
    const imagePreview = document.getElementById('image_preview');
    
    if (imageUrlInput && imagePreview) {
        imageUrlInput.addEventListener('change', function() {
            const url = imageUrlInput.value.trim();
            if (url) {
                const img = imagePreview.querySelector('img') || document.createElement('img');
                img.src = url;
                img.alt = 'Car Image Preview';
                if (!imagePreview.contains(img)) {
                    imagePreview.appendChild(img);
                }
                imagePreview.style.display = 'block';
            } else {
                imagePreview.style.display = 'none';
            }
        });
        
        // Trigger change event to load initial preview
        if (imageUrlInput.value.trim()) {
            const event = new Event('change');
            imageUrlInput.dispatchEvent(event);
        }
    }

    // Features input enhancement
    const featuresInput = document.getElementById('features');
    const featuresContainer = document.getElementById('features_container');
    
    if (featuresInput && featuresContainer) {
        const addFeatureBtn = document.getElementById('add_feature');
        const featureInput = document.getElementById('new_feature');
        
        if (addFeatureBtn && featureInput) {
            // Initialize features from comma-separated value
            updateFeaturesDisplay();
            
            addFeatureBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const feature = featureInput.value.trim();
                if (feature) {
                    const currentFeatures = featuresInput.value ? 
                        featuresInput.value.split(',').map(f => f.trim()).filter(f => f) : 
                        [];
                    
                    if (!currentFeatures.includes(feature)) {
                        currentFeatures.push(feature);
                        featuresInput.value = currentFeatures.join(', ');
                        updateFeaturesDisplay();
                        featureInput.value = '';
                    }
                }
            });
        }
        
        function updateFeaturesDisplay() {
            if (!featuresInput.value) {
                featuresContainer.innerHTML = '<p class="text-muted">No features added yet.</p>';
                return;
            }
            
            const features = featuresInput.value.split(',').map(f => f.trim()).filter(f => f);
            let html = '<div class="features-list">';
            
            features.forEach(feature => {
                html += `
                    <div class="feature-tag">
                        <span>${feature}</span>
                        <button type="button" class="remove-feature" data-feature="${feature}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
            });
            
            html += '</div>';
            featuresContainer.innerHTML = html;
            
            // Add event listeners to remove buttons
            document.querySelectorAll('.remove-feature').forEach(btn => {
                btn.addEventListener('click', function() {
                    const featureToRemove = this.getAttribute('data-feature');
                    const updatedFeatures = featuresInput.value
                        .split(',')
                        .map(f => f.trim())
                        .filter(f => f && f !== featureToRemove);
                    
                    featuresInput.value = updatedFeatures.join(', ');
                    updateFeaturesDisplay();
                });
            });
        }
    }

    // Admin sidebar toggle
    const menuToggle = document.querySelector('.menu-toggle');
    const adminSidebar = document.querySelector('.admin-sidebar');
    const adminContent = document.querySelector('.admin-content');
    
    if (menuToggle && adminSidebar && adminContent) {
        menuToggle.addEventListener('click', function() {
            adminSidebar.classList.toggle('active');
            if (adminSidebar.classList.contains('active')) {
                adminContent.style.marginLeft = '0';
            } else {
                adminContent.style.marginLeft = '0';
            }
        });
    }
});
