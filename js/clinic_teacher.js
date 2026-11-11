$(function () {
    var API = 'ajax/clinic_ajax_records.php', TID = window.CLINIC_TEACHER_ID, TYPES = window.CLINIC_TYPES;
    var $tabs = $('#type-tabs'), $panes = $('#type-panes'), $modal = $('#rec-modal'), $form = $('#rec-form'), $msg = $('#rec-msg');
    var data = {}, current = null, first = true;

    function esc(s) { return $('<div>').text(s == null ? '' : s).html(); }

    $.each(TYPES, function (key, cfg) {
        $tabs.append('<li' + (first ? ' class="active"' : '') + '><a href="#pane-' + key + '" data-toggle="tab">' + esc(cfg.label) + ' <span class="badge" id="count-' + key + '">0</span></a></li>');
        $panes.append('<div class="tab-pane' + (first ? ' active' : '') + '" id="pane-' + key + '">' +
            '<button class="btn btn-primary btn-sm btn-add" data-type="' + key + '" style="margin-bottom:10px">+ Add</button>' +
            '<div class="table-responsive"><table class="table table-striped table-bordered table-condensed" id="table-' + key + '"></table></div></div>');
        first = false;
        load(key);
    });

    function load(key) {
        $.getJSON(API, { action: 'list', type: key, teacher_id: TID }).done(function (r) {
            data[key] = r.rows;
            render(key);
        });
    }

    function render(key) {
        var cfg = TYPES[key], rows = data[key] || [];
        var html = '<thead><tr>';
        $.each(cfg.fields, function (i, f) { html += '<th>' + esc(f.label) + '</th>'; });
        html += '<th>Notes</th><th style="width:110px"></th></tr></thead><tbody>';
        if (!rows.length) { html += '<tr><td colspan="' + (cfg.fields.length + 2) + '" class="text-muted text-center">No records yet.</td></tr>'; }
        $.each(rows, function (idx, row) {
            html += '<tr>';
            $.each(cfg.fields, function (i, f) { html += '<td>' + esc(row[f.name]) + '</td>'; });
            html += '<td>' + esc(row.notes) + '</td>' +
                '<td><button class="btn btn-xs btn-default btn-edit" data-type="' + key + '" data-idx="' + idx + '">Edit</button> ' +
                '<button class="btn btn-xs btn-danger btn-del" data-type="' + key + '" data-idx="' + idx + '">Delete</button></td></tr>';
        });
        $('#table-' + key).html(html + '</tbody>');
        $('#count-' + key).text(rows.length);
    }

    function openForm(key, row) {
        current = { type: key, id: row ? row.id : 0 };
        var cfg = TYPES[key], html = '';
        $('#rec-title').text((row ? 'Edit ' : 'Add ') + cfg.label.toLowerCase());
        $.each(cfg.fields, function (i, f) {
            var v = row ? (row[f.name] || '') : '';
            html += '<div class="form-group"><label>' + esc(f.label) + (f.required ? ' *' : '') + '</label>' +
                '<input name="' + f.name + '" class="form-control" type="' + (f.type === 'date' ? 'date' : 'text') + '"' +
                (f.lookup ? ' data-lookup="' + f.lookup + '"' : '') + (f.required ? ' required' : '') +
                (f.max ? ' maxlength="' + f.max + '"' : '') + ' value="' + esc(v).replace(/"/g, '&quot;') + '"></div>';
        });
        html += '<div class="form-group"><label>Notes</label><textarea name="notes" class="form-control" rows="3">' + esc(row ? row.notes : '') + '</textarea></div>';
        $('#rec-fields').html(html);
        $.clinicInitLookups($('#rec-fields'));
        $msg.hide();
        $modal.modal('show');
    }

    $panes.on('click', '.btn-add', function () { openForm($(this).data('type'), null); });
    $panes.on('click', '.btn-edit', function () { var k = $(this).data('type'); openForm(k, data[k][$(this).data('idx')]); });
    $panes.on('click', '.btn-del', function () {
        var k = $(this).data('type');
        var row = data[k][$(this).data('idx')];
        if (!confirm('Delete this record?')) { return; }
        $.post(API, { action: 'delete', type: k, id: row.id }, null, 'json').done(function () { load(k); })
         .fail(function (x) { alert((x.responseJSON && x.responseJSON.message) || 'Could not delete.'); });
    });

    $form.on('submit', function (e) {
        e.preventDefault();
        $msg.hide();
        var payload = $form.serialize() + '&action=save&type=' + current.type + '&id=' + current.id + '&teacher_id=' + TID;
        $.post(API, payload, null, 'json').done(function () {
            $modal.modal('hide'); load(current.type);
        }).fail(function (x) { $msg.text((x.responseJSON && x.responseJSON.message) || 'Could not save.').show(); });
    });
});
