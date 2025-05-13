<?php
require_once __DIR__ . '/../config.php';

function getAvailableMechanics($pdo, $date = null) {
    $date = $date ?? date('Y-m-d');
    try {
        $stmt = $pdo->prepare("
            SELECT m.*, 
                   (MAX_APPOINTMENTS_PER_MECHANIC - COUNT(a.appointment_id)) as available_slots
            FROM mechanics m
            LEFT JOIN appointments a ON m.mechanic_id = a.mechanic_id 
                AND a.appointment_date = ?
                AND a.status IN ('pending', 'confirmed')
            GROUP BY m.mechanic_id
            HAVING available_slots > 0
        ");
        $stmt->execute([$date]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting mechanics: " . $e->getMessage());
        return [];
    }
}

function getAllAppointments($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT a.*, c.name as client_name, c.phone, c.car_license, m.name as mechanic_name
            FROM appointments a
            LEFT JOIN clients c ON a.client_id = c.client_id
            LEFT JOIN mechanics m ON a.mechanic_id = m.mechanic_id
            ORDER BY a.appointment_date DESC
        ");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting appointments: " . $e->getMessage());
        return [];
    }
}

function updateAppointment($pdo, $id, $date, $mechanicId, $status) {
    try {
        
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return [
                'success' => false,
                'message' => 'Invalid date format (YYYY-MM-DD required)',
                'code' => 'INVALID_DATE'
            ];
        }

        if (!in_array($status, ['pending', 'confirmed', 'completed'])) {
            return [
                'success' => false,
                'message' => 'Invalid status value',
                'code' => 'INVALID_STATUS'
            ];
        }

        
        if ($mechanicId > 0) {
            
            $stmt = $pdo->prepare("
                SELECT mechanic_id, appointment_date 
                FROM appointments 
                WHERE appointment_id = ?
            ");
            $stmt->execute([$id]);
            $current = $stmt->fetch();

            
            if (!$current || $current['mechanic_id'] != $mechanicId || $current['appointment_date'] != $date) {
                $stmt = $pdo->prepare("
                    SELECT m.max_appointments, 
                           COUNT(a.appointment_id) AS booked_slots
                    FROM mechanics m
                    LEFT JOIN appointments a ON 
                        m.mechanic_id = a.mechanic_id AND
                        a.appointment_date = ? AND
                        a.status IN ('pending', 'confirmed') AND
                        a.appointment_id != ?
                    WHERE m.mechanic_id = ?
                    GROUP BY m.mechanic_id
                ");
                $stmt->execute([$date, $id, $mechanicId]);
                $result = $stmt->fetch();

                if (!$result) {
                    return [
                        'success' => false,
                        'message' => 'Invalid mechanic selected',
                        'code' => 'INVALID_MECHANIC'
                    ];
                }

                if ($result['booked_slots'] >= $result['max_appointments']) {
                    return [
                        'success' => false,
                        'message' => 'Mechanic is fully booked for this date',
                        'code' => 'MECHANIC_FULL'
                    ];
                }
            }
        }

        
        $stmt = $pdo->prepare("
            UPDATE appointments 
            SET appointment_date = ?, 
                mechanic_id = ?, 
                status = ?,
                updated_at = NOW()
            WHERE appointment_id = ?
        ");
        
        $stmt->execute([$date, $mechanicId, $status, $id]);
        
        return [
            'success' => $stmt->rowCount() > 0,
            'message' => $stmt->rowCount() > 0 ? 'Appointment updated' : 'No changes made',
            'code' => $stmt->rowCount() > 0 ? 'UPDATE_SUCCESS' : 'NO_CHANGES',
            'appointment' => [
                'appointment_date' => $date,
                'mechanic_id' => $mechanicId,
                'status' => $status
            ]
        ];

    } catch (PDOException $e) {
        error_log("Error updating appointment (ID: $id): " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Database error during update',
            'code' => 'DB_UPDATE_ERROR'
        ];
    }
}

function isClientBooked($pdo, $phone, $date) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM appointments a
            JOIN clients c ON a.client_id = c.client_id
            WHERE c.phone = ? AND a.appointment_date = ?
        ");
        $stmt->execute([$phone, $date]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log("Error checking client booking: " . $e->getMessage());
        return false;
    }
}

function createAppointment($pdo, $clientData, $mechanicId, $appointmentDate) {
    try {
        $pdo->beginTransaction();
        
        
        
        $stmt = $pdo->prepare("
            INSERT INTO clients (name, address, phone, car_license, car_engine)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                name = VALUES(name),
                address = VALUES(address),
                car_license = VALUES(car_license),
                car_engine = VALUES(car_engine)
        ");
        $stmt->execute([
            $clientData['name'],
            $clientData['address'],
            $clientData['phone'],
            $clientData['car_license'],
            $clientData['car_engine']
        ]);
        
        $clientId = $pdo->lastInsertId() ?: $pdo->query("
            SELECT client_id FROM clients WHERE phone = '{$clientData['phone']}'
        ")->fetchColumn();
        
       
        $stmt = $pdo->prepare("
            INSERT INTO appointments (client_id, mechanic_id, appointment_date, status)
            VALUES (?, ?, ?, 'pending')
        ");
        $stmt->execute([$clientId, $mechanicId, $appointmentDate]);
        
        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error creating appointment: " . $e->getMessage());
        return false;
    }
}

function getAllMechanics($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM mechanics ORDER BY name");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting mechanics: " . $e->getMessage());
        return [];
    }
}

?>