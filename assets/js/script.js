document.addEventListener('DOMContentLoaded', function() {
    const appointmentForm = document.getElementById('appointmentForm');
    const appointmentDate = document.getElementById('appointment_date');
    const mechanicSelect = document.getElementById('mechanic_id');
    
 
    appointmentDate.min = new Date().toISOString().split('T')[0];
    
    
    appointmentForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        
        const formData = {
            name: document.getElementById('name').value.trim(),
            address: document.getElementById('address').value.trim(),
            phone: document.getElementById('phone').value.trim(),
            car_license: document.getElementById('car_license').value.trim(),
            car_engine: document.getElementById('car_engine').value.trim(),
            appointment_date: appointmentDate.value,
            mechanic_id: mechanicSelect.value
        };
        
      
        try {
            if (!formData.name) throw new Error('Full name is required');
            if (!formData.address) throw new Error('Address is required');
            
            if (!/^\d{10,15}$/.test(formData.phone)) {
                throw new Error('Please enter a valid phone number (10-15 digits)');
            }
            
            if (!/^[A-Z0-9-]+$/i.test(formData.car_license)) {
                throw new Error('Car license should be alphanumeric (hyphens allowed)');
            }
            
            if (!/^[A-Z0-9-]+$/i.test(formData.car_engine)) {
                throw new Error('Car engine number should be alphanumeric (hyphens allowed)');
            }
            
            if (!formData.appointment_date) throw new Error('Please select a date');
            if (!formData.mechanic_id) throw new Error('Please select a mechanic');
            
           
            const submitBtn = document.querySelector('#appointmentForm button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';
            
            const response = await fetch('process_appointment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(formData)
            });
            
            if (response.redirected) {
                window.location.href = response.url;
            } else {
                const result = await response.json();
                if (!result.success) {
                    throw new Error(result.message || 'Booking failed');
                }
                window.location.href = '/success.php';
            }
            
        } catch (error) {
           
            const errorDisplay = document.getElementById('error-display') || createErrorDisplay();
            errorDisplay.textContent = error.message;
            errorDisplay.style.display = 'block';
            
            const submitBtn = document.querySelector('#appointmentForm button[type="submit"]');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Book Appointment';
            
            console.error('Error:', error);
        }
    });
    
   
    function createErrorDisplay() {
        const errorDiv = document.createElement('div');
        errorDiv.id = 'error-display';
        errorDiv.style.display = 'none';
        errorDiv.style.color = '#e74c3c';
        errorDiv.style.margin = '10px 0';
        errorDiv.style.padding = '10px';
        errorDiv.style.border = '1px solid #e74c3c';
        errorDiv.style.borderRadius = '4px';
        appointmentForm.prepend(errorDiv);
        return errorDiv;
    }
    
    
    appointmentDate.addEventListener('change', function() {
        const date = this.value;
        if (!date) return;
        
        const loader = document.createElement('div');
        loader.className = 'loader';
        mechanicSelect.parentNode.appendChild(loader);
        mechanicSelect.disabled = true;
        
        fetch(`/includes/functions.php?action=getMechanics&date=${date}`)
            .then(response => {
                if (!response.ok) throw new Error('Network error');
                return response.json();
            })
            .then(data => {
                mechanicSelect.innerHTML = '<option value="">-- Select Mechanic --</option>';
                data.forEach(mechanic => {
                    const option = document.createElement('option');
                    option.value = mechanic.mechanic_id;
                    option.textContent = `${mechanic.name} (${mechanic.available_slots} slots available)`;
                    option.disabled = mechanic.available_slots <= 0;
                    mechanicSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error loading mechanics:', error);
                alert('Failed to load mechanic availability. Please try again.');
            })
            .finally(() => {
                mechanicSelect.disabled = false;
                if (loader.parentNode) loader.parentNode.removeChild(loader);
            });
    });
});