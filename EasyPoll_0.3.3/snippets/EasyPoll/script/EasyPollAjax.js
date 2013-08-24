/**
 * Script part for EasyPoll Snippet.
 * Enables the Poll to be updated via AJAX
 * This was written to work with the mootools library that comes with MODx
 *
 * @author banal
 * @version 0.2 (2008-02-08)
 */
var EasyPollAjax = new Class({
	initialize: function(identifier, url){
		if(typeof identifier != 'string' || typeof url != 'string'){
			alert('EasyPoll Constructor: invalid arguments');
			return;
		};

		this.url = url;
		this.identifier = identifier;
		this.handlers = new Array();

		this.pollxhr = new XHR({
			method: 'post',
			onSuccess: this.callbackHandler.bind(this),
			onRequest: this.callbackHandler.bind(this),
			headers: {"Content-type": "application/x-www-form-urlencoded; charset=utf-8"}
		});

		if($(identifier)){
			$(identifier + 'ajx').value = '1';
		};
	},

	/** register a button to fire a ajax request */
	registerButton: function(button){
		if(button == 'submit' || button == 'result' || button == 'vote'){
			if(!$(this.identifier + button)){ return; };

			$(this.identifier + button).onclick = function(event){
				var event = new Event(event).stopPropagation();
				event.preventDefault();
				this.pollxhr.send(this.url, $(this.identifier +'form').toQueryString() + '&' + button + '=1');
				return false;
			}.bind(this);
		};
	},

	/** register a callback method that will be called upon request and upon success */
	registerCallback: function(callback){
		if(typeof callback == 'function')
			this.handlers.push(callback);
	},

	/** distributes response from XHR object to the registered callbacks */
	callbackHandler: function(response){
		if(response == undefined){
			this.handlers.each(function(func){
				func(false, this.identifier);
			}.bind(this));
		} else {
			this.handlers.each(function(func){
				func(response, this.identifier);
			}.bind(this));
		}
	}
});

var EasyPoll_DefaultCallback = function(response, id){
	if(response == false){
		$(id + "submit").disabled = true;
	} else {
		$(id).setHTML(response);
	}
}