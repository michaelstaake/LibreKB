(function () {
    var list = document.getElementById('editor-toolbar-list');
    var preview = document.getElementById('editor-toolbar-preview');
    var summary = document.getElementById('editor-toolbar-summary');
    var reset = document.getElementById('editor-toolbar-reset');
    if (!list || !preview || !summary) {
        return;
    }

    var sections = ['History', 'Format', 'Alignment', 'Structure', 'Insert', 'Tools'];

    function rows() {
        return Array.prototype.slice.call(list.querySelectorAll('.tb-item'));
    }

    function headers() {
        var map = {};
        Array.prototype.forEach.call(list.querySelectorAll('.tb-group'), function (header) {
            map[header.getAttribute('data-section')] = header;
        });
        return map;
    }

    function updateArrows() {
        var items = rows();
        items.forEach(function (row, index) {
            var up = row.querySelector('[data-move="up"]');
            var down = row.querySelector('[data-move="down"]');
            if (up) {
                up.disabled = index === 0;
            }
            if (down) {
                down.disabled = index === items.length - 1;
            }
        });
    }

    function updateBadges() {
        rows().forEach(function (row) {
            var box = row.querySelector('input[type="checkbox"]');
            var badge = row.querySelector('.toolbar-added');
            if (!box || !badge) {
                return;
            }
            var show = box.checked && row.getAttribute('data-default') !== '1';
            if (show) {
                badge.removeAttribute('hidden');
            } else {
                badge.setAttribute('hidden', 'hidden');
            }
        });
    }

    function addedCount() {
        var count = 0;
        rows().forEach(function (row) {
            var box = row.querySelector('input[type="checkbox"]');
            if (box && box.checked && row.getAttribute('data-default') !== '1') {
                count += 1;
            }
        });
        return count;
    }

    function renderPreview() {
        preview.replaceChildren();
        var previous = '';
        rows().forEach(function (row) {
            var box = row.querySelector('input[type="checkbox"]');
            if (!box || !box.checked) {
                return;
            }
            var group = row.getAttribute('data-group') || '';
            if (previous && group !== previous) {
                var separator = document.createElement('span');
                separator.className = 'tb-sep';
                preview.appendChild(separator);
            }
            previous = group;

            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'tb-btn';
            button.tabIndex = -1;
            var kind = row.getAttribute('data-preview');
            if (kind === 'wide') {
                button.classList.add('tb-wide');
                button.appendChild(document.createTextNode((row.getAttribute('data-preview-text') || '') + ' '));
                var chevron = document.createElement('i');
                chevron.className = 'bi bi-chevron-down';
                button.appendChild(chevron);
            } else if (kind === 'color') {
                var mark = document.createElement('span');
                mark.className = 'tb-color';
                mark.textContent = 'A';
                button.appendChild(mark);
            } else {
                var icon = document.createElement('i');
                icon.className = 'bi bi-' + (row.getAttribute('data-icon') || 'circle');
                button.appendChild(icon);
            }
            preview.appendChild(button);
        });

        var added = addedCount();
        var sentence;
        if (added === 0) {
            sentence = 'No extra buttons are added.';
        } else if (added === 1) {
            sentence = '1 button is added to the default toolbar.';
        } else {
            sentence = added + ' buttons are added to the default toolbar.';
        }
        summary.textContent = sentence + ' Authors see this on Create Article and Edit Article.';
    }

    function move(row, delta) {
        var items = rows();
        var index = items.indexOf(row);
        var target = items[index + delta];
        if (!row.parentNode || !target) {
            return;
        }
        if (delta < 0) {
            row.parentNode.insertBefore(row, target);
        } else {
            row.parentNode.insertBefore(row, target.nextSibling);
        }
        updateArrows();
        renderPreview();
    }

    list.addEventListener('click', function (event) {
        var control = event.target.closest('[data-move]');
        if (!control || !list.contains(control)) {
            return;
        }
        var row = control.closest('.tb-item');
        if (!row) {
            return;
        }
        move(row, control.getAttribute('data-move') === 'up' ? -1 : 1);
    });

    list.addEventListener('change', function (event) {
        if (event.target && event.target.matches('input[type="checkbox"]')) {
            updateBadges();
            renderPreview();
        }
    });

    var dragging = null;

    list.addEventListener('dragstart', function (event) {
        var grip = event.target.closest('.tb-grip');
        var row = grip ? grip.closest('.tb-item') : null;
        if (!row) {
            return;
        }
        dragging = row;
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', row.getAttribute('data-id') || '');
        row.classList.add('tb-dragging');
    });

    list.addEventListener('dragend', function () {
        if (dragging) {
            dragging.classList.remove('tb-dragging');
        }
        dragging = null;
        updateArrows();
        renderPreview();
    });

    list.addEventListener('dragover', function (event) {
        if (!dragging) {
            return;
        }
        var row = event.target.closest('.tb-item');
        var header = event.target.closest('.tb-group');
        if (row && row !== dragging) {
            event.preventDefault();
            var rect = row.getBoundingClientRect();
            var after = event.clientY > rect.top + (rect.height / 2);
            row.parentNode.insertBefore(dragging, after ? row.nextSibling : row);
            return;
        }
        if (header) {
            event.preventDefault();
            header.parentNode.insertBefore(dragging, header.nextSibling);
        }
    });

    if (reset) {
        reset.addEventListener('click', function () {
            var sectionHeaders = headers();
            var items = rows().sort(function (a, b) {
                return Number(a.getAttribute('data-order')) - Number(b.getAttribute('data-order'));
            });
            sections.forEach(function (section) {
                if (sectionHeaders[section]) {
                    list.appendChild(sectionHeaders[section]);
                }
                items.forEach(function (item) {
                    if (item.getAttribute('data-section') !== section) {
                        return;
                    }
                    list.appendChild(item);
                    var box = item.querySelector('input[type="checkbox"]');
                    if (box) {
                        box.checked = item.getAttribute('data-default') === '1';
                    }
                });
            });
            updateArrows();
            updateBadges();
            renderPreview();
        });
    }

    updateArrows();
    updateBadges();
    renderPreview();
})();
