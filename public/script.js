if (!String.prototype.trim) {
    String.prototype.trim = function () {
        return this.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '');
    };
}

if (!Element.prototype.remove) {
    Element.prototype.remove = function () {
        this.parentNode.removeChild(this);
    };
}

window.onload = function () {
    var left = document.getElementById('left'),
        output = document.getElementById('output'),
        loader = document.getElementById('loader'),
        editors = [],
        postJson = function (url, urlQuery, done) {
            var request = new XMLHttpRequest();
            request.open('POST', url, true);
            request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
            request.onload = function () {
                if (request.status >= 200 && request.status < 400) {
                    done(JSON.parse(request.responseText));
                }
            };
            request.send(urlQuery);
        },
        refresh = function () {
            var urlQuery = [];

            for (var i = 0; i < editors.length; i++) {
                var value = editors[i].getValue().toString();
                if (value.trim()) {
                    urlQuery.push('yaml[]=' + encodeURIComponent(value));
                }
            }

            loader.style.display = 'block';
            postJson(
                'process.php',
                urlQuery.join('&'),
                function (result) {
                    loader.style.display = 'none';
                    if (result.code) {
                        CodeMirror.runMode(result.code, 'text/x-php', output);
                    } else {
                        if (result.error) {
                            console.error(result.error);
                        }

                        alert('An error has occurred (see details in console).');
                    }
                }
            );
        },
        removeEmptyEditors = function () {
            for (var i = editors.length - 1; i >= 0; i--) {
                if (editors[i].getValue().toString().trim() === '') {
                    editors[i].getWrapperElement().remove();
                    editors.splice(i, 1);
                }
            }
        },
        addEmptyEditor = function () {
            var editor = CodeMirror(left, {mode: "yaml"});
            editors.push(editor);
            editor.on('changes', function () {
                removeEmptyEditors();
                addEmptyEditor();
                refresh();
            });
        };

    addEmptyEditor();
    refresh();
};
