<?php $messages = $app->getMessages(); ?>

<?php if ( isset( $messages['alerts'] ) ): ?>
<div class="row">
	<?php foreach ($messages['alerts'] as $alert): ?>
    <div class="alert alert-<?php echo $alert['type']; ?><?php echo $alert['timeout'] ? ' fadeOut' : ''; ?> fade in alert-radius-bordered alert-shadowed">
    <button data-dismiss="alert" class="close">&times;</button>
    <?php if ($alert['title']): ?>
    <h3><?php echo $alert['title']; ?></h3>
    <?php endif; ?>
	<?php echo $alert['message']; ?>
    <?php if ( $alert['timeout'] ): ?>
    <span class="alert-label pull-right">will close in <span class="timer"><?php echo (int) $alert['timeout']; ?></span>s</span>
    <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ( isset( $messages['modals'] ) ): ?>
<?php foreach ($messages['modals'] as $message): ?>
<!--Success Modal Templates-->
<div class="modal fade alert-modal modal-message modal-<?php echo $message['type']; ?>" style="display: none;" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <?php if ( $message['type'] == 'success' ): ?>
                <i class="fa fa-check"></i>
            <?php elseif ( $message['type'] == 'info' ): ?>
                <i class="fa fa-info"></i>
            <?php elseif ( $message['type'] == 'warning' ): ?>
                <i class="fa fa-warning"></i>
            <?php elseif ( $message['type'] == 'danger' ): ?>
                <i class="fa fa-fire"></i>
            <?php elseif ( $message['type'] == 'primary' ): ?>
                <i class="fa fa-info"></i>
            <?php else: ?>
                <i class="fa fa-envelope"></i>
            <?php endif; ?>
            </div>
            <?php if ( $message['title'] ): ?>
            <div class="modal-title"><?php echo $message['title']; ?></div>
            <?php endif; ?>
            <div class="modal-body"><?php echo $message['message']; ?></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-<?php echo $message['type']; ?>" data-dismiss="modal">OK</button>
            </div>
        </div> <!-- / .modal-content -->
    </div> <!-- / .modal-dialog -->
</div>
<!--End Success Modal Templates-->
<?php endforeach; ?>
<?php endif; ?>
