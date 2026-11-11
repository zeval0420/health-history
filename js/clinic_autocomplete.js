/*
 * Small autocomplete for the clinic app.
 *   $('#input').clinicAutocomplete({
 *       url: 'ajax/clinic_ajax_teacher_lookup.php',   // GET ?q=...  -> {results:[...]}
 *       params: function () { return {cat: 'medication'}; },   // optional extra query params
 *       minLength: 2,
 *       label: function (item) { return item.label; },          // main line
 *       sub:   function (item) { return item.sub; },            // optional second line (small)
 *       onSelect: function (item, $input) { ... }               // return false to keep typed text
 *   });
 * Free text is always allowed: the user can ignore the list and just type.
 */
(function ($) {
    function esc(s) { return $('<div>').text(s == null ? '' : s).html(); }

    $.fn.clinicAutocomplete = function (opts) {
        opts = $.extend({ minLength: 2, delay: 200, params: null, label: function (i) { return i.label || i.value; }, sub: null, onSelect: null }, opts);
        return this.each(function () {
            var $in = $(this); if ($in.data('clAc')) { return; } $in.data('clAc', true);
            var timer = null, seq = 0, active = -1, items = [];
            if (!$in.parent().hasClass('cl-ac-wrap')) { $in.wrap('<div class="cl-ac-wrap"></div>'); }
            var $list = $('<div class="cl-ac-list" style="display:none"></div>').insertAfter($in);
            $in.attr('autocomplete', 'off');

            function close() { $list.hide().empty(); active = -1; items = []; }
            function choose(i) {
                var item = items[i]; if (!item) { return; }
                var keep = opts.onSelect ? opts.onSelect(item, $in) === false : false;
                if (!keep) { $in.val(item.value != null ? item.value : opts.label(item)); }
                close();
            }
            function render() {
                $list.empty();
                if (!items.length) { $list.hide(); return; }
                $.each(items, function (i, it) {
                    var html = esc(opts.label(it)) + (it.badge ? '<span class="label label-default">' + esc(it.badge) + '</span>' : '');
                    if (opts.sub) { html += '<small>' + esc(opts.sub(it)) + '</small>'; }
                    $('<div class="cl-ac-item"></div>').html(html).data('i', i).appendTo($list);
                });
                $list.show();
            }
            function search() {
                var q = $.trim($in.val());
                if (q.length < opts.minLength) { close(); return; }
                var my = ++seq, data = $.extend({ q: q }, opts.params ? opts.params($in) : {});
                $.getJSON(opts.url, data).done(function (r) {
                    if (my !== seq) { return; }
                    items = (r && r.results) || []; active = -1; render();
                });
            }

            $in.on('input', function () { clearTimeout(timer); timer = setTimeout(search, opts.delay); });
            $in.on('keydown', function (e) {
                if (!$list.is(':visible')) { return; }
                var n = items.length;
                if (e.which === 40) { active = (active + 1) % n; }
                else if (e.which === 38) { active = (active - 1 + n) % n; }
                else if (e.which === 13 && active >= 0) { e.preventDefault(); choose(active); return; }
                else if (e.which === 27) { close(); return; }
                else { return; }
                e.preventDefault();
                $list.children().removeClass('active').eq(active).addClass('active');
            });
            $list.on('mousedown', '.cl-ac-item', function (e) { e.preventDefault(); choose($(this).data('i')); });
            $in.on('blur', function () { setTimeout(close, 100); });
        });
    };

    /** Attach autocomplete to every <input data-lookup="category"> under root (free text stays allowed). */
    $.clinicInitLookups = function (root) {
        $(root).find('input[data-lookup]').each(function () {
            var cat = $(this).data('lookup');
            $(this).clinicAutocomplete({ url: 'ajax/clinic_ajax_lookup.php', minLength: 1, params: function () { return { cat: cat }; } });
        });
    };
})(jQuery);
