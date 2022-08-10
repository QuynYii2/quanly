jQuery(document).ready(function($) {
	$(".check-all").click(function() {
		var checked_status = this.checked;
		$("input.check-id").each(function() {
			this.checked = checked_status;
		});
	});
	
	$('a.sort-order').click(function(){
		if ($(this).attr('field') != $('input#orderby_field').val()) $('input#orderby_order').val('ASC');
		else {
			if ($('input#orderby_order').val().toLowerCase() == 'asc') $('input#orderby_order').val('DESC');
			else $('input#orderby_order').val('ASC');
		}
		$('input#orderby_field').val($(this).attr('field'));
		$('form.form-admin').submit();
	});
});

function checkAll() {

}

function submitForm(task, cid) {
    if (typeof(cid) != 'undefined') {
        jQuery("input.check-all").attr('checked', false);
        jQuery("input.check-id").each(function() {
            if (this.id != 'cid_'+cid) this.checked = false;
            else this.checked = true;
        });
    }
    jQuery('input#task').val(task);
    jQuery('form.form-admin').submit();
}