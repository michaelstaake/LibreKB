(function () {
    'use strict';

    var KEY = 'librekb.admin.structure.open';
    var root = document.querySelector('.kb-tree-root');
    if (!root) {
        return;
    }

    function nodes() {
        return Array.prototype.slice.call(root.querySelectorAll('.kb-node'));
    }

    function hasChildren(node) {
        return !!node.querySelector(':scope > .kb-children');
    }

    function setOpen(node, open) {
        if (!hasChildren(node)) {
            return;
        }
        node.classList.toggle('is-collapsed', !open);
        var btn = node.querySelector(':scope > .kb-row .kb-toggle');
        if (!btn) {
            return;
        }
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        var icon = btn.querySelector('i');
        if (icon) {
            icon.className = open ? 'bi bi-chevron-down' : 'bi bi-chevron-right';
        }
        var name = node.getAttribute('data-name') || 'category';
        btn.setAttribute('aria-label', (open ? 'Collapse ' : 'Expand ') + name);
    }

    function read() {
        try {
            var parsed = JSON.parse(localStorage.getItem(KEY) || '[]');
            return Array.isArray(parsed) ? parsed.map(String) : [];
        } catch (e) {
            return [];
        }
    }

    function write(ids) {
        try {
            localStorage.setItem(KEY, JSON.stringify(ids));
        } catch (e) {
            /* Private browsing can refuse storage. The tree still works. */
        }
    }

    function currentOpen() {
        return nodes().filter(function (node) {
            return hasChildren(node) && !node.classList.contains('is-collapsed');
        }).map(function (node) {
            return node.getAttribute('data-id');
        });
    }

    function applySaved() {
        var open = read();
        nodes().forEach(function (node) {
            setOpen(node, open.indexOf(node.getAttribute('data-id')) !== -1);
        });
    }

    function filterQuery() {
        var input = document.getElementById('kb-filter');
        return input ? input.value.trim() : '';
    }

    function containsFold(text, needle) {
        text = text || '';
        if (!needle || needle.length > text.length) {
            return false;
        }
        var last = text.length - needle.length;
        for (var i = 0; i <= last; i++) {
            if (text.slice(i, i + needle.length).toLowerCase() === needle) {
                return true;
            }
        }
        return false;
    }

    function setLabel(el, needle) {
        var text = el.getAttribute('data-label');
        if (text === null) {
            return false;
        }
        while (el.firstChild) {
            el.removeChild(el.firstChild);
        }
        if (!needle) {
            el.appendChild(document.createTextNode(text));
            return false;
        }
        var found = false;
        var i = 0;
        while (i < text.length) {
            var at = -1;
            var last = text.length - needle.length;
            for (var j = i; j <= last; j++) {
                if (text.slice(j, j + needle.length).toLowerCase() === needle) {
                    at = j;
                    break;
                }
            }
            if (at === -1) {
                el.appendChild(document.createTextNode(text.slice(i)));
                break;
            }
            found = true;
            if (at > i) {
                el.appendChild(document.createTextNode(text.slice(i, at)));
            }
            var mark = document.createElement('mark');
            mark.className = 'kb-hit';
            mark.appendChild(document.createTextNode(text.slice(at, at + needle.length)));
            el.appendChild(mark);
            i = at + needle.length;
        }
        return found;
    }

    function paintLabels(query) {
        var needle = query.trim().toLowerCase();
        document.querySelectorAll('[data-label]').forEach(function (el) {
            setLabel(el, needle);
        });
    }

    function applyFilter(raw) {
        var pretty = raw.trim();
        var needle = pretty.toLowerCase();
        var status = document.getElementById('kb-filter-status');
        var empty = document.getElementById('kb-filter-empty');
        var clearBtn = document.getElementById('kb-filter-clear');
        var expand = document.getElementById('expand-all');
        var collapse = document.getElementById('collapse-all');
        var filtering = needle.length > 0;

        if (expand) {
            expand.disabled = filtering;
        }
        if (collapse) {
            collapse.disabled = filtering;
        }
        if (clearBtn) {
            clearBtn.hidden = !filtering;
        }

        root.querySelectorAll('.kb-node, .kb-article, .kb-articles, .kb-subs').forEach(function (el) {
            el.classList.remove('d-none');
        });

        if (!filtering) {
            paintLabels('');
            applySaved();
            if (status) {
                status.hidden = true;
                status.textContent = '';
            }
            if (empty) {
                empty.hidden = true;
            }
            return;
        }

        var memo = new Map();

        function directArticles(node) {
            var wrap = node.querySelector(':scope > .kb-children > .kb-articles');
            if (!wrap) {
                return [];
            }
            return Array.prototype.slice.call(wrap.querySelectorAll(':scope > .kb-article'));
        }

        function directSubs(node) {
            var wrap = node.querySelector(':scope > .kb-children > .kb-subs');
            if (!wrap) {
                return [];
            }
            return Array.prototype.slice.call(wrap.querySelectorAll(':scope > .kb-node'));
        }

        function matchType(node) {
            if (memo.has(node)) {
                return memo.get(node);
            }
            var self = containsFold(node.getAttribute('data-name') || '', needle);
            var art = directArticles(node).some(function (item) {
                return containsFold(item.getAttribute('data-title') || '', needle);
            });
            var desc = directSubs(node).some(function (sub) {
                return matchType(sub) !== 'none';
            });
            var type = self ? 'self' : art ? 'article' : desc ? 'descendant' : 'none';
            memo.set(node, type);
            return type;
        }

        function showNode(node, inherited) {
            var type = matchType(node);
            var show = inherited || type !== 'none';
            node.classList.toggle('d-none', !show);
            if (!show) {
                return;
            }
            var showAll = inherited || type === 'self';
            if (hasChildren(node)) {
                setOpen(node, true);
            }
            directArticles(node).forEach(function (item) {
                var artShow = showAll || containsFold(item.getAttribute('data-title') || '', needle);
                item.classList.toggle('d-none', !artShow);
            });
            directSubs(node).forEach(function (sub) {
                showNode(sub, showAll);
            });
        }

        Array.prototype.slice.call(root.querySelectorAll(':scope > .kb-node')).forEach(function (node) {
            showNode(node, false);
        });

        root.querySelectorAll('.kb-articles').forEach(function (wrap) {
            var any = Array.prototype.slice.call(wrap.querySelectorAll(':scope > .kb-article')).some(function (item) {
                return !item.classList.contains('d-none');
            });
            wrap.classList.toggle('d-none', !any);
        });

        root.querySelectorAll('.kb-subs').forEach(function (wrap) {
            var any = Array.prototype.slice.call(wrap.querySelectorAll(':scope > .kb-node')).some(function (node) {
                return !node.classList.contains('d-none');
            });
            wrap.classList.toggle('d-none', !any);
        });

        paintLabels(pretty);

        var hits = 0;
        root.querySelectorAll('[data-label]').forEach(function (el) {
            if (el.querySelector('mark.kb-hit') && !el.closest('.d-none')) {
                hits += 1;
            }
        });

        if (status) {
            status.hidden = hits === 0;
            status.textContent = hits === 0
                ? ''
                : hits + (hits === 1 ? ' match. ' : ' matches. ') + 'Matching rows are expanded; other categories are hidden.';
        }
        if (empty) {
            empty.hidden = hits !== 0;
        }
    }

    applySaved();

    root.addEventListener('click', function (event) {
        var toggle = event.target.closest('.kb-toggle');
        if (!toggle || !root.contains(toggle)) {
            return;
        }
        var node = toggle.closest('.kb-node');
        if (!node) {
            return;
        }
        setOpen(node, node.classList.contains('is-collapsed'));
        if (!filterQuery()) {
            write(currentOpen());
        }
    });

    var expandBtn = document.getElementById('expand-all');
    var collapseBtn = document.getElementById('collapse-all');
    if (expandBtn) {
        expandBtn.addEventListener('click', function () {
            if (filterQuery()) {
                return;
            }
            nodes().forEach(function (node) {
                setOpen(node, true);
            });
            write(currentOpen());
        });
    }
    if (collapseBtn) {
        collapseBtn.addEventListener('click', function () {
            if (filterQuery()) {
                return;
            }
            nodes().forEach(function (node) {
                setOpen(node, false);
            });
            write([]);
        });
    }

    var filter = document.getElementById('kb-filter');
    if (filter) {
        filter.addEventListener('input', function () {
            applyFilter(filter.value);
        });
        var clearBtn = document.getElementById('kb-filter-clear');
        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                filter.value = '';
                applyFilter('');
                filter.focus();
            });
        }
    }
})();
