<?php

defined('FROM_POST_HANDLER') || die('Direct access not allowed');

if (isset($_POST['add_agreement'])) {
    enforceUserPermission('module_client', 2);
    
    $client_id = intval($_POST['client_id'] ?? 0);
    $name = mysqli_real_escape_string($mysqli, sanitizeInput($_POST['name'] ?? ''));
    $type = mysqli_real_escape_string($mysqli, sanitizeInput($_POST['type'] ?? ''));
    $start_date = sanitizeInput($_POST['start_date'] ?? '');
    $end_date = sanitizeInput($_POST['end_date'] ?? '');
    $value = floatval($_POST['value'] ?? 0);
    $recurring_amount = floatval($_POST['recurring_amount'] ?? 0);
    $hours_included = floatval($_POST['hours_included'] ?? 0);
    $overage_rate = floatval($_POST['overage_rate'] ?? 0);
    
    if (!empty($client_id) && !empty($name) && !empty($type) && !empty($start_date) && !empty($end_date)) {
        
        // Get next agreement number
        $result = mysqli_query($mysqli, "SELECT MAX(agreement_number) as max_num FROM agreements");
        $row = mysqli_fetch_assoc($result);
        $next_num = ($row['max_num'] ?? 0) + 1;
        
        $sql = "INSERT INTO agreements (
            agreement_prefix,
            agreement_number,
            agreement_name,
            agreement_type,
            agreement_status,
            agreement_start_date,
            agreement_end_date,
            agreement_value,
            agreement_recurring_amount,
            agreement_hours_included,
            agreement_hours_used,
            agreement_overage_rate,
            agreement_client_id,
            agreement_created_at
        ) VALUES (
            'AGR',
            $next_num,
            '$name',
            '$type',
            'Draft',
            '$start_date',
            '$end_date',
            $value,
            $recurring_amount,
            $hours_included,
            0,
            $overage_rate,
            $client_id,
            NOW()
        )";
        
        if (mysqli_query($mysqli, $sql)) {
            $agreement_id = mysqli_insert_id($mysqli);
            // Log activity
            logAction('agreement_created', $client_id, 'New agreement created: ' . $name);
            header('Location: agreements.php?success=1');
            exit;
        } else {
            // Redirect back with error
            header('Location: agreements.php?error=' . urlencode(mysqli_error($mysqli)));
            exit;
        }
    } else {
        // Missing required field
        header('Location: agreements.php?error=' . urlencode('Please fill in all required fields'));
        exit;
    }
}

?>
