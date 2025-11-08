<?php

// Agreement Helper Functions

function createAgreement($mysqli, $data) {
    $agreement_name = sanitizeInput($data['name']);
    $agreement_client_id = intval($data['client_id']);
    $agreement_type = sanitizeInput($data['type']);
    $agreement_start_date = sanitizeInput($data['start_date']);
    $agreement_end_date = sanitizeInput($data['end_date']);
    $agreement_value = floatval($data['value'] ?? 0);
    $agreement_recurring_amount = floatval($data['recurring_amount'] ?? 0);
    $agreement_hours_included = floatval($data['hours_included'] ?? 0);
    $agreement_overage_rate = floatval($data['overage_rate'] ?? 0);
    
    $sql = "INSERT INTO agreements (
        agreement_name, 
        agreement_client_id,
        agreement_type,
        agreement_start_date,
        agreement_end_date,
        agreement_value,
        agreement_recurring_amount,
        agreement_hours_included,
        agreement_overage_rate,
        agreement_number
    ) VALUES (
        '$agreement_name',
        $agreement_client_id,
        '$agreement_type',
        '$agreement_start_date',
        '$agreement_end_date',
        $agreement_value,
        $agreement_recurring_amount,
        $agreement_hours_included,
        $agreement_overage_rate,
        1
    )";
    
    if (mysqli_query($mysqli, $sql)) {
        return mysqli_insert_id($mysqli);
    }
    return false;
}

function getAgreement($mysqli, $agreement_id) {
    $agreement_id = intval($agreement_id);
    $result = mysqli_query($mysqli, "SELECT * FROM agreements WHERE agreement_id = $agreement_id");
    $agreement = mysqli_fetch_assoc($result);

    if ($agreement) {
        // Calculate hours remaining and overage
        $agreement['agreement_hours_remaining'] = max(0, $agreement['agreement_hours_included'] - $agreement['agreement_hours_used']);
        $agreement['agreement_hours_overage'] = max(0, $agreement['agreement_hours_used'] - $agreement['agreement_hours_included']);
    }

    return $agreement;
}

function getAllAgreements($mysqli, $client_id = null, $status = null) {
    $where = "WHERE 1=1";
    
    if ($client_id) {
        $client_id = intval($client_id);
        $where .= " AND agreement_client_id = $client_id";
    }
    
    if ($status) {
        $status = sanitizeInput($status);
        $where .= " AND agreement_status = '$status'";
    }
    
    $result = mysqli_query($mysqli, "SELECT * FROM agreements $where ORDER BY agreement_created_at DESC");
    $agreements = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $agreements[] = $row;
    }
    return $agreements;
}

function updateAgreement($mysqli, $agreement_id, $data) {
    $agreement_id = intval($agreement_id);
    $agreement_name = sanitizeInput($data['name']);
    $agreement_status = sanitizeInput($data['status'] ?? 'Active');
    $agreement_recurring_amount = floatval($data['recurring_amount'] ?? 0);
    
    $sql = "UPDATE agreements SET 
        agreement_name = '$agreement_name',
        agreement_status = '$agreement_status',
        agreement_recurring_amount = $agreement_recurring_amount
        WHERE agreement_id = $agreement_id";
    
    return mysqli_query($mysqli, $sql);
}

function getAgreementHoursRemaining($mysqli, $agreement_id) {
    $agreement_id = intval($agreement_id);
    $result = mysqli_query($mysqli, 
        "SELECT agreement_hours_remaining FROM agreements WHERE agreement_id = $agreement_id");
    $row = mysqli_fetch_assoc($result);
    return $row['agreement_hours_remaining'] ?? 0;
}

function getAgreementsByClient($mysqli, $client_id) {
    $client_id = intval($client_id);
    $result = mysqli_query($mysqli, 
        "SELECT * FROM agreements WHERE agreement_client_id = $client_id AND agreement_status = 'Active' ORDER BY agreement_name");
    $agreements = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $agreements[] = $row;
    }
    return $agreements;
}

?>
