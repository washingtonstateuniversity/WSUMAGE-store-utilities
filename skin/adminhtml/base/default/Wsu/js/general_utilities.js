var WSU=WSU||{};

(function($){
	WSU={
		alertThings:function($mess){
			alert($mess);
		},
		ajaxManager: (function() {
			var requests = [];
			var requests_obj = {};
			 return {
				addReq:  function(action,opt) {
					if( $.inArray(action, requests) > -1 ){
						//not this assums that the first one is what we wnt to use
					}else{
						requests.push(action);
						requests_obj[action]=opt;
					}
				},
				removeReq:  function(action,opt) {
					if( $.inArray(opt, requests) > -1 ){
						requests.splice($.inArray(action, requests), 1);
						delete requests_obj[action]; 
					}
				},
				run: function() {
					var self = this, oriSuc;
		
					if( requests.length ) {
						var action = requests[0];
						var post_obj = requests_obj[action];
						oriSuc = post_obj.complete;
		
						post_obj.complete = function() {
							 if( typeof(oriSuc) === 'function' ) oriSuc();
							 requests.shift();
							 self.run.apply(self, []);
						};   
		
						$.ajax(post_obj);
					} else {
					  self.tid = setTimeout(function() {
						 self.run.apply(self, []);
					  }, 200);
					}
				},
				stop:  function() {
					requests = [];
					requests_obj = {};
					clearTimeout(this.tid);
				}
			 };
		}()),
	};
	
	
	
	/*$(function(){
		$('body').css({'overview':'hidden'});
		WSU.alertThings("hello");
	});*/
})(jQuery);

