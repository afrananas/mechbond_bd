<?php

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

$mechanics = $pdo->query("SELECT * FROM mechanics")->fetchAll();
?>
<main class="container">
    
    <section class="hero">
        <div class="hero-content">
            <h1>Expert Car Repairs</h1>
            <p>Book your preferred senior mechanic with real-time availability</p>
        </div>
    </section>

    
    <div class="booking-form">
        <h2><i class="fas fa-calendar-check"></i> Book Your Appointment</h2>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= sanitize_output(urldecode($_GET['error'])) ?>
            </div>
        <?php endif; ?>
        
        <form id="appointmentForm" action="process_appointment.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            
            <div class="form-grid">
              
                <div class="form-column">
                    <div class="form-group">
                        <label for="name"><i class="fas fa-user"></i> Full Name:</label>
                        <input type="text" id="name" name="name" required 
                               pattern="[A-Za-z ]{3,50}" title="3-50 alphabetical characters">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone"><i class="fas fa-phone"></i> Phone:</label>
                        <input type="tel" id="phone" name="phone" required 
                               pattern="[0-9]{10,15}" title="10-15 digits">
                    </div>
                    
                    <div class="form-group">
                        <label for="car_license"><i class="fas fa-car"></i> License Plate:</label>
                        <input type="text" id="car_license" name="car_license" required
                               pattern="[A-Z0-9-]+" title="Alphanumeric with hyphens">
                    </div>
                </div>
                
               
                <div class="form-column">
                    <div class="form-group">
                        <label for="address"><i class="fas fa-map-marker-alt"></i> Address:</label>
                        <textarea id="address" name="address" required rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="car_engine"><i class="fas fa-cog"></i> Engine No:</label>
                        <input type="text" id="car_engine" name="car_engine" required
                               pattern="[A-Z0-9-]+" title="Alphanumeric with hyphens">
                    </div>
                </div>
            </div>

            
            <div class="form-bottom">
                <div class="form-group">
                    <label for="appointment_date"><i class="fas fa-calendar-day"></i> Date:</label>
                    <input type="date" id="appointment_date" name="appointment_date" required
                           min="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label for="mechanic_id"><i class="fas fa-user-cog"></i> Preferred Mechanic:</label>
                    <select id="mechanic_id" name="mechanic_id" class="form-control" required>
                        <option value="">-- Select Mechanic --</option>
                        <?php foreach ($mechanics as $mechanic): ?>
                            <option value="<?= $mechanic['mechanic_id'] ?>">
                                <?= htmlspecialchars($mechanic['name']) ?> 
                                (Slots: <?= $mechanic['max_appointments'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>              
                
                
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-check-circle"></i> Confirm Booking
            </button>
            
                <div class="debug-info" style="
                    background: #f8f9fa;
                    border-radius: 8px;
                    padding: 20px;
                    margin-top: 30px;
                    box-shadow: 0 0 10px rgba(0,0,0,0.05);
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                ">
                <h4 style="
                    color: #2c3e50;
                    border-bottom: 2px solid #3498db;
                    padding-bottom: 10px;
                    margin-bottom: 15px;
                ">
                    <i class="fas fa-wrench"></i> Mechanic Availability for <?= date('Y-m-d') ?>
                </h4>
    
                <?php
                $availability = $pdo->query("
                    SELECT m.mechanic_id, m.name, 
                        m.max_appointments as max,
                        COUNT(a.appointment_id) as booked,
                        m.max_appointments - COUNT(a.appointment_id) AS available
                    FROM mechanics m
                    LEFT JOIN appointments a ON m.mechanic_id = a.mechanic_id
                        AND a.appointment_date = CURDATE()
                        AND a.status IN ('pending', 'confirmed')
                    GROUP BY m.mechanic_id
                ")->fetchAll();
                ?>

                <table style="
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 15px;
                ">
                    <thead>
                        <tr style="background-color: #3498db; color: white;">
                            <th style="padding: 10px; text-align: left;">Mechanic</th>
                            <th style="padding: 10px; text-align: center;">Booked</th>
                            <th style="padding: 10px; text-align: center;">Max</th>
                            <th style="padding: 10px; text-align: center;">Available</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($availability as $mechanic): ?>
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 10px;">
                                <?= htmlspecialchars($mechanic['name']) ?>
                            </td>
                            <td style="text-align: center; padding: 10px; 
                                <?= $mechanic['booked'] >= $mechanic['max'] ? 'color: #e74c3c;' : '' ?>">
                                <?= $mechanic['booked'] ?>
                            </td>
                            <td style="text-align: center; padding: 10px;">
                                <?= $mechanic['max'] ?>
                            </td>
                            <td style="text-align: center; padding: 10px;
                                <?= $mechanic['available'] <= 0 ? 'color: #e74c3c;' : 'color: #27ae60;' ?>">
                                <?= $mechanic['available'] ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
    
                <div style="margin-top: 15px; font-size: 0.9em; color: #7f8c8d;">
                    <i class="fas fa-info-circle"></i> Updates in real-time. Green = available, Red = fully booked.
                </div>
            </div>
        </form>
    </div>


    <section class="features">
        <h3>Why Choose Us?</h3>
        <div class="feature-grid">
            <div class="feature-card">
                <i class="fas fa-users"></i>
                <h4>5 Senior Mechanics</h4>
                <p>Each with 10+ years experience</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-clock"></i>
                <h4>Real-Time Availability</h4>
                <p>See who's available instantly</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-shield-alt"></i>
                <h4>Quality Guarantee</h4>
                <p>90-day warranty on all repairs</p>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
