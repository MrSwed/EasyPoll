/**
 * Script part for EasyPoll Snippet.
 * Enables the Poll to be updated via AJAX
 * This was written to work with the mootools library that comes with MODx
 *
 * @author banal, vanchelo <brezhnev.ivan@yahoo.com>
 * @version 0.3.4 <2014-09-23>
 */
var EasyPollAjax = function EasyPollAjax(identifier, url) {
    if (typeof identifier != 'string' || typeof url != 'string') {
        alert('EasyPoll Constructor: invalid arguments');
        return;
    }

    this.url = url;
    this.identifier = '#' + identifier;
    this.handlers = [];

    if ($(this.identifier)) {
        $(this.identifier + 'ajx').val('1');
    }
};

/**
 * Кegister a button to fire a ajax request
 *
 * @param button
 */
EasyPollAjax.prototype.registerButton = function (button) {
    if (button == 'submit' || button == 'result' || button == 'vote') {
        if (!$(this.identifier).find('[name=' + button + ']')) {
            alert('hello');
            return;
        }

        $(this.identifier).on('click', '[name=' + button + ']', function (e) {
            e.preventDefault();
            if (button == 'submit' && $(this.identifier).find('input:checked').length != 1) {
                return false;
            }
            $.ajax({
                method: 'post',
                global: false,
                url: this.url,
                data: $(this.identifier).find('form').serialize() + '&' + button + '=1',
                headers: {
                    "Content-type": "application/x-www-form-urlencoded; charset=utf-8"
                },
                success: this.callbackHandler.bind(this),
                error: this.callbackHandler.bind(this)
            });
            return false;
        }.bind(this));
    }
};

/**
 * Register a callback method that will be called upon request and upon success
 *
 * @param callback
 */
EasyPollAjax.prototype.registerCallback = function (callback) {
    if (typeof callback == 'function') {
        this.handlers.push(callback);
    }
};

/**
 * Distributes response from XHR object to the registered callbacks
 *
 * @param response
 */
EasyPollAjax.prototype.callbackHandler = function (response) {
    if (response == undefined) {
        this.handlers.forEach(function (func) {
            func(false, this.identifier);
        }.bind(this));
    } else {
        this.handlers.forEach(function (func) {
            func(response, this.identifier);
        }.bind(this));
    }
};

var EasyPoll_DefaultCallback = function (response, id) {
    if (response == false) {
        $(id).find('submit').prop('disabled', true);
    } else {
        $(id).html(response);
    }
};
