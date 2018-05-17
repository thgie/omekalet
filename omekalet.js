(function () {

    omeka.url          = omeka.url.replace(/\/$/, '') + '/';
    omeka.api_url      = omeka.url + 'api/';
    omeka.omekalet_url = omeka.omekalet_url.replace(/\/$/, '') + '/';

    var init = function () {

        var link = create('link');
        link.rel = 'stylesheet';
        link.href = omeka.omekalet_url + 'omekalet.css';
        query('head').appendChild(link);

        request(omeka.api_url + 'collections?key=' + omeka.api_key, 'GET', function (res) {
            omeka.collections = JSON.parse(res);
            ui();
        })
    };

    var ui = function () {
        var wrapper = create('div', {classes: 'omekalet-wrapper', data: {omekalet: ''}}),
            left = create('div', {classes: 'left', data: {left: ''}}),
            middle = create('div', {classes: 'middle', data: {middle: ''}}),
            right = create('div', {classes: 'right', data: {right: ''}}),
            collections = create('select', {id: 'collections', data: {'collections': ''}}),
            tags = create('input', {type: 'text', placeholder: 'Tags, Comma separated', data: {tags: ''}}),
            type = create('select', {id: 'type', data: {'type': ''}}),
            title = create('input', {type: 'text', placeholder: 'Title', data: {title: ''}}),
            description = create('textarea', {placeholder: 'Description', data: {description: ''}}),
            creator = create('input', {type: 'text', placeholder: 'Creator', data: {creator: ''}}),
            url = create('input', {type: 'text', placeholder: 'URL', data: {url: ''}}),
            files = create('input', {type: 'file', name: 'files[]', multiple: '', data: {files: ''}}),
            article = create('input', {type: 'checkbox', name: 'article', data: {article: ''}}),
            screenshot = create('input', {type: 'checkbox', name: 'screenshot', data: {screenshot: ''}}),
            button = create('button', {inner: 'Add Item to Omeka', data: {send: ''}}),
            status = create('p', {classes: 'status', data: {status: ''}});

        type.appendChild(create('option', {value: 11, inner: 'Hyperlink'}));
        type.appendChild(create('option', {value: 1, inner: 'Text'}));

        each(omeka.collections, function (el) {
            collections.appendChild(
                create('option', {inner: el.element_texts[0].text, value: el.id})
            )
        });

        title.value = query('title').innerHTML;
        each(query('meta'), function (el) {
            if (['description', 'Description', 'twitter:description', 'fb:description'].indexOf(el.getAttribute('name')) > -1
                || ['og:description'].indexOf(el.getAttribute('property')) > -1) {
                description.value = el.getAttribute('content')
            }
        });
        url.value = location.href;

        left.appendChild(create('label', {inner: 'Collection'}));
        left.appendChild(collections);
        left.appendChild(create('label', {inner: 'Tags'}));
        left.appendChild(tags);
        left.appendChild(create('label', {inner: 'Type'}));
        left.appendChild(type);
        left.appendChild(create('label', {inner: 'URL', data: {urltext: ''}}));
        left.appendChild(url);

        middle.appendChild(create('label', {inner: 'Title'}));
        middle.appendChild(title);
        middle.appendChild(create('label', {inner: 'Description'}));
        middle.appendChild(description);
        middle.appendChild(create('label', {inner: 'Creator'}));
        middle.appendChild(creator);

        right.appendChild(create('label', {inner: 'Files'}));
        right.appendChild(files);
        right.appendChild(create('label', {inner: ' Save Article Text?', for: 'article'}));
        right.appendChild(article);
        right.appendChild(create('br'));
        right.appendChild(create('label', {inner: ' Save Screenshot?', for: 'screenshot'}));
        right.appendChild(screenshot);
        right.appendChild(create('br'));
        right.appendChild(create('br'));
        right.appendChild(button);
        right.appendChild(status);

        wrapper.appendChild(left);
        wrapper.appendChild(middle);
        wrapper.appendChild(right);

        wrapper.appendChild(create('div', {inner: '+', classes: 'show-button'}));

        query('body').appendChild(wrapper);

        setTimeout(function () {
            wrapper.className += ' show';
        }, 50);

        events();
    }

    var events = function () {
        // todo: nice feature, but needs to be retought
        /*window.addEventListener('mouseup', function () {
            var selection = '';
            if (window.getSelection) {
                selection = window.getSelection().toString();
            } else if (document.selection && document.selection.type != "Control") {
                selection = document.selection.createRange().text;
            }
            if (selection.length > 0) {
                var type = query('[data-omekalet] [data-type]'),
                    middle = query('[data-omekalet] [data-middle]'),
                    text = query('[data-omekalet] [data-text]');

                if (text.length === 0) {
                    text = create('textarea', {data: {text: ''}});
                    middle.appendChild(text)
                }

                if (query('[data-omekalet] [data-url]').length != 0) {
                    var url = query('[data-omekalet] [data-url]');
                    if (query('[data-omekalet] [data-url]') !== null)
                        url.parentNode.removeChild(url);
                }

                type.selectedIndex = 1;
                text.value = selection;

                if (window.getSelection) {
                    if (window.getSelection().empty) {  // Chrome
                        window.getSelection().empty();
                    } else if (window.getSelection().removeAllRanges) {  // Firefox
                        window.getSelection().removeAllRanges();
                    }
                } else if (document.selection) {  // IE?
                    document.selection.empty();
                }
            }
        })*/
        query('[data-omekalet] [data-type]').addEventListener('change', function () {
            var value = this.options[this.selectedIndex].value,
                left = query('[data-omekalet] [data-left]'),
                url = query('[data-omekalet] [data-url]'),
                text = query('[data-omekalet] [data-text]'),
                urltext = query('[data-omekalet] [data-urltext]'),
                article = query('[data-omekalet] [data-article]');

            if (value === '11') {
                urltext.innerHTML = 'URL';
                article.checked = false;
                if (url.length === 0) {
                    url = create('input', {type: 'text', data: {url: ''}});
                    left.appendChild(url)
                }

                if (text.length != 0) {
                    text.parentNode.removeChild(text);
                }

                url.value = location.href;
            }
            if(value === '1') {
                urltext.innerHTML = 'Text';
                if (text.length === 0) {
                    left.appendChild(create('textarea', {data: {text: ''}}))
                }

                if (url.length != 0) {
                    url.parentNode.removeChild(url);
                }
            }
        });
        query('[data-omekalet] [data-article]').addEventListener('change', function () {

            var url = query('[data-omekalet] [data-url]'),
                text = query('[data-omekalet] [data-text]'),
                urltext = query('[data-omekalet] [data-urltext]'),
                left = query('[data-omekalet] [data-left]');

            if(this.checked){
                query('[data-omekalet] [data-type]').selectedIndex = 1;

                urltext.innerHTML = 'Text';
                if (text.length === 0) {
                    text = create('textarea', {data: {text: ''}});
                    left.appendChild(text);
                }

                if (url.length != 0) {
                    url.parentNode.removeChild(url);
                }

                if (url.length != 0) { url.className = 'hide'; }
                if (text.length != 0) { text.className = 'hide'; }
                urltext.className = 'hide';
            } else {
                if (url.length != 0) { url.className = ''; }
                if (text.length != 0) { text.className = ''; }
                urltext.className = '';
            }
        });
        query('[data-omekalet] [data-send]').addEventListener('click', function () {

            var collections = query('[data-omekalet] [data-collections]'),
                collection = collections.options[collections.selectedIndex].value,
                type = query('[data-omekalet] [data-type]').value || 11,
                tags = query('[data-omekalet] [data-tags]').value || '',
                files = query('[data-omekalet] [data-files]').files;

            var data = new FormData();

            data.append('title', (query('[data-omekalet] [data-title]').value || ''));
            data.append('description', (query('[data-omekalet] [data-description]').value || ''));
            data.append('creator', (query('[data-omekalet] [data-creator]').value || ''));

            if (type === '11') {
                data.append('hyperlink', (query('[data-omekalet] [data-url]').value || ''))
            }
            if (type === '1') {
                data.append('text', (query('[data-omekalet] [data-text]').value || ''));
            }
            if (tags.length > 0) {
                data.append('tags', (query('[data-omekalet] [data-tags]').value || ''))
            }
            if(query('[data-omekalet] [data-article]').checked){
                data.append('extract_article', 'true');
            }
            if(query('[data-omekalet] [data-screenshot]').checked){
                data.append('make_screenshot', 'true');
                data.append('screenshot_url', omeka.screenshot_url);
            }

            data.append('item_type', type);
            data.append('collection', collection);
            data.append('source', location.href);
            data.append('base_url', omeka.url);
            data.append('api_key', omeka.api_key);

            for (var f in files) {
                if (files[f].name) {
                    data.append("files[]", files[f]);
                }
            }

            var c = 0, st = setInterval(function(){
                c++;
                document.querySelector('[data-omekalet] [data-status]').innerHTML = 'Adding Item / Uploading Files ' + Array(c % 5).join('.')
            }, 250);

            request(omeka.omekalet_url, 'POST', function (res) {
                clearInterval(st);
                quit();
            }, null, data);
        })
    }

    /* helper functions */
    var request = function (url, action, cb, error, data, content_type) {

        content_type = content_type || '';

        var request = new XMLHttpRequest();
        request.open(action, url, true);
        request.onreadystatechange = function () {
            if (this.readyState === 4) {
                if (this.status >= 200 && this.status < 400) {
                    cb(this.responseText);
                } else {
                    if (error) {
                        error(this);
                    } else {
                        console.log(this)
                    }
                }
            }
        };
        if (data) {
            if (content_type.length) {
                request.setRequestHeader('Content-Type', content_type);
            }
            request.send(data);
        } else {
            request.send();
        }
        request = null;
    };

    var create = function (el, attr) {

        attr = attr || {};

        var element = document.createElement(el);

        if (attr.id != null)
            element.id = attr.id;
        if (attr.classes != null)
            element.className = attr.classes;
        if (attr.inner != null)
            element.innerHTML = attr.inner;
        if (attr.href != null)
            element.href = attr.href;
        if (attr.src != null)
            element.src = attr.src;
        if (attr.type != null)
            element.type = attr.type;
        if (attr.placeholder != null)
            element.placeholder = attr.placeholder;
        if (attr.for != null)
            element.setAttribute('for', attr.for);
        if (attr.value != null)
            element.value = attr.value;
        if (attr.multiple != null)
            element.setAttribute('multiple', attr.multiple);
        if (attr.data != null) {
            for (var d in attr.data) {
                element.setAttribute('data-' + d, attr.data[d]);
            }
        }

        return element
    };

    var each = function (elements, cb) {
        Array.prototype.forEach.call(elements, cb)
    }

    var query = function () {
        var query = document.querySelectorAll(arguments[0]);
        if (query.length === 1) {
            return query[0];
        } else {
            return query;
        }
    }

    var clone = function (obj) {
        return JSON.parse(JSON.stringify(obj));
    }

    var quit = function () {
        var omekalet_wrapper = query('[data-omekalet]');
        omekalet_wrapper.className = omekalet_wrapper.className.replace('show', '');
        setTimeout(function () {
            omekalet_wrapper.parentNode.removeChild(omekalet_wrapper);
        }, 500);
    }

    init()

})();