<?php

$sort = "agreement_created_at";
$order = "DESC";

require_once "includes/inc_all.php";
require_once "includes/inc_agreements.php";

enforceUserPermission('module_client', 2);

?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header bg-dark">
                <h3 class="card-title">Create New Agreement</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="post.php">
                    <input type="hidden" name="module" value="agreement">
                    <input type="hidden" name="add_agreement" value="1">
                    
                    <div class="form-group">
                        <label>Client <span class="text-danger">*</span></label>
                        <select class="form-control" name="client_id" required>
                            <option value="">-- Select Client --</option>
                            <?php
                            $clients = mysqli_query($mysqli, "SELECT client_id, client_name FROM clients WHERE client_archived_at IS NULL ORDER BY client_name");
                            while ($client = mysqli_fetch_assoc($clients)) {
                                echo "<option value='" . $client['client_id'] . "'>" . $client['client_name'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Agreement Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>

                    <div class="form-group">
                        <label>Agreement Type <span class="text-danger">*</span></label>
                        <select class="form-control" name="type" required>
                            <option value="">-- Select Type --</option>
                            <option>Fixed Price - Monthly</option>
                            <option>Fixed Price - Quarterly</option>
                            <option>Fixed Price - Annually</option>
                            <option>Block Hours - Prepaid</option>
                            <option>Block Hours - Monthly Drawdown</option>
                            <option>Time & Materials</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>End Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="end_date" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Agreement Value</label>
                                <input type="number" step="0.01" class="form-control" name="value" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Recurring Amount</label>
                                <input type="number" step="0.01" class="form-control" name="recurring_amount" value="0">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Included Hours (for Block Hours agreements)</label>
                                <input type="number" step="0.01" class="form-control" name="hours_included" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Overage Rate</label>
                                <input type="number" step="0.01" class="form-control" name="overage_rate" value="0">
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="form-group">
                        <a href="agreements.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Agreement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

