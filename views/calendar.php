	<script>
	$(function() {
		$.datepicker.setDefaults($.datepicker.regional['ro']);
		
		var dates = ['2013/06/02', '2013/05/23'];
		var tips  = ['Examen Arhitectura Calculatoarelor','Examen'];
		
		$( "#datepicker" ).datepicker({
			dateFormat: 'dd-mm-yy',
			numberOfMonths: 2,
			beforeShowDay: highlightDays,
		});
		
		function highlightDays(date) {
			for (var i = 0; i < dates.length; i++) {
				console.log(new Date(dates[i]).toString());
				if (new Date(dates[i]).toString() == date.toString()) {              
					return [true, 'highlight', tips[i]];
				}
			}
			return [true, ''];
		} 
	});
	</script>

	<div class="row">
	
		<div class="span8">
		
			<h2>Calendar evenimente</h2>
			
			<ul>
				<li>23-05-2013 - Examen Grafica pe calculator</li>
				<li>02-06-2013 - Examen Arhitectura Calculatoarelor</li>
			</ul>
		
			<div id="datepicker"></div>
		
		</div>
	
	</div>