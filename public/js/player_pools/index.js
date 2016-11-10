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
	SALARY FILTER
	****************************************************************************************/

	// https://www.datatables.net/examples/plug-ins/range_filtering.html

	$.fn.dataTable.ext.search.push(

	    function(settings, data, dataIndex) {
	        
	        var targetSalary = parseInt($('.salary-input').val(), 10);

	        var modifier = $('input:radio[name=salary-toggle]:checked').val();

	        var salary = parseFloat(data[5]) || 0; // use data for the salary column

	        if (modifier === 'greater-than') {

	        	if (salary >= targetSalary) {

	        		return true;
	        	
	        	} else {

	        		return false;
	        	}
	        }

	        if (modifier === 'less-than') {

	        	if (salary <= targetSalary) {

	        		return true;
	        	
	        	} else {

	        		return false;
	        	}
	        }		 
	    }
	);

	$('.salary-input').keyup(function() {

		playerPoolTable.draw();
	});

	$("input[name=salary-toggle]:radio").change(function() {

		playerPoolTable.draw();
	});


	$('.salary-reset').on('click', function(event) { 
		$('.salary-input').val(100000);
		$('#less-than').prop('checked', true);

		playerPoolTable.draw();
	});


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