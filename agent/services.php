<?php

$sort = "service_sort_order";
$order = "ASC";

require_once "includes/inc_all.php";
require_once "includes/inc_services.php";

// Permission check
enforceUserPermission('module_client', 2);

$status_filter = sanitizeInput($_GET['status'] ?? 'Active');
$category_filter = sanitizeInput($_GET['category'] ?? '');
$search_query = sanitizeInput($_GET['search'] ?? '');

$where = "WHERE 1=1";

if ($status_filter && $status_filter != 'All') {
    $where .= " AND service_status = '$status_filter'";
}

if ($category_filter) {
    $where .= " AND service_category = '$category_filter'";
}

if ($search_query) {
    $where .= " AND (service_name LIKE '%$search_query%' OR service_description LIKE '%$search_query%')";
}

$sql = mysqli_query(
    $mysqli,
    "SELECT SQL_CALC_FOUND_ROWS s.*, 
            (SELECT COUNT(DISTINCT client_id) FROM client_services WHERE service_id = s.service_id) as client_count,
            (SELECT COUNT(DISTINCT agreement_id) FROM agreement_services WHERE service_id = s.service_id) as agreement_count
    FROM service_catalog s
    $where
    ORDER BY $sort $order
    LIMIT $record_from, $record_to"
);

$num_rows = mysqli_fetch_row(mysqli_query($mysqli, "SELECT FOUND_ROWS()"));
$categories = getServiceCategories($mysqli);

?>

<div class="card">
    <div class="card-header bg-dark py-2">
        <h3 class="card-title mt-2"><i class="fas fa-fw fa-cogs mr-2"></i>Service Catalog</h3>
        <div class="card-tools">
            <?php if (lookupUserPermission("module_client") >= 2) { ?>
                <a href="javascript:void(0)" class="btn btn-primary" data-toggle="modal" data-target="#service_add_modal">
                    <i class="fas fa-plus mr-2"></i>New Service
                </a>
            <?php } ?>
        </div>
    </div>

    <div class="card-body">
        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-md-3">
                <input type="text" class="form-control" id="search_services" placeholder="Search services..." value="<?php echo $search_query; ?>">
            </div>
            <div class="col-md-3">
                <select class="form-control" id="category_filter">
                    <option value="">-- All Categories --</option>
                    <?php foreach ($categories as $cat) { ?>
                        <option value="<?php echo $cat; ?>" <?php echo ($category_filter == $cat) ? 'selected' : ''; ?>>
                            <?php echo $cat; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-control" id="status_filter">
                    <option value="Active" <?php echo ($status_filter == 'Active') ? 'selected' : ''; ?>>Active</option>
                    <option value="Archived" <?php echo ($status_filter == 'Archived') ? 'selected' : ''; ?>>Archived</option>
                    <option value="All" <?php echo ($status_filter == 'All') ? 'selected' : ''; ?>>All</option>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-secondary btn-block" id="apply_filters">Apply Filters</button>
            </div>
        </div>

        <!-- Services Table -->
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th>Service Name</th>
                    <th>Category</th>
                    <th>Default Rate</th>
                    <th>Unit</th>
                    <th>Status</th>
                    <th>Clients</th>
                    <th>Agreements</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($sql)) { ?>
                    <tr>
                        <td>
                            <strong><?php echo $row['service_name']; ?></strong>
                            <br><small class="text-muted"><?php echo $row['service_description']; ?></small>
                        </td>
                        <td><span class="badge badge-info"><?php echo $row['service_category'] ?: 'Uncategorized'; ?></span></td>
                        <td>$<?php echo number_format($row['service_default_rate'], 2); ?></td>
                        <td><?php echo $row['service_default_unit']; ?></td>
                        <td>
                            <span class="badge badge-<?php echo ($row['service_status'] == 'Active') ? 'success' : 'secondary'; ?>">
                                <?php echo $row['service_status']; ?>
                            </span>
                        </td>
                        <td><?php echo $row['client_count']; ?></td>
                        <td><?php echo $row['agreement_count']; ?></td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="editService(<?php echo $row['service_id']; ?>)" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <?php if ($row['service_status'] == 'Active' && $row['client_count'] == 0 && $row['agreement_count'] == 0) { ?>
                                <button class="btn btn-sm btn-danger" onclick="deleteService(<?php echo $row['service_id']; ?>)" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            <?php } else if ($row['service_status'] == 'Active') { ?>
                                <button class="btn btn-sm btn-warning" onclick="archiveService(<?php echo $row['service_id']; ?>)" title="Archive">
                                    <i class="fas fa-archive"></i>
                                </button>
                            <?php } else { ?>
                                <button class="btn btn-sm btn-success" onclick="restoreService(<?php echo $row['service_id']; ?>)" title="Restore">
                                    <i class="fas fa-undo"></i>
                                </button>
                            <?php } ?>
                            <button class="btn btn-sm btn-secondary" onclick="cloneService(<?php echo $row['service_id']; ?>)" title="Clone">
                                <i class="fas fa-copy"></i>
                            </button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <?php if ($num_rows[0] == 0) { ?>
            <div class="alert alert-info">No services found. <a href="javascript:void(0)" data-toggle="modal" data-target="#service_add_modal">Create one</a></div>
        <?php } ?>
    </div>
</div>

<!-- Add Service Modal -->
<div class="modal fade" id="service_add_modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark">
                <h5 class="modal-title">New Service</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="service_add_form" method="POST" action="post.php">
                    <input type="hidden" name="add_service" value="1">

                    <div class="form-group">
                        <label>Service Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="service_name" required>
                    </div>

                    <div class="form-group">
                        <label>Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="service_description" rows="3" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Default Rate <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" name="service_default_rate" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Default Unit</label>
                                <input type="text" class="form-control" name="service_default_unit" value="Hour" placeholder="Hour, Month, Incident, etc.">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Category</label>
                                <input type="text" class="form-control" name="service_category" placeholder="Support, Projects, etc.">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Minimum Hours</label>
                                <input type="number" step="0.01" class="form-control" name="service_minimum_hours">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" class="form-control" name="service_sort_order" value="0">
                    </div>

                    <hr>
                    <button type="submit" class="btn btn-primary">Create Service</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Service Modal -->
<div class="modal fade" id="service_edit_modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark">
                <h5 class="modal-title">Edit Service</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="service_edit_form" method="POST" action="post.php">
                    <input type="hidden" name="edit_service" value="1">
                    <input type="hidden" name="service_id" id="edit_service_id">

                    <div class="form-group">
                        <label>Service Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_service_name" name="service_name" required>
                    </div>

                    <div class="form-group">
                        <label>Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit_service_description" name="service_description" rows="3" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Default Rate <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" id="edit_service_rate" name="service_default_rate" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Default Unit</label>
                                <input type="text" class="form-control" id="edit_service_unit" name="service_default_unit">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Category</label>
                                <input type="text" class="form-control" id="edit_service_category" name="service_category">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Minimum Hours</label>
                                <input type="number" step="0.01" class="form-control" id="edit_service_minimum" name="service_minimum_hours">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" class="form-control" id="edit_service_sort" name="service_sort_order">
                    </div>

                    <hr>
                    <button type="submit" class="btn btn-primary">Update Service</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function editService(serviceId) {
    // Fetch service data and populate modal
    fetch('api/service.php?action=get&id=' + serviceId)
        .then(r => r.json())
        .then(data => {
            document.getElementById('edit_service_id').value = data.service_id;
            document.getElementById('edit_service_name').value = data.service_name;
            document.getElementById('edit_service_description').value = data.service_description;
            document.getElementById('edit_service_rate').value = data.service_default_rate;
            document.getElementById('edit_service_unit').value = data.service_default_unit;
            document.getElementById('edit_service_category').value = data.service_category;
            document.getElementById('edit_service_minimum').value = data.service_minimum_hours;
            document.getElementById('edit_service_sort').value = data.service_sort_order;
            $('#service_edit_modal').modal('show');
        });
}

function deleteService(serviceId) {
    if (confirm('Are you sure? This cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'post.php';
        form.innerHTML = '<input type="hidden" name="delete_service" value="1"><input type="hidden" name="service_id" value="' + serviceId + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function archiveService(serviceId) {
    if (confirm('Archive this service? It can be restored later.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'post.php';
        form.innerHTML = '<input type="hidden" name="archive_service" value="1"><input type="hidden" name="service_id" value="' + serviceId + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function restoreService(serviceId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'post.php';
    form.innerHTML = '<input type="hidden" name="restore_service" value="1"><input type="hidden" name="service_id" value="' + serviceId + '">';
    document.body.appendChild(form);
    form.submit();
}

function cloneService(serviceId) {
    const newName = prompt('Enter name for cloned service:', '');
    if (newName) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'post.php';
        form.innerHTML = '<input type="hidden" name="clone_service" value="1"><input type="hidden" name="service_id" value="' + serviceId + '"><input type="hidden" name="clone_name" value="' + newName + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

// Filter handling
document.getElementById('apply_filters').addEventListener('click', function() {
    const search = document.getElementById('search_services').value;
    const category = document.getElementById('category_filter').value;
    const status = document.getElementById('status_filter').value;
    window.location = 'services.php?search=' + search + '&category=' + category + '&status=' + status;
});
</script>
