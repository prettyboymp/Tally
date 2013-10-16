( ($) -> $ ->

	enabled = 'tally-enabled'
	registrants = 'tally-registrants'

	$table = $ '.tally-fields'
	$enabled = $ '#tally-enabled'
	$registrants = $ '#tally-capture_registrants'

	$type_container = $ '.tally-registration-types'
	$types = $ '.tally-registration-types .tally-fields'
	$type_template = $ $('#tally-template').html()
	$type_controls = $ '.tally-registration-types-controls'
	$type_add = $ '.tally-registration-type-add'
	$type_remove = $ '.tally-registration-type-remove'

	$enabled.change ->
		if $enabled.is(':checked')
			$table.addClass enabled
			$type_container.addClass enabled
		else
			$table.removeClass enabled
			$type_container.removeClass enabled

	$registrants.change ->
		if $registrants.is(':checked')
			$table.addClass registrants
		else
			$table.removeClass registrants

	$enabled.trigger('change')
	$registrants.trigger('change')

	$type_add.click ->
		$type_template.clone().addClass('tally-enabled').insertBefore $type_controls

	$type_remove.click ->
		$types = $('.tally-registration-types .tally-fields')
		if $types.length > 1 then $types.last().remove()

)(jQuery)
