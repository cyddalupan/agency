<div id="modalExtendReserved" role="dialog" tabindex="-1" class="modal fade modal-darkorange" aria-hidden="false">
	<div class="modal-dialog">
    	<div class="modal-content">
        	<div class="modal-header">
            	<button aria-hidden="true" data-dismiss="modal" class="bootbox-close-button close" type="button">×</button>
                <h4 class="modal-title">--</h4>
            </div>
            
            <form method="post" class="form" role="form">
            <input type="hidden" name="reservation[reservation_id]" value="0">
            <div class="modal-body">
                <div class="bootbox-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                            	<label for="reservation[extend]">Days to extend starting today (<?php echo date( 'd M Y', time() ); ?>):</label>
                                <input name="reservation[extend]" type="text" placeholder="0" class="form-control" value="" style="text-transform:uppercase">
                            </div>
                            
                            <hr class="wide" />
                            <div class="form-group"> 
                            	<label for="reservation[remarks]">Note/Remarks:</label>
                                <textarea name="reservation[remarks]" rows="2" placeholder="Remarks" class="form-control"></textarea>
                            </div>
                            
                        </div>
                    </div> 
                    
                </div>
           </div>
			<div class="modal-footer">
    			<button class="btn btn-default " data-dismiss="modal"  type="button">Cancel</button>
                <button class="btn btn-primary" type="submit">Update</button>
			</div>
            </form>
		</div>
	</div>
</div>