$(function () {
    var API = 'ajax/clinic_ajax_teachers.php';
    var $modal = $('#teacher-modal'), $form = $('#teacher-form'), $msg = $('#form-msg');

    function fullName(r) {
        return $('<div>').text(r.last_name + ', ' + r.first_name + (r.middle_name ? ' ' + r.middle_name : '')).html();
    }

    var table = $('#teachers').DataTable({
        ajax: API + '?action=list',
        order: [[1, 'asc']],
        pageLength: 25,
        columns: [
            { data: 'employee_no', render: function (d) { return $('<div>').text(d || '').html(); } },
            { data: null, render: function (d, t, r) { return '<a href="clinic_teacher.php?id=' + r.id + '">' + fullName(r) + '</a>'; } },
            { data: 'position', render: function (d) { return $('<div>').text(d || '').html(); } },
            { data: 'department', render: function (d) { return $('<div>').text(d || '').html(); } },
            { data: 'contact', render: function (d) { return $('<div>').text(d || '').html(); } },
            { data: 'record_count' },
            { data: null, orderable: false, searchable: false, render: function (d, t, r) {
                return '<a class="btn btn-xs btn-info" href="clinic_teacher.php?id=' + r.id + '">Records</a> ' +
                       '<button class="btn btn-xs btn-default btn-edit" data-id="' + r.id + '">Edit</button> ' +
                       '<button class="btn btn-xs btn-danger btn-del" data-id="' + r.id + '">Remove</button>';
            } }
        ]
    });

    $.clinicInitLookups($form);

    function showMsg(m) { $msg.text(m).show(); }

    function fill(t) {
        $form[0].reset();
        $form.find('[name=id]').val(t.id || '');
        $form.find('[name=source]').val(t.source || 'manual');
        $form.find('[name=force]').val('');
        $.each(['employee_no', 'last_name', 'first_name', 'middle_name', 'position', 'department', 'email', 'dob', 'sex', 'blood_type', 'contact', 'emergency_contact', 'notes'], function (i, k) {
            $form.find('[name=' + k + ']').val(t[k] || '');
        });
    }

    $('#btn-add').on('click', function () {
        fill({}); $msg.hide(); $('#autofill').val(''); $('#autofill-box').show();
        $('#modal-title').text('Add teacher'); $modal.modal('show');
    });

    $('#teachers').on('click', '.btn-edit', function () {
        $.getJSON(API, { action: 'get', id: $(this).data('id') }).done(function (r) {
            fill(r.teacher); $msg.hide(); $('#autofill-box').hide();
            $('#modal-title').text('Edit teacher'); $modal.modal('show');
        });
    });

    $('#teachers').on('click', '.btn-del', function () {
        var row = table.row($(this).closest('tr')).data();
        if (!confirm('Remove ' + row.last_name + ', ' + row.first_name + '? Their records are kept but hidden.')) { return; }
        $.post(API, { action: 'delete', id: row.id }, null, 'json').done(function () { table.ajax.reload(null, false); })
         .fail(function (x) { alert((x.responseJSON && x.responseJSON.message) || 'Could not remove.'); });
    });

    // Autofill from reserve-a-lab accounts; every field stays editable
    $('#autofill').clinicAutocomplete({
        url: 'ajax/clinic_ajax_teacher_lookup.php',
        label: function (i) { return i.label; },
        sub: function (i) { return i.sub; },
        onSelect: function (it, $in) {
            if (it.already) { showMsg(it.label + ' is already in the clinic list.'); return false; }
            $msg.hide();
            $.each(['employee_no', 'last_name', 'first_name', 'middle_name', 'position', 'email'], function (i, k) {
                $form.find('[name=' + k + ']').val(it[k] || '');
            });
            $form.find('[name=source]').val('reserve-a-lab');
            $in.val('');
            return false;
        }
    });

    $form.on('submit', function (e) {
        e.preventDefault();
        $msg.hide();
        $.post(API, $form.serialize() + '&action=save', null, 'json').done(function () {
            $modal.modal('hide'); table.ajax.reload(null, false);
        }).fail(function (x) {
            var r = x.responseJSON || {};
            if (r.status === 'duplicate_name' && confirm(r.message)) {
                $form.find('[name=force]').val('1'); $form.trigger('submit'); return;
            }
            if (r.status !== 'duplicate_name') { showMsg(r.message || 'Could not save.'); }
        });
    });
});
