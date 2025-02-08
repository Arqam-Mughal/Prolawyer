<div class="modal fade" id="customPreviewModal" tabindex="-1" role="dialog" aria-labelledby="customModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="customModalLabel">Quick View</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label for="parties" class="col-form-label">Parties</label>
          <input type="text" class="form-control" id="parties" readonly>
        </div>
        <div class="form-group">
          <label for="case_number" class="col-form-label">Case No.</label>
          <input type="text" class="form-control" id="case_number" readonly>
        </div>
        <div class="form-group">
          <label for="remarks" class="col-form-label">Remarks</label>
          <textarea type="text" class="form-control" id="remarks" readonly></textarea>
        </div>
        <div class="form-group">
          <label for="tags" class="col-form-label">Case Labels</label>
          <input type="text" class="form-control" id="tags" readonly>
        </div>
        <div class="form-group">
          <label for="assign_to" class="col-form-label">Assign To</label>
          <input type="text" class="form-control" id="assign_to" readonly>
        </div>
        <div class="form-group">
          <label for="brief_for" class="col-form-label">Brief For</label>
          <input type="text" class="form-control" id="brief_for" readonly>
        </div>
        <div class="form-group">
          <label for="connected_matter" class="col-form-label">Connected Matters</label>
          <input type="text" class="form-control" id="connected_matter" readonly>
        </div>
      </div>
      <div class="modal-footer">
        <a class="btn btn-primary" id="case-preview-button" href="" target="_blank">view</a>
        <a class="btn btn-primary" id="case-edit-button" href="" target="_blank">Edit</a>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>