jQuery(document).ready(function() {
	var options = {
                    responsive: true,
                    title: {
                        display: false,
                        text: ''
                    },
                    scales: {
			            xAxes: [{
			                stacked: false
			            }],
			            yAxes: [{
			                stacked: true
			            }]
			        },
					layout: {
						padding: 20
					}
                };
    var ctx = document.getElementById("canvas").getContext("2d");
	var myLineChart = Chart.Line(ctx, {
	    data: chartjs_object.data,
	    options: options
	});
});