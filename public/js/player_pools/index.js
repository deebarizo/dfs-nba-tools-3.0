$(document).ready(function() {

	/****************************************************************************************
	FILTER
	****************************************************************************************/

	function Filter(type, columnIndex, value, modifier) {

		this.type = type;
		this.columnIndex = columnIndex;
		this.value = value;
		this.modifier = modifier;
	}

	Filter.prototype.execute = function() {

		// https://datatables.net/reference/api/column().search()

		if (this.type === 'team') {

			if (this.value !== 'All') {

				playerPoolTable.column(this.columnIndex).search(this.value, true, false, false); 
			
			} else {

				playerPoolTable.column(this.columnIndex).search('.*', true, false, false); 
			}
		} 

		if (this.type === 'position') {

			if (this.value === 'All') {

				playerPoolTable.column(this.columnIndex).search('.*', true, false, false); 
			
			} else {

				playerPoolTable.column(this.columnIndex).search(this.value, true, false, false); 
			}
		}

		playerPoolTable.draw();
	};


	/****************************************************************************************
	POSITION FILTER
	****************************************************************************************/

	$('select.position-filter').on('change', function() {

		var value = $('select.position-filter').val();
		
		var filter = new Filter('position', 6, value, null);

		filter.execute();
	});


	/****************************************************************************************
	TEAM FILTER
	****************************************************************************************/

	$('select.team-filter').on('change', function() {

		var value = $('select.team-filter').val();
		
		var filter = new Filter('team', 1, value, null);

		filter.execute();
	});

});