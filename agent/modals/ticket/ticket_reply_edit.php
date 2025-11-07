<?php

require_once '../../../includes/modal_header.php';
require_once '../../includes/inc_invoice_services.php';

$ticket_reply_id = intval($_GET['id']);

$sql = mysqli_query($mysqli, "SELECT * FROM ticket_replies
    LEFT JOIN tickets ON ticket_id = ticket_reply_ticket_id
    WHERE ticket_reply_id = $ticket_reply_id
    LIMIT 1"
);

$row = mysqli_fetch_array($sql);
$ticket_reply_type = nullable_htmlentities($row['ticket_reply_type']);
$ticket_reply_time_worked = date_create($row['ticket_reply_time_worked']);
$ticket_reply_time_worked_formatted = date_format($ticket_reply_time_worked, 'H:i:s');
$ticket_reply = nullable_htmlentities($row['ticket_reply']);
$client_id = intval($row['ticket_client_id']);
$service_id = intval($row['ticket_reply_service_id'] ?? 0);
$ticket_id = intval($row['ticket_reply_ticket_id']);

// Get available services for client
$available_services = getServicesForInvoice($mysqli, $client_id);

// Generate the HTML form content using output buffering.
ob_start();

?>

<div class="modal-header bg-dark">
    <h5 class="modal-title"><i class="fa fa-fw fa-edit mr-2"></i>Editing Ticket Reply</h5>
    <button type="button" class="close text-white" data-dismiss="modal">
        <span>&times;</span>
    </button>
</div>
<form action="post.php" method="post" autocomplete="off">
    <input type="hidden" name="ticket_reply_id" value="<?php echo $ticket_reply_id; ?>">
    <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">

    <div class="modal-body">

        <div class="form-group">
            <div class="btn-group btn-block btn-group-toggle" data-toggle="buttons">
                <label class="btn btn-outline-secondary <?php if ($ticket_reply_type == 'Internal') { echo "active"; } ?>">
                    <input type="radio" name="ticket_reply_type" value="Internal" <?php if ($ticket_reply_type == 'Internal') { echo "checked"; } ?>>Internal Note
                </label>
                <label class="btn btn-outline-secondary <?php if ($ticket_reply_type == 'Public') { echo "active"; } ?>">
                    <input type="radio" name="ticket_reply_type" value="Public" <?php if ($ticket_reply_type == 'Public') { echo "checked"; } ?>>Public Comment
                </label>
            </div>
        </div>

        <div class="form-group">
            <textarea class="form-control tinymce" name="ticket_reply"><?php echo $ticket_reply; ?></textarea>
        </div>

        <div class="form-row">
            <?php if (!empty($ticket_reply_time_worked)) { ?>
                <div class="col-6">
                    <div class="form-group">
                        <label>Time worked</label>
                        <input class="form-control" name="time" type="text" placeholder="HH:MM:SS" pattern="([01]?[0-9]|2[0-3]):([0-5]?[0-9]):([0-5]?[0-9])" value="<?php echo $ticket_reply_time_worked_formatted; ?>" required>
                    </div>
                </div>
            <?php } ?>

            <div class="col-6">
                <div class="form-group">
                    <label>Service (Optional)</label>
                    <select class="form-control" name="service_id">
                        <option value="0">-- No Service --</option>
                        <?php foreach ($available_services as $svc) { ?>
                            <option value="<?php echo $svc['service_id']; ?>" <?php echo $service_id == $svc['service_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($svc['service_name']); ?> - $<?php echo number_format($svc['effective_rate'], 2); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        </div>

    </div>
    <div class="modal-footer">
        <button type="submit" name="edit_ticket_reply" class="btn btn-primary text-bold"><i class="fa fa-check mr-2"></i>Save</button>
        <button type="button" class="btn btn-light" data-dismiss="modal"><i class="fa fa-times mr-2"></i>Cancel</button>
    </div>
</form>

<?php

require_once '../../../includes/modal_footer.php';
