/* NIMCS helper scripts */
document.addEventListener('DOMContentLoaded', function () {
    // Enable Bootstrap tooltips
    [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        .forEach(function (el) { new bootstrap.Tooltip(el); });

    // State -> LGA cascading selects.
    // The state select carries data-lga-target="#id" (or data-lga-targets="#a,#b")
    // and data-lga-url pointing at the JSON endpoint.
    [].slice.call(document.querySelectorAll('[data-state-cascade]'))
        .forEach(function (stateSel) {
            var targets = (stateSel.getAttribute('data-lga-targets') || stateSel.getAttribute('data-lga-target') || '').split(',')
                .map(function (s) { return s.trim(); }).filter(Boolean);
            var selects = targets.map(function (t) { return document.querySelector(t); }).filter(Boolean);
            if (!selects.length) { return; }
            var url = stateSel.getAttribute('data-lga-url');

            var populate = function () {
                var stateId = stateSel.value;
                if (!stateId) {
                    selects.forEach(function (sel) {
                        sel.innerHTML = '<option value="">Select LGA</option>';
                    });
                    return;
                }
                selects.forEach(function (sel) {
                    var keep = sel.value;
                    sel.innerHTML = '<option value="">Loading&hellip;</option>';
                    fetch(url + (url.indexOf('?') >= 0 ? '&' : '?') + 'state_id=' + encodeURIComponent(stateId), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    }).then(function (r) { return r.json(); }).then(function (lgas) {
                        sel.innerHTML = '<option value="">Select LGA</option>';
                        lgas.forEach(function (l) {
                            var o = document.createElement('option');
                            o.value = l.id;
                            o.textContent = l.name;
                            sel.appendChild(o);
                        });
                        if (keep) { sel.value = keep; }
                        sel.dispatchEvent(new Event('change'));
                    }).catch(function () {
                        sel.innerHTML = '<option value="">Unable to load LGAs</option>';
                    });
                });
            };

            stateSel.addEventListener('change', populate);
            if (stateSel.value) { populate(); }
        });

    // LGA -> Ward cascading selects.
    // The LGA select carries data-ward-target="#id" and data-ward-url pointing
    // at the JSON endpoint. Fired automatically when the state cascade repopulates it.
    [].slice.call(document.querySelectorAll('[data-ward-cascade]'))
        .forEach(function (lgaSel) {
            var target = document.querySelector(lgaSel.getAttribute('data-ward-target'));
            if (!target) { return; }
            var url = lgaSel.getAttribute('data-ward-url');

            var populate = function () {
                var lgaId = lgaSel.value;
                if (!lgaId) {
                    target.innerHTML = '<option value="">Select ward</option>';
                    return;
                }
                var keep = target.value;
                target.innerHTML = '<option value="">Loading&hellip;</option>';
                fetch(url + (url.indexOf('?') >= 0 ? '&' : '?') + 'lga_id=' + encodeURIComponent(lgaId), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                }).then(function (r) { return r.json(); }).then(function (wards) {
                    target.innerHTML = '<option value="">Select ward</option>';
                    wards.forEach(function (w) {
                        var o = document.createElement('option');
                        o.value = w.id;
                        o.textContent = w.name;
                        target.appendChild(o);
                    });
                    if (keep) { target.value = keep; }
                }).catch(function () {
                    target.innerHTML = '<option value="">Unable to load wards</option>';
                });
            };

            lgaSel.addEventListener('change', populate);
            if (lgaSel.value) { populate(); }
        });

    // Confirm dialogs
    [].slice.call(document.querySelectorAll('[data-confirm]'))
        .forEach(function (el) {
            el.addEventListener('submit', function (e) {
                var message = el.getAttribute('data-confirm') || 'Are you sure? This action is recorded in the audit log.';
                if (!window.confirm(message)) { e.preventDefault(); }
            });
        });

    // NIN input mask: digits only, max 11
    [].slice.call(document.querySelectorAll('.nin-input'))
        .forEach(function (el) {
            el.addEventListener('input', function () {
                el.value = el.value.replace(/\D/g, '').slice(0, 11);
            });
        });

    // Phone input: keep E.164-ish formatting
    [].slice.call(document.querySelectorAll('.phone-input'))
        .forEach(function (el) {
            el.addEventListener('input', function () {
                var v = el.value.replace(/[^\d+]/g, '');
                if (v.startsWith('0')) { v = '+234' + v.slice(1); }
                if (v.startsWith('234')) { v = '+' + v; }
                el.value = v.slice(0, 15);
            });
        });

    // Uppercase certificate number inputs
    [].slice.call(document.querySelectorAll('.cert-number-input'))
        .forEach(function (el) {
            el.addEventListener('input', function () {
                el.value = el.value.toUpperCase().replace(/\s+/g, '');
            });
        });

    // Multi-step wizard: preserve scroll on error
    var wizardErrorSummary = document.getElementById('wizard-error-summary');
    if (wizardErrorSummary) { wizardErrorSummary.scrollIntoView({ behavior: 'smooth', block: 'center' }); }

    // Autosave indicator
    var autosaveForm = document.querySelector('[data-autosave]');
    if (autosaveForm) {
        var indicator = document.getElementById('autosave-indicator');
        var timer = null;
        var triggerSave = function () {
            if (timer) { clearTimeout(timer); }
            if (indicator) { indicator.textContent = 'Saving…'; }
            timer = setTimeout(function () {
                var data = new FormData(autosaveForm);
                data.append('autosave', '1');
                fetch(autosaveForm.getAttribute('action') || window.location.href, {
                    method: 'POST',
                    body: data,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    credentials: 'same-origin'
                }).then(function (r) { return r.json(); }).then(function (json) {
                    if (indicator) { indicator.textContent = json.saved_at ? 'Saved ' + json.saved_at : 'Saved'; }
                }).catch(function () {
                    if (indicator) { indicator.textContent = 'Not saved - check connection'; }
                });
            }, 1800);
        };
        autosaveForm.querySelectorAll('input, select, textarea').forEach(function (el) {
            el.addEventListener('change', triggerSave);
            el.addEventListener('input', triggerSave);
        });
    }

    // Searchable selects: wraps a native <select class="searchable-select"> with a
    // type-to-filter input + dropdown. The native select stays hidden and remains the
    // source of truth for the submitted value and validation. A MutationObserver keeps
    // the dropdown in sync when cascading JS replaces the option list.
    [].slice.call(document.querySelectorAll('select.searchable-select'))
        .forEach(function (select) {
            if (select.dataset.searchableReady) { return; }
            select.dataset.searchableReady = '1';

            var wrap = document.createElement('div');
            wrap.className = 'searchable-select-wrap';
            select.parentNode.insertBefore(wrap, select);
            wrap.appendChild(select);

            var input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control searchable-select-input';
            input.setAttribute('autocomplete', 'off');
            input.setAttribute('placeholder', 'Search and select\u2026');
            wrap.appendChild(input);

            var chevron = document.createElement('span');
            chevron.className = 'searchable-select-chevron';
            wrap.appendChild(chevron);

            var list = document.createElement('ul');
            list.className = 'searchable-select-list';
            list.hidden = true;
            wrap.appendChild(list);

            var options = [];

            function readOptions() {
                options = [].slice.call(select.options)
                    .filter(function (o) { return o.value && o.value !== ''; })
                    .map(function (o) { return { value: o.value, label: o.textContent.trim() }; });
            }

            function selectedLabel() {
                return select.selectedIndex >= 0 ? select.options[select.selectedIndex].textContent.trim() : '';
            }

            function render(filter) {
                list.innerHTML = '';
                filter = (filter || '').toLowerCase();
                var shown = 0;
                options.forEach(function (opt) {
                    if (filter && opt.label.toLowerCase().indexOf(filter) < 0) { return; }
                    var li = document.createElement('li');
                    li.className = 'searchable-select-option';
                    li.textContent = opt.label;
                    li.addEventListener('mousedown', function (e) { e.preventDefault(); });
                    li.addEventListener('click', function () { choose(opt.value); });
                    list.appendChild(li);
                    shown++;
                });
                if (!shown) {
                    var empty = document.createElement('li');
                    empty.className = 'searchable-select-empty';
                    empty.textContent = 'No matching option';
                    list.appendChild(empty);
                }
            }

            function choose(value) {
                select.value = value;
                var label = '';
                for (var i = 0; i < options.length; i++) {
                    if (options[i].value === value) { label = options[i].label; break; }
                }
                input.value = label;
                list.hidden = true;
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }

            input.addEventListener('focus', function () { readOptions(); render(input.value); list.hidden = false; });
            input.addEventListener('input', function () { render(input.value); list.hidden = false; });
            input.addEventListener('blur', function () { setTimeout(function () { list.hidden = true; }, 120); });
            document.addEventListener('click', function (e) { if (!wrap.contains(e.target)) { list.hidden = true; } });

            select.addEventListener('change', function () { input.value = selectedLabel(); });

            var mo = new MutationObserver(function () { readOptions(); input.value = selectedLabel(); });
            mo.observe(select, { childList: true });

            readOptions();
            input.value = selectedLabel();
        });
});
