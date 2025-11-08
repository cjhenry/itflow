<?php

/**
 * Agreement Services POST Handler
 * Handles service assignment to agreements with rate/hour overrides
 */

defined('FROM_POST_HANDLER') || die('Direct access not allowed');

require_once "includes/inc_agreement_services.php";

// Permission check
enforceUserPermission('module_client', 2);

// Add service to agreement
if (isset($_POST['add_agreement_service'])) {
    $agreement_id = intval($_POST['agreement_id'] ?? 0);
    $service_id = intval($_POST['service_id'] ?? 0);
    $custom_rate = $_POST['custom_rate'] ?? null;

    if ($agreement_id <= 0 || $service_id <= 0) {
        $_SESSION['error_message'] = "Invalid agreement or service ID";
        header('Location: agreement_details.php?agreement_id=' . $agreement_id);
        exit;
    }

    $result = addAgreementService($mysqli, $agreement_id, $service_id, $custom_rate);

    if ($result['success']) {
        $_SESSION['success_message'] = "Service added to agreement";
    } else {
        $_SESSION['error_message'] = $result['error'];
    }

    header('Location: agreement_details.php?agreement_id=' . $agreement_id . '&tab=services');
    exit;
}

// Update agreement service rate
if (isset($_POST['update_agreement_service_rate'])) {
    $agreement_service_id = intval($_POST['agreement_service_id'] ?? 0);
    $agreement_id = intval($_POST['agreement_id'] ?? 0);
    $custom_rate = $_POST['custom_rate'] ?? null;

    if ($agreement_service_id <= 0) {
        $_SESSION['error_message'] = "Invalid service ID";
        header('Location: agreement_details.php?agreement_id=' . $agreement_id);
        exit;
    }

    $result = updateAgreementServiceRate($mysqli, $agreement_service_id, $custom_rate);

    if ($result['success']) {
        $_SESSION['success_message'] = "Service rate updated";
    } else {
        $_SESSION['error_message'] = $result['error'];
    }

    header('Location: agreement_details.php?agreement_id=' . $agreement_id . '&tab=services');
    exit;
}

// Allocate hours to service
if (isset($_POST['allocate_service_hours'])) {
    $agreement_id = intval($_POST['agreement_id'] ?? 0);
    $service_id = intval($_POST['service_id'] ?? 0);
    $hours_allocated = floatval($_POST['hours_allocated'] ?? 0);

    if ($agreement_id <= 0 || $service_id <= 0) {
        $_SESSION['error_message'] = "Invalid agreement or service ID";
        header('Location: agreement_details.php?agreement_id=' . $agreement_id);
        exit;
    }

    $result = allocateAgreementServiceHours($mysqli, $agreement_id, $service_id, $hours_allocated);

    if ($result['success']) {
        $_SESSION['success_message'] = "Hours allocated";
    } else {
        $_SESSION['error_message'] = $result['error'];
    }

    header('Location: agreement_details.php?agreement_id=' . $agreement_id . '&tab=services');
    exit;
}

// Remove service from agreement
if (isset($_POST['remove_agreement_service'])) {
    $agreement_service_id = intval($_POST['agreement_service_id'] ?? 0);
    $agreement_id = intval($_POST['agreement_id'] ?? 0);

    if ($agreement_service_id <= 0) {
        $_SESSION['error_message'] = "Invalid service ID";
        header('Location: agreement_details.php?agreement_id=' . $agreement_id);
        exit;
    }

    $result = removeAgreementService($mysqli, $agreement_service_id);

    if ($result['success']) {
        $_SESSION['success_message'] = "Service removed";
    } else {
        $_SESSION['error_message'] = $result['error'];
    }

    header('Location: agreement_details.php?agreement_id=' . $agreement_id . '&tab=services');
    exit;
}

// Default redirect
header('Location: agreements.php');
exit;

?>
