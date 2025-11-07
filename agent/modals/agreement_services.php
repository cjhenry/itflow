<?php

/**
 * Agreement Services Tab
 * Manages service selection, rate overrides, and hour allocation for agreements
 */

if (!isset($agreement_id)) {
    return;
}

require_once "includes/inc_agreement_services.php";
require_once "includes/inc_client_services.php";

$agreement = getAgreement($mysqli, $agreement_id);
$agreement_services = getAgreementServices($mysqli, $agreement_id);

// Get available services for client
$client_services = getClientServices($mysqli, $agreement['agreement_client_id']);

// Check if block hours agreement
$is_block_hours = (strpos($agreement['agreement_type'], 'Block Hours') !== false);

?>

<div class="tab-pane fade" id="services_tab">
    <div class="mt-3">
        <h5>Services for This Agreement</h5>
        <p class="text-muted">Select which services are included in this agreement and optionally override rates.</p>

        <?php if ($is_block_hours) { ?>
            <div class="alert alert-info">
                <strong>Block Hours Agreement:</strong> Allocate included hours across services.
                Total allocated hours must equal <?php echo $agreement['agreement_hours_included']; ?> hours.
            </div>
        <?php } ?>

        <!-- Services Table -->
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Service Name</th>
                    <th>Master Rate</th>
                    <th>Client Rate</th>
                    <th>Agreement Rate</th>
                    <?php if ($is_block_hours) { ?>
                        <th>Hours Allocated</th>
                        <th>Hours Used</th>
                        <th>Hours Remaining</th>
                    <?php } ?>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($agreement_services as $svc) { ?>
                    <tr>
                        <td><strong><?php echo $svc['service_name']; ?></strong></td>
                        <td>$<?php echo number_format($svc['service_default_rate'], 2); ?></td>
                        <td class="text-muted">-</td>
                        <td>
                            <?php if ($svc['agreement_service_custom_rate']) { ?>
                                <span class="badge badge-warning">$<?php echo number_format($svc['agreement_service_custom_rate'], 2); ?></span>
                            <?php } else { ?>
                                <span class="badge badge-info">$<?php echo number_format($svc['service_default_rate'], 2); ?></span>
                            <?php } ?>
                        </td>
                        <?php if ($is_block_hours) { ?>
                            <td>
                                <?php if ($svc['service_hours_allocated']) { ?>
                                    <?php echo number_format($svc['service_hours_allocated'], 2); ?> hrs
                                <?php } else { ?>
                                    <span class="text-muted">Not allocated</span>
                                <?php } ?>
                            </td>
                            <td><?php echo number_format($svc['service_hours_used'] ?? 0, 2); ?> hrs</td>
                            <td><?php echo number_format($svc['service_hours_remaining'] ?? 0, 2); ?> hrs</td>
                        <?php } ?>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="editAgreementService(<?php echo $svc['agreement_service_id']; ?>)" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="removeAgreementService(<?php echo $svc['agreement_service_id']; ?>)" title="Remove">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Add Service Button -->
        <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#add_agreement_service_modal">
            <i class="fas fa-plus mr-2"></i>Add Service
        </button>

        <!-- Hour Allocation Warning (for Block Hours) -->
        <?php if ($is_block_hours) {
            $total_allocated = 0;
            foreach ($agreement_services as $svc) {
                $total_allocated += $svc['service_hours_allocated'] ?? 0;
            }
            $difference = abs($total_allocated - $agreement['agreement_hours_included']);
            if ($difference > 0.01) {
        ?>
            <div class="alert alert-warning mt-3">
                <strong>Hour Allocation Mismatch:</strong>
                Total allocated (<?php echo number_format($total_allocated, 2); ?> hrs) 
                does not match agreement total (<?php echo number_format($agreement['agreement_hours_included'], 2); ?> hrs)
            </div>
        <?php }
        } ?>
    </div>
</div>

<!-- Modal: Add Service to Agreement -->
<div class="modal fade" id="add_agreement_service_modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark">
                <h5 class="modal-title">Add Service to Agreement</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="post/agreement_service.php">
                    <input type="hidden" name="add_agreement_service" value="1">
                    <input type="hidden" name="agreement_id" value="<?php echo $agreement_id; ?>">

                    <div class="form-group">
                        <label>Select Service <span class="text-danger">*</span></label>
                        <select class="form-control" name="service_id" required>
                            <option value="">-- Choose Service --</option>
                            <?php foreach ($client_services as $cs) {
                                // Check if not already added
                                $already_added = false;
                                foreach ($agreement_services as $as) {
                                    if ($as['service_id'] == $cs['service_id']) {
                                        $already_added = true;
                                        break;
                                    }
                                }
                                if (!$already_added) {
                            ?>
                                <option value="<?php echo $cs['service_id']; ?>">
                                    <?php echo $cs['service_name']; ?> 
                                    - $<?php echo number_format($cs['service_rate'], 2); ?>/<?php echo $cs['service_default_unit']; ?>
                                </option>
                            <?php }
                            } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Custom Rate (optional)</label>
                        <input type="number" step="0.01" class="form-control" name="custom_rate" placeholder="Leave empty to use client/master rate">
                        <small class="text-muted">If provided, will override client and master rates for this agreement</small>
                    </div>

                    <hr>
                    <button type="submit" class="btn btn-primary">Add Service</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Edit Agreement Service -->
<div class="modal fade" id="edit_agreement_service_modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark">
                <h5 class="modal-title">Edit Service</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="post/agreement_service.php">
                <div class="modal-body">
                    <input type="hidden" name="update_agreement_service_rate" value="1">
                    <input type="hidden" name="agreement_id" value="<?php echo $agreement_id; ?>">
                    <input type="hidden" name="agreement_service_id" id="edit_agreement_service_id">

                    <div id="edit_service_details"></div>

                    <div class="form-group">
                        <label>Custom Rate (optional)</label>
                        <input type="number" step="0.01" class="form-control" id="edit_custom_rate" name="custom_rate" placeholder="Leave empty to use client/master rate">
                        <small class="text-muted">Leave empty to use the client or master rate</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editAgreementService(agreementServiceId) {
    alert('Edit rate modal - coming soon');
    // TODO: Implement rate override editing
}

function removeAgreementService(agreementServiceId) {
    if (confirm('Remove this service from the agreement?')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = 'post/agreement_service.php';
        form.innerHTML = '<input type="hidden" name="remove_agreement_service" value="1">';
        form.innerHTML += '<input type="hidden" name="agreement_service_id" value="' + agreementServiceId + '">';
        form.innerHTML += '<input type="hidden" name="agreement_id" value="<?php echo $agreement_id; ?>">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

