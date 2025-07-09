document.addEventListener('DOMContentLoaded', function() {
   
    document.querySelectorAll('.save-btn').forEach(button => {
        button.addEventListener('click', async function() {
            const row = this.closest('tr');
            const appointmentId = row.dataset.appointmentId;
            const dateInput = row.querySelector('.date-input');
            const mechanicSelect = row.querySelector('.mechanic-select');
            const statusSelect = row.querySelector('.status-select');
            
           
            if (!dateInput.value || !statusSelect.value) {
                alert('Please fill all required fields');
                return;
            }

            const payload = new URLSearchParams({
                action: 'updateAppointment',
                id: appointmentId,
                date: dateInput.value,
                mechanicId: mechanicSelect.value,
                status: statusSelect.value
            });

            
            const originalText = button.textContent;
            button.disabled = true;
            button.textContent = 'Saving...';

            try {
                
                let response = await fetch(`${window.BASE_URL}/admin/admin_functions.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams({
                        action: 'updateAppointment',
                        id: appointmentId,
                        date: dateInput.value,
                        mechanicId: mechanicSelect.value,
                        status: statusSelect.value
                    })
                });

                
                let data;
                const contentType = response.headers.get('content-type');
                
                if (!contentType?.includes('application/json')) {
                    const text = await response.text();
                    console.error('Received non-JSON:', text);
                    
                  
                    response = await fetch('/Car_Workshop_Appointment_System/admin/admin_functions.php', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: payload
                    });
                    
                    data = await response.json();
                } else {
                    data = await response.json();
                }

              
                if (!data.success) {
                    throw new Error(data.message || 'Update failed');
                }

           
                button.textContent = 'Saved!';
                button.classList.add('success');
                if (data.appointment) {
                    dateInput.value = data.appointment.appointment_date;
                    mechanicSelect.value = data.appointment.mechanic_id || 0;
                    statusSelect.value = data.appointment.status;
                }

            } catch (error) {
                console.error('Update error:', error);
                let userMessage = error.message.includes('JSON');
                
                if (error.message.includes('JSON')) {
                    userMessage = 'Server configuration issue - please contact support';
                    
                    if (isAdminUser) { 
                        userMessage += `\nTechnical Details: ${error.message}`;
                    }
                }
                
                alert(`Update failed: ${userMessage}`);
                button.textContent = 'Error!';
                button.classList.add('error');
            }
                
               finally {
                setTimeout(() => {
                    button.textContent = originalText;
                    button.disabled = false;
                    button.classList.remove('success', 'error');
                }, 2000);
            }
        });
    });
// document.querySelectorAll('form').forEach(form => {
//     form.addEventListener('submit', function(e) {
//         const button = this.querySelector('[type="submit"]');
//         button.disabled = true;
//         button.textContent = 'Saving...';
        
//         // Optional: Add visual feedback
//         this.classList.add('saving');
//     });
// });
    
    window.debugAppointments = async function() {
        const tests = {
            config: await fetch('config.php').then(r => r.text()),
            functions: await fetch('includes/functions.php').then(r => r.text()),
            endpoint: await fetch('test_endpoint.php').then(r => r.text()),
            mechanics: await fetch('admin_functions.php?action=getMechanics')
                         .then(async r => ({
                            status: r.status,
                            type: r.headers.get('content-type'),
                            body: await r.text()
                         }))
        };
        console.table(tests);
    };
});