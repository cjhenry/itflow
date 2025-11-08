<?php

require_once "includes/inc_all.php";
require_once "includes/inc_agreements.php";

enforceUserPermission('module_client');

$agreement_id = intval($_GET['agreement_id'] ?? 0);

if (!$agreement_id) {
    echo "Invalid agreement";
    exit;
}

$agreement = getAgreement($mysqli, $agreement_id);

if (!$agreement) {
    echo "Agreement not found";
    exit;
}

$client = mysqli_fetch_assoc(mysqli_query($mysqli, 
    "SELECT * FROM clients WHERE client_id = " . $agreement['agreement_client_id']));

// Get related tickets
$tickets = mysqli_query($mysqli, 
    "SELECT * FROM tickets WHERE ticket_agreement_id = $agreement_id ORDER BY ticket_created_at DESC");

?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-dark">
                <h3 class="card-title">Agreement Details</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5">Agreement Name:</dt>
                            <dd class="col-sm-7"><?php echo $agreement['agreement_name']; ?></dd>
                            
                            <dt class="col-sm-5">Type:</dt>
                            <dd class="col-sm-7"><?php echo $agreement['agreement_type']; ?></dd>
                            
                            <dt class="col-sm-5">Status:</dt>
                            <dd class="col-sm-7"><span class="badge badge-<?php echo $agreement['agreement_status'] == 'Active' ? 'success' : 'secondary'; ?>"><?php echo $agreement['agreement_status']; ?></span></dd>
                            
                            <dt class="col-sm-5">Client:</dt>
                            <dd class="col-sm-7"><a href="client_overview.php?client_id=<?php echo $client['client_id']; ?>"><?php echo $client['client_name']; ?></a></dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5">Start Date:</dt>
                            <dd class="col-sm-7"><?php echo date('M d, Y', strtotime($agreement['agreement_start_date'])); ?></dd>
                            
                            <dt class="col-sm-5">End Date:</dt>
                            <dd class="col-sm-7"><?php echo date('M d, Y', strtotime($agreement['agreement_end_date'])); ?></dd>
                            
                            <dt class="col-sm-5">Value:</dt>
                            <dd class="col-sm-7">$<?php echo number_format($agreement['agreement_value'], 2); ?></dd>
                            
                            <dt class="col-sm-5">Recurring Amount:</dt>
                            <dd class="col-sm-7">$<?php echo number_format($agreement['agreement_recurring_amount'], 2); ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Block Hours Agreement Section -->
        <?php if (strpos($agreement['agreement_type'], 'Block Hours') !== false) { ?>
        <div class="card mt-3">
            <div class="card-header bg-dark">
                <h3 class="card-title">Hour Tracking & Billing</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-6">Hours Included:</dt>
                            <dd class="col-sm-6"><strong><?php echo $agreement['agreement_hours_included']; ?> hrs</strong></dd>

                            <dt class="col-sm-6">Hours Used:</dt>
                            <dd class="col-sm-6"><?php echo $agreement['agreement_hours_used']; ?> hrs</dd>

                            <dt class="col-sm-6">Hours Remaining:</dt>
                            <dd class="col-sm-6"><span class="badge badge-success"><?php echo $agreement['agreement_hours_remaining']; ?> hrs</span></dd>

                            <dt class="col-sm-6">Overage Hours:</dt>
                            <dd class="col-sm-6"><?php echo $agreement['agreement_hours_overage']; ?> hrs</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <h5>Rate Information</h5>
                        <dl class="row">
                            <dt class="col-sm-6">Overage Rate:</dt>
                            <dd class="col-sm-6"><strong>$<?php echo number_format($agreement['agreement_overage_rate'], 2); ?>/hr</strong></dd>

                            <dt class="col-sm-6">Agreement Value:</dt>
                            <dd class="col-sm-6">$<?php echo number_format($agreement['agreement_value'], 2); ?></dd>
                        </dl>

                        <?php if ($agreement['agreement_hours_included'] > 0) { ?>
                        <div class="mt-3">
                            <p class="mb-2">Utilization: <strong><?php echo round(($agreement['agreement_hours_used'] / $agreement['agreement_hours_included'] * 100), 1); ?>%</strong></p>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: <?php echo ($agreement['agreement_hours_used'] / $agreement['agreement_hours_included'] * 100); ?>%"></div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>

        <!-- Fixed Price Agreement Section -->
        <?php if (strpos($agreement['agreement_type'], 'Fixed Price') !== false) { ?>
        <div class="card mt-3">
            <div class="card-header bg-dark">
                <h3 class="card-title">Billing Information</h3>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Recurring Amount:</dt>
                    <dd class="col-sm-8"><strong>$<?php echo number_format($agreement['agreement_recurring_amount'], 2); ?></strong></dd>

                    <dt class="col-sm-4">Billing Frequency:</dt>
                    <dd class="col-sm-8"><?php echo $agreement['agreement_billing_frequency']; ?></dd>

                    <dt class="col-sm-4">Agreement Value:</dt>
                    <dd class="col-sm-8">$<?php echo number_format($agreement['agreement_value'], 2); ?></dd>

                    <dt class="col-sm-4">Net Terms:</dt>
                    <dd class="col-sm-8"><?php echo $agreement['agreement_net_terms']; ?> days</dd>
                </dl>
            </div>
        </div>
        <?php } ?>

        <!-- Time & Materials Section -->
        <?php if (strpos($agreement['agreement_type'], 'Time & Materials') !== false) { ?>
        <div class="card mt-3">
            <div class="card-header bg-dark">
                <h3 class="card-title">Billing Rates</h3>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Client Rate:</dt>
                    <dd class="col-sm-8"><strong>$<?php echo number_format($client['client_rate'], 2); ?>/hr</strong></dd>

                    <dt class="col-sm-4">Overage Rate (if set):</dt>
                    <dd class="col-sm-8">$<?php echo number_format($agreement['agreement_overage_rate'], 2); ?>/hr</dd>
                </dl>
            </div>
        </div>
        <?php } ?>

        <!-- Related Tickets Section -->
        <div class="card mt-3">
            <div class="card-header bg-dark">
                <h3 class="card-title">Related Tickets</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Ticket #</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($ticket = mysqli_fetch_assoc($tickets)) { ?>
                        <tr>
                            <td><?php echo $ticket['ticket_id']; ?></td>
                            <td><?php echo $ticket['ticket_subject']; ?></td>
                            <td><?php echo $ticket['ticket_status']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($ticket['ticket_created_at'])); ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-dark">
                <h3 class="card-title">Actions</h3>
            </div>
            <div class="card-body">
                <a href="#" class="btn btn-primary btn-block">Edit Agreement</a>
                <a href="#" class="btn btn-danger btn-block mt-2">Cancel Agreement</a>
            </div>
        </div>
    </div>
</div>

