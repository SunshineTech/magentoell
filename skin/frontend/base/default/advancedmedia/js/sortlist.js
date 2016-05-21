function OrderDIV(position)
{
	var classPrefix = 'advancedmedia';
	var listElementSelector = '.advancedmedia-list';

	// -- Decrement the position to make it 0 based
	position--;

	// -- Parses the "position" section from the given classes, and
	//    then the position at the specific index requested.
	var parsePosition = function(classes, pos) {
		// -- Split the "classes" into an array.
		var classList = classes.split(' ');

		// -- Determine which of the "classes" starts with the prefix we want.
		for( var i in classList )
		{
			if( classList[i].substr(0, classPrefix.length) == classPrefix )
			{
				// -- Strip out the positions section, and split it.
				var positions = classList[i].split('-')[1].split('.');

				// -- return the one position we want
				return positions[pos];
			}
		}

		// -- In the event that we don't find the class we're looking for ...
		return -1;
	}

	// -- Compares div A to div B, and returns an indicator of the order
	var funcSort = function(a, b) {
		// -- Use "parsePosition" to determine the sortable criteria from the classes.
	   var compA = parsePosition(jQuery(a).attr('class'), position);
	   var compB = parsePosition(jQuery(b).attr('class'), position);
	   return (compA < compB) ? -1 : (compA > compB) ? 1 : 0;
	};

	// -- Select the list element.
	var list = jQuery(listElementSelector);

	// -- Select the list items, and return them as an array.
	var listitems = list.children('li').get();

	// -- Sort the array using the "funcSort".
	listitems.sort(funcSort);

	// -- Go through each of the array entries, and "append" them to the list container
	//   (this moves them to the 'back' of the list)
	jQuery.each(listitems, function(idx, itm) { list.append(itm); });
}
