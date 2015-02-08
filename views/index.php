<?php
include('header.php');
?>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">

  // Load the Visualization API and the piechart package.
  google.load('visualization', '1.0', {'packages':['corechart']});

  // Set a callback to run when the Google Visualization API is loaded.
  google.setOnLoadCallback(drawChart);

  // Callback that creates and populates a data table,
  // instantiates the pie chart, passes in the data and
  // draws it.
  function drawChart(rows, title) {
	// Create the data table.
	        var data = new google.visualization.DataTable();
	        data.addColumn('string', 'Status');
	        data.addColumn('number', 'Total');
			$.each(rows, function(k, v) {
			    data.addRow([k, v]);
			});

	        // Set chart options
	        var options = {
							title:title,
	                       	width:500,
	                       	height:500,
						   	backgroundColor: '#FFFFFF',
						    chartArea:{left:10,top:20,width:"80%",height:"80%"},
						   	slices: [{color: '#444'}, {color: 'F54997'}, {color: '#ccc'}],
						   	titleTextStyle: {color: '#5f6c74'},
							legend: {position: 'bottom', textStyle:{color: '#5f6c74'}}
						   	//legend.textStyle: {color: '#5f6c74'}
						  };

	        // Instantiate and draw our chart, passing in some options.
	        var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
	        chart.draw(data, options);
  }
</script>

<div class="btn-group" style="margin-bottom:20px; width:100%;">
  <a class="btn btn-primary" href="#"><i class="icon-tasks icon-white"></i> <?= lang('actions'); ?></a>
  <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#"><span class="caret"></span></a>
  <ul class="dropdown-menu">
	<li><?php echo '<a href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=create_client"><i class="icon-user"></i> ' . lang('create_client') . '</a>'; ?></li>
	<li><?php echo '<a href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=create_record'.AMP.'type=quote"><i class="icon-pencil"></i> ' . lang('create_quote') . '</a>'; ?></li>
	<li><?php echo '<a href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=create_record'.AMP.'type=invoice"><i class="icon-book"></i> ' . lang('create_invoice') . '</a>'; ?></li>
	<li><?php echo '<a href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=settings"><i class="icon-cog"></i> ' . lang('settings') . '</a>'; ?></li>
  </ul>
</div>
<div class="span4">
	<table class="table table-striped table-bordered">
	<tr><th colspan="2"><?= lang('overview'); ?></th></tr>
	<tr><td><?= lang('outstanding_invoices'); echo '</td><td style="text-align:right;">'.$outstanding; ?></td></tr>
	<tr><td><?= lang('paid_invoices'); echo '</td><td style="text-align:right;">'.$paid; ?></td></tr>
	<tr><td><?= lang('prospective'); echo '</td><td style="text-align:right;">'.$prospective; ?></td></tr>
	</table>
	<table class="table table-striped table-bordered">
	<tr><th colspan="2"><?= lang('overdue_invoices'); ?></th></tr>
	<?php
		foreach($overdue as $row){
			echo '<tr class="error"><td><a href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=record'.AMP.'record_id='.$row['invoice_id'].AMP.'type=invoice">'.$row['name'].'</a></td><td>'.$row['total'].'</td></tr>';
		}
	?>
	</table>
</div>
<div class="span8" id="chart_div"></div>