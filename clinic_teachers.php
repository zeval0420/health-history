<?php
include('../credentials/db_connection.php');
include('helperFiles/clinic_session_handler.php');
clinic_require_login();
$pageTitle = 'Teachers';
$pageAssets = ['datatables'];
include('helperFiles/clinic_header.php');
?>
<div class="clearfix" style="margin-bottom:15px">
  <h3 class="pull-left" style="margin:0">Teachers</h3>
  <button class="btn btn-primary pull-right" id="btn-add">+ Add teacher</button>
</div>

<div class="table-responsive">
  <table id="teachers" class="table table-striped table-bordered" style="width:100%">
    <thead><tr><th>Emp. no.</th><th>Name</th><th>Position</th><th>Department</th><th>Contact</th><th>Records</th><th style="width:170px"></th></tr></thead>
  </table>
</div>

<div class="modal fade" id="teacher-modal" tabindex="-1">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <form id="teacher-form" autocomplete="off">
      <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" id="modal-title">Add teacher</h4></div>
      <div class="modal-body">
        <div id="form-msg" class="alert alert-danger" style="display:none"></div>
        <input type="hidden" name="id" value="">
        <input type="hidden" name="source" value="manual">
        <input type="hidden" name="force" value="">

        <div class="autofill-box" id="autofill-box">
          <label for="autofill">Autofill from reserve-a-lab (optional)</label>
          <input type="text" id="autofill" class="form-control" placeholder="Type a name or employee no. to find an existing teacher, or skip and fill in the fields below">
        </div>

        <div class="row">
          <div class="form-group col-sm-3"><label>Employee no.</label><input name="employee_no" class="form-control" maxlength="20"></div>
          <div class="form-group col-sm-3"><label>Last name *</label><input name="last_name" class="form-control" maxlength="100" required></div>
          <div class="form-group col-sm-3"><label>First name *</label><input name="first_name" class="form-control" maxlength="100" required></div>
          <div class="form-group col-sm-3"><label>Middle name</label><input name="middle_name" class="form-control" maxlength="100"></div>
        </div>
        <div class="row">
          <div class="form-group col-sm-4"><label>Position</label><input name="position" data-lookup="position" class="form-control" maxlength="100"></div>
          <div class="form-group col-sm-4"><label>Department</label><input name="department" data-lookup="department" class="form-control" maxlength="100"></div>
          <div class="form-group col-sm-4"><label>Email</label><input type="email" name="email" class="form-control" maxlength="100"></div>
        </div>
        <div class="row">
          <div class="form-group col-sm-3"><label>Date of birth</label><input type="date" name="dob" class="form-control"></div>
          <div class="form-group col-sm-3"><label>Sex</label><input name="sex" data-lookup="sex" class="form-control" maxlength="20"></div>
          <div class="form-group col-sm-3"><label>Blood type</label><input name="blood_type" data-lookup="blood_type" class="form-control" maxlength="10"></div>
          <div class="form-group col-sm-3"><label>Contact no.</label><input name="contact" class="form-control" maxlength="100"></div>
        </div>
        <div class="form-group"><label>Emergency contact</label><input name="emergency_contact" class="form-control" maxlength="255" placeholder="Name, relationship, number"></div>
        <div class="form-group"><label>Notes</label><textarea name="notes" class="form-control" rows="3"></textarea></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </form>
  </div></div>
</div>

<script src="js/clinic_autocomplete.js"></script>
<script src="js/clinic_teachers.js"></script>
<?php include('helperFiles/clinic_footer.php'); ?>
