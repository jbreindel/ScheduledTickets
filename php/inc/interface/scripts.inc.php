<!-- J.Breindel option hider -->
<script>
	$( document ).ready(function() {
		
		// get the radio button that is checked
		var option = $("input[name='isrecurring']:checked", "#save").val();
		
		// IF this is non recurring
		if(option == 0){
			$('#intervalRow').hide();
			$('#timeIntervalRow').hide();
			$('#recurringDueDates').hide();
		}else{
			$('#intervalRow').show();
			$('#timeIntervalRow').show();
			$('#recurringDueDates').show();
		}
		
	});
</script>
<!-- J.Breindel recurring interval script -->
<script>
	// when the is recurring button is clicked
	$("input[name='isrecurring']").change(function(event){
		
		// get the radio button that was checked
		var option = $("input[name='isrecurring']:checked", "#save").val();
		
		// IF this is non recurring
		if(option == 0){
			$('#intervalRow').hide();
			$('#timeIntervalRow').hide();
			$('#recurringDueDates').hide();
		}else{
			$('#intervalRow').show();
			$('#timeIntervalRow').show();
			$('#recurringDueDates').show();
		}
		
	});
</script>
<!-- J.Breindel interval population -->
<script>
	// when the is recurring button is clicked
	$("#intervalSelect").change(function(event){
		
		// get the radio button that was checked
		var interval = $('#intervalSelect option:selected').val();

		// remove all options except the first
		$('#timeIntervalSelect option:gt(0)').remove();

		// SWITCH on options
		switch(interval){
			
			case "hour":
				// FOR all hours
				for (var i = 1; i < 24; i++){
					$("#timeIntervalSelect").append($("<option></option>")
     					.attr("value", i).text(i + " hours"));
				}
			break;
			
			case "day":
				// FOR all days
				for (var i = 1; i < 7; i++){
					$("#timeIntervalSelect").append($("<option></option>")
     					.attr("value", i).text(i + " days"));
				}
			break;
			
			case "week":
				// FOR all weeks
				for (var i = 1; i < 5; i++){
					$("#timeIntervalSelect").append($("<option></option>")
     					.attr("value", i).text(i + " weeks"));
				}
			break;
			
			case "month":
				// FOR all months
				for (var i = 1; i < 12; i++){
					$("#timeIntervalSelect").append($("<option></option>")
     					.attr("value", i).text(i + " months"));
				}
			break;
			
			case "year":
				// FOR all years
				for (var i = 1; i < 6; i++){
					$("#timeIntervalSelect").append($("<option></option>")
     					.attr("value", i).text(i + " years"));
				}
			break;
			
		}
		
	});
</script>
