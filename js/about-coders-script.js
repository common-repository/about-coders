function filter_the_sizes(){
	jQuery(document).ready(function($){
		jQuery.post(
			ajaxurl,
			{
				'action': 'mon_action',
				'param': 'coucou'
			},
			function(response){
				// on affiche la réponse ou l'on veut
				$('.display_about_coders').append(response);
			}
		);
	});
}