<?php

$sort = "agreement_created_at";
$order = "DESC";

require_once "includes/inc_all.php";
require_once "includes/inc_agreements.php";

// Perms check
enforceUserPermission('module_client');

$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

$where = "WHERE 1=1";
if ($status_filter) {
    $where .= " AND agreement_status = '$status_filter'";
}

$sql = mysqli_query(
    $mysqli,
    "SELECT SQL_CALC_FOUND_ROWS a.*, c.client_name
    FROM agreements a
    LEFT JOIN clients c ON a.agreement_client_id = c.client_id
    $where
    ORDER BY $sort $order
    LIMIT $record_from, $record_to"
);

$num_rows = mysqli_fetch_row(mysqli_query($mysqli, "SELECT FOUND_ROWS()"));

?>

<div class="card">
    <div class="card-header bg-dark py-2">
        <h3 class="card-title mt-2"><i class="fa fa-fw fa-handshake mr-2"></i>Agreements</h3>
        <div class="card-tools">
            <?php if (lookupUserPermission("module_client") >= 2) { ?>
                <a class="btn btn-primary" href="agreement_add.php">
                    <i class="fas fa-plus mr-2"></i>New Agreement
                </a>
            <?php } ?>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th>Agreement #</th>
                    <th>Client</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($sql)) { ?>
                    <tr>
                        <td><?php echo $row['agreement_id']; ?></td>
                        <td><a href="client_overview.php?client_id=<?php echo $row['agreement_client_id']; ?>"><?php echo $row['client_name']; ?></a></td>
                        <td><?php echo $row['agreement_name']; ?></td>
                        <td><small class="badge badge-info"><?php echo $row['agreement_type']; ?></small></td>
                        <td><small class="badge badge-<?php echo $row['agreement_status'] == 'Active' ? 'success' : 'secondary'; ?>"><?php echo $row['agreement_status']; ?></small></td>
                        <td><?php echo date('M d, Y', strtotime($row['agreement_start_date'])); ?></td>
                        <td><?php echo date('M d, Y', strtotime($row['agreement_end_date'])); ?></td>
                        <td>
                            <a href="agreement_details.php?agreement_id=<?php echo $row['agreement_id']; ?>" class="btn btn-sm btn-info">
                                <i class="fa fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
