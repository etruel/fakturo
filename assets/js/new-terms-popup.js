var current_json_save = '';
var current_ajax_popup_taxonomy_object = {taxonomy:'category', selector:'', selector_parent_select: ''};
var fktr_parents_popup = new Array();
jQuery(document).ready(function() {
	events_init_term_popup();
});

function events_init_term_popup() {
	jQuery('body').on('click', '.fktr_btn_taxonomy', function(e) {
		if(jQuery('#fktr_background_popup_taxonomy').length) {
			fktr_parents_popup.push({taxonomy: current_ajax_popup_taxonomy_object.taxonomy, selector: current_ajax_popup_taxonomy_object.selector, selector_parent_select: current_ajax_popup_taxonomy_object.selector_parent_select});
		}
		var cur_taxonomy = jQuery(this).data('taxonomy');
		if(cur_taxonomy != '' && cur_taxonomy != null) {
			current_ajax_popup_taxonomy_object.taxonomy = cur_taxonomy;
		} else {
			current_ajax_popup_taxonomy_object.taxonomy = 'category';
		}

		var cur_selector = jQuery(this).data('selector');
		if(cur_selector != '' && cur_selector != null) {
			current_ajax_popup_taxonomy_object.selector = cur_selector;
		} else {
			current_ajax_popup_taxonomy_object.selector = '';
		}

		var cur_selector_parent_select = jQuery(this).data('selectorparent');
		if(cur_selector_parent_select != '' && cur_selector_parent_select != null) {
			current_ajax_popup_taxonomy_object.selector_parent_select = cur_selector_parent_select;
		} else {
			current_ajax_popup_taxonomy_object.selector_parent_select = '';
		}
		open_popup_taxonomy(backend_object);
		e.preventDefault();
	});
}

function default_validate(e) {
	if (jQuery('#tag-name').val() == '') {
		jQuery('#tag-name').addClass('form-invalid');
		jQuery('#tag-name').focus();
		e.preventDefault();
		return false;
	}
}
function open_popup_taxonomy(object_backend) {

	create_popop_taxonomy(object_backend);
	show_popup_taxonomy();
	var ajax_url = backend_object.ajax_url+'?action=fktr_popup_taxonomy&taxonomy='+current_ajax_popup_taxonomy_object.taxonomy;
	jQuery.ajax({
		url: ajax_url,
		type: 'get',
		contentType: false,
		processData: false,
		                    
		success: function(response) {
		    jQuery('#popup_content').html(response);
		    events_popup_taxonomy();
		    jQuery(document).trigger('popup-tax-loaded');
		},  
		error: function (response) {
		    console.log('error');
		}

	});
}

function show_popup_taxonomy() {
	jQuery('#fktr_background_popup_taxonomy').show();
	jQuery('#fktr_background_popup_taxonomy_background').fadeIn();
}
function hide_popup_taxonomy() {
	jQuery('#fktr_background_popup_taxonomy').remove();
	jQuery('#fktr_background_popup_taxonomy_background').remove();
	//jQuery('#fktr_background_popup_taxonomy').html('<div id="popup_content">'+backend_object.loading_text+' <img src="'+backend_object.loading_image+'"/></div>');
	if ( fktr_parents_popup.length > 0 ) {
		current_ajax_popup_taxonomy_object = fktr_parents_popup.pop();
		open_popup_taxonomy(backend_object);
	}
}

function create_popop_taxonomy(object_backend) {
	if(jQuery('#fktr_background_popup_taxonomy').length) {
	    // exist the popup
	    jQuery('#fktr_background_popup_taxonomy #popup_content').html(''+backend_object.loading_text+' <img src="'+backend_object.loading_image+'"/>');
	} else {
		// don't exist the popup
		jQuery('body').append( '<div id="fktr_background_popup_taxonomy"><div id="popup_content">'+backend_object.loading_text+' <img src="'+backend_object.loading_image+'"/></div></div>');
		jQuery('body').append( '<div id="fktr_background_popup_taxonomy_background"></div>');
		events_popup_taxonomy();
	}
}
function events_popup_taxonomy() {

	jQuery(document).on('popup-tax-validate', function(e){
		default_validate(e);
	});
	if (current_ajax_popup_taxonomy_object.selector_parent_select != '') {
		jQuery('#fktr_form_popup_taxonomy #parent').val(jQuery(current_ajax_popup_taxonomy_object.selector_parent_select).val());
	}
	jQuery('#fktr_form_popup_taxonomy #tag-name').focus();
	jQuery('#fktr_background_popup_taxonomy_background').click(function(e) {
		hide_popup_taxonomy();
	});
	jQuery('#fktr_form_popup_taxonomy').submit(function(e) {

		var event_validate = jQuery.Event('popup-tax-validate');
		jQuery(document).trigger(event_validate);
		if (event_validate.isDefaultPrevented() ) {
			e.preventDefault();
			return false;
		}
		
		var url = jQuery(this).attr('action')+'?fktr_is_ajax=true';
		jQuery('#fktr_popup_taxomy_loading').html(''+backend_object.loading_text+' <img src="'+backend_object.loading_image+'"/>');
		jQuery.post(url, jQuery.param(jQuery(this).serializeArray()), function(data) {
			jQuery('#fktr_form_popup_taxonomy').find('input, textarea, button, select').prop('disabled', false);
			current_json_save = jQuery.parseJSON(data);
			if (current_json_save.code == 1) {
				jQuery(document).trigger('popup-new-term-sucess', [current_json_save.term.term_id]);
				
				if (jQuery(current_ajax_popup_taxonomy_object.selector).length) {
					jQuery(current_ajax_popup_taxonomy_object.selector).append(jQuery('<option>', {
					    value: current_json_save.term.term_id,
					    text: current_json_save.name
					}));
					jQuery(current_ajax_popup_taxonomy_object.selector).val(current_json_save.term.term_id);
					jQuery(current_ajax_popup_taxonomy_object.selector).trigger('change');
				}
				hide_popup_taxonomy();
				
			} else {
				jQuery(document).trigger('popup-new-term-fail');
				jQuery('#fktr_popup_taxomy_loading').html('');
				if (current_json_save.code == 3) {
					jQuery('#fktr_form_popup_taxonomy #tag-name').addClass('form-invalid');
					jQuery('#fktr_form_popup_taxonomy #tag-name').focus();
				} else {
					if(current_json_save.message != '' && current_json_save.message != null) {
						jQuery('#fktr_popup_taxomy_loading').html(current_json_save.message);
					}
				}
			}
    	});
    	jQuery('#fktr_form_popup_taxonomy #tag-name').removeClass('form-invalid');
    	jQuery('#fktr_form_popup_taxonomy').find('input, textarea, button, select').prop('disabled', true);
		e.preventDefault();
		return false;
	});

}
