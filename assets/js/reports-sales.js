jQuery(document).ready(function() {
    var options = {
        responsive: true,
        title: {
            display: false,
            text: ''
        },
        scales: {
            xAxes: [{
                stacked: false,
                ticks: {
                    autoSkip: true,
                    maxTicksLimit: 5 
                }
            }],
            yAxes: [{
                stacked: true,
                ticks: {
                    fontSize: window.innerWidth < 600 ? 8 : 12 
                }
            }]
        },
        layout: {
            padding: {
                left: 10,
                right: 10,
                top: 0,
                bottom: 0
            }
        }
    };
    
    var ctx = document.getElementById("canvas").getContext("2d");
    var myLineChart = Chart.Line(ctx, {
        data: chartjs_object.data,
        options: options
    });

    
    jQuery(window).resize(function() {
        var canvas = document.getElementById("canvas");
        canvas.height = window.innerWidth < 600 ? 300 : 400;
    });
});
