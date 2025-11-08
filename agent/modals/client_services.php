<?php

/**
 * Client Services Tab
 * Manages service selection and overrides for clients
 */

if (!isset($client_id)) {
    return;
}

require_once "includes/inc_client_services.php";

$client_services = getClientServices($mysqli, $client_id, false);
$active_services = getActiveServices($mysqli);

?>

<div class="tab-pane fade" id="services_tab">
    <div class="row mt-3">
        <!-- Column A: Master Services -->
        <div class="col-md-5">
            <h5>Available Services</h5>
            <div class="list-group" style="max-height: 600px; overflow-y: auto;">
                <?php foreach ($active_services as $service) {
                    // Check if already added to client
                    $is_added = false;
                    foreach ($client_services as $cs) {
                        if ($cs['service_id'] == $service['service_id']) {
                            $is_added = true;
                            break;
                        }
                    }
                    ?>
                    <a href="javascript:void(0)" class="list-group-item list-group-item-action" onclick="selectService(<?php echo $service['service_id']; ?>)">
                        <h6 class="mb-1"><?php echo $service['service_name']; ?></h6>
                        <small>$<?php echo number_format($service['service_default_rate'], 2); ?>/<?php echo $service['service_default_unit']; ?></small>
                        <br>
                        <span class="badge badge-<?php echo $is_added ? 'success' : 'secondary'; ?>">
                            <?php echo $is_added ? '✓ Added' : '○ Not Added'; ?>
                        </span>
                    </a>
                <?php } ?>
            </div>
        </div>

        <!-- Column B: Service Details & Overrides -->
        <div class="col-md-7">
            <h5>Service Details</h5>
            <div id="service_details" class="card">
                <div class="card-body text-muted">
                    <p>Select a service to view and customize.</p>
                </div>
            </div>

            <!-- Custom Service Section -->
            <hr>
            <h5>Add Custom Service</h5>
            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#add_custom_service_modal">
                <i class="fas fa-plus mr-2"></i>Add Custom Service
            </button>

            <!-- Added Services List -->
            <hr>
            <h5>Services for This Client</h5>
            <div class="list-group" style="max-height: 300px; overflow-y: auto;">
                <?php foreach ($client_services as $service) { ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="mb-1"><?php echo $service['service_name']; ?></h6>
                                <small class="text-muted">
                                    <?php if ($service['client_service_custom_rate']) { ?>
                                        <span class="badge badge-warning">Custom: $<?php echo number_format($service['client_service_custom_rate'], 2); ?></span>
                                    <?php } else { ?>
                                        <span class="badge badge-info">Default: $<?php echo number_format($service['service_rate'], 2); ?></span>
                                    <?php } ?>
                                </small>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-info" onclick="editClientService(<?php echo $service['client_service_id']; ?>)" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="removeClientService(<?php echo $service['client_service_id']; ?>)" title="Remove">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Custom Service -->
<div class="modal fade" id="add_custom_service_modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark">
                <h5 class="modal-title">Add Custom Service</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="post/client_service.php">
                <div class="modal-body">
                    <input type="hidden" name="add_custom_client_service" value="1">
                    <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">

                    <div class="form-group">
                        <label>Service Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="service_name" required>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="service_description" rows="2"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Rate <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" name="service_rate" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Category</label>
                                <input type="text" class="form-control" name="service_category">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Add Service</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function selectService(serviceId) {
    fetch('api/service.php?action=get&id=' + serviceId)
        .then(r => r.json())
        .then(data => {
            var html = '<div class="card-header"><h6>' + data.service_name + '</h6></div>';
            html += '<div class="card-body"><p class="text-muted">' + data.service_description + '</p>';
            html += '<dl class="row">';
            html += '<dt class="col-sm-6">Default Rate:</dt>';
            html += '<dd class="col-sm-6">$' + parseFloat(data.service_default_rate).toFixed(2) + '/' + data.service_default_unit + '</dd>';
            html += '<dt class="col-sm-6">Category:</dt>';
            html += '<dd class="col-sm-6">' + (data.service_category || 'N/A') + '</dd>';
            html += '</dl>';
            html += '<form method="POST" action="post/client_service.php">';
            html += '<input type="hidden" name="add_client_service" value="1">';
            html += '<input type="hidden" name="client_id" value="<?php echo isset($client_id) ? $client_id : 0; ?>">';
            html += '<input type="hidden" name="service_id" value="' + serviceId + '">';
            html += '<div class="form-group"><label>Custom Rate (optional)</label>';
            html += '<input type="number" step="0.01" class="form-control" name="custom_rate" placeholder="Leave empty for default rate"></div>';
            html += '<button type="submit" class="btn btn-sm btn-success">Add Service</button>';
            html += '</form></div>';
            document.getElementById('service_details').innerHTML = html;
        });
}

function editClientService(clientServiceId) {
    alert('Edit functionality coming soon');
}

function removeClientService(clientServiceId) {
    if (confirm('Remove this service from the client?')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = 'post/client_service.php';
        form.innerHTML = '<input type="hidden" name="remove_client_service" value="1">';
        form.innerHTML += '<input type="hidden" name="client_service_id" value="' + clientServiceId + '">';
        form.innerHTML += '<input type="hidden" name="client_id" value="<?php echo isset($client_id) ? $client_id : 0; ?>">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
