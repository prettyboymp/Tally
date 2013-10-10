( ($) -> $ ->

	enabled = 'tally-enabled'
	registrants = 'tally-registrants'

	$table = $ '.tally-fields'
	$enabled = $ '#tally-enabled'
	$registrants = $ '#tally-capture_registrants'

	$enabled.change ->
		if $enabled.is(':checked')
			$table.addClass enabled
		else
			$table.removeClass enabled

	$registrants.change ->
		if $registrants.is(':checked')
			$table.addClass registrants
		else
			$table.removeClass registrants

	$enabled.trigger('change')
	$registrants.trigger('change')

)(jQuery)
