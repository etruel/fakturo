jQuery(document).ready(function() {
	jQuery('.wrap h1').html('Fakturo Add-Ons Plugins');
	var $all = jQuery('.subsubsub .all a').attr('href');
	var $act = jQuery('.subsubsub .active a').attr('href');
	var $ina = jQuery('.subsubsub .inactive a').attr('href');
	var $rec = jQuery('.subsubsub .recently_activated a').attr('href');
	var $upg = jQuery('.subsubsub .upgrade a').attr('href');
	jQuery('.subsubsub .all a').attr('href',$all+'&page=fakturo');
	jQuery('.subsubsub .active a').attr('href',$act+'&page=fakturo');
	jQuery('.subsubsub .inactive a').attr('href',$ina+'&page=fakturo');
	jQuery('.subsubsub .recently_activated a').attr('href',$rec+'&page=fakturo');
	jQuery('.subsubsub .upgrade a').attr('href',$upg+'&page=fakturo');

});