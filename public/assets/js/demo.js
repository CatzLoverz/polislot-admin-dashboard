"use strict";
// Cicle Chart
Circles.create({
	id:           'task-complete',
	radius:       50,
	value:        80,
	maxValue:     100,
	width:        5,
	text:         function(value){return value + '%';},
	colors:       ['#36a3f7', '#fff'],
	duration:     400,
	wrpClass:     'circles-wrp',
	textClass:    'circles-text',
	styleWrapper: true,
	styleText:    true
})

//Notify
/*$.notify({
	icon: 'flaticon-alarm-1',
	title: 'Atlantis Lite',
	message: 'Free Bootstrap 4 Admin Dashboard',
},{
	type: 'info',
	placement: {
		from: "bottom",
		align: "right"
	},
	time: 1000,
});*/

// JQVmap
$('#map-example').vectorMap(
{
	map: 'world_en',
	backgroundColor: 'transparent',
	borderColor: '#fff',
	borderWidth: 2,
	color: '#e4e4e4',
	enableZoom: true,
	hoverColor: '#35cd3a',
	hoverOpacity: null,
	normalizeFunction: 'linear',
	scaleColors: ['#b6d6ff', '#005ace'],
	selectedColor: '#35cd3a',
	selectedRegions: ['ID', 'RU', 'US', 'AU', 'CN', 'BR'],
	showTooltip: true,
	onRegionClick: function(element, code, region)
	{
		return false;
	}
});

//Chart 
var ctx = document.getElementById('statisticsChart').getContext('2d');

var statisticsChart = new Chart(ctx, {
	type: 'line',
	data: {
		labels: ["Januari", "Februari", "Maret", "April", "Mai", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"],
		datasets: [ {
			label: "Pt Pencari CINTA",
			borderColor: '#f3545d',
			pointBackgroundColor: 'rgba(243, 84, 93, 0.6)',
			pointRadius: 0,
			backgroundColor: 'rgba(243, 84, 93, 0.4)',
			legendColor: '#f3545d',
			fill: true,
			borderWidth: 2,
			data: [154, 184, 175, 203, 210, 231, 240, 278, 252, 312, 320, 374]
		}, {
			label: "Anteiku",
			borderColor: '#fdaf4b',
			pointBackgroundColor: 'rgba(253, 175, 75, 0.6)',
			pointRadius: 0,
			backgroundColor: 'rgba(253, 175, 75, 0.4)',
			legendColor: '#fdaf4b',
			fill: true,
			borderWidth: 2,
			data: [256, 230, 245, 287, 240, 250, 230, 295, 331, 431, 456, 521]
		}, {
			label: "Pt Polex",
			borderColor: '#177dff',
			pointBackgroundColor: 'rgba(23, 125, 255, 0.6)',
			pointRadius: 0,
			backgroundColor: 'rgba(23, 125, 255, 0.4)',
			legendColor: '#177dff',
			fill: true,
			borderWidth: 2,
			data: [542, 480, 430, 550, 530, 453, 380, 434, 568, 610, 700, 900]
		}]
	},
	options : {
		responsive: true, 
		maintainAspectRatio: false,
		legend: {
			display: false
		},
		tooltips: {
			bodySpacing: 4,
			mode:"nearest",
			intersect: 0,
			position:"nearest",
			xPadding:10,
			yPadding:10,
			caretPadding:10
		},
		layout:{
			padding:{left:5,right:5,top:15,bottom:15}
		},
		scales: {
			yAxes: [{
				ticks: {
					fontStyle: "500",
					beginAtZero: false,
					maxTicksLimit: 5,
					padding: 10
				},
				gridLines: {
					drawTicks: false,
					display: false
				}
			}],
			xAxes: [{
				gridLines: {
					zeroLineColor: "transparent"
				},
				ticks: {
					padding: 10,
					fontStyle: "500"
				}
			}]
		}, 
		legendCallback: function(chart) { 
			var text = []; 
			text.push('<ul class="' + chart.id + '-legend html-legend">'); 
			for (var i = 0; i < chart.data.datasets.length; i++) { 
				text.push('<li><span style="background-color:' + chart.data.datasets[i].legendColor + '"></span>'); 
				if (chart.data.datasets[i].label) { 
					text.push(chart.data.datasets[i].label); 
				} 
				text.push('</li>'); 
			} 
			text.push('</ul>'); 
			return text.join(''); 
		}  
	}
});

var myLegendContainer = document.getElementById("myChartLegend");

// generate HTML legend
myLegendContainer.innerHTML = statisticsChart.generateLegend();

// bind onClick event to all LI-tags of the legend
var legendItems = myLegendContainer.getElementsByTagName('li');
for (var i = 0; i < legendItems.length; i += 1) {
	legendItems[i].addEventListener("click", legendClickCallback, false);
}


//multiple line charts
var myMultipleLineChart = new Chart(multipleLineChart, {
	type: 'line',
	data: {
		labels: ["Januari", "Februari", "Maret", "April", "Mai", "Juni", "Juli", "Augustus", "September", "Oktober", "November", "Desember"],
		datasets: [{
			label: "Pt pencari cinta",
			borderColor: "#1d7af3",
			pointBorderColor: "#FFF",
			pointBackgroundColor: "#1d7af3",
			pointBorderWidth: 2,
			pointHoverRadius: 4,
			pointHoverBorderWidth: 1,
			pointRadius: 4,
			backgroundColor: 'transparent',
			fill: true,
			borderWidth: 2,
			data: [30, 45, 45, 68, 69, 90, 100, 158, 177, 200, 245, 256]
		},{
			label: "Anteiku",
			borderColor: "#59d05d",
			pointBorderColor: "#FFF",
			pointBackgroundColor: "#59d05d",
			pointBorderWidth: 2,
			pointHoverRadius: 4,
			pointHoverBorderWidth: 1,
			pointRadius: 4,
			backgroundColor: 'transparent',
			fill: true,
			borderWidth: 2,
			data: [10, 20, 55, 75, 80, 48, 59, 55, 23, 107, 60, 87]
		}, {
			label: "Akatsuki",
			borderColor: "#f3545d",
			pointBorderColor: "#FFF",
			pointBackgroundColor: "#f3545d",
			pointBorderWidth: 2,
			pointHoverRadius: 4,
			pointHoverBorderWidth: 1,
			pointRadius: 4,
			backgroundColor: 'transparent',
			fill: true,
			borderWidth: 2,
			data: [10, 30, 58, 79, 90, 105, 117, 160, 185, 210, 185, 194]
		}]
	},
	options : {
		responsive: true, 
		maintainAspectRatio: false,
		legend: {
			position: 'top',
		},
		tooltips: {
			bodySpacing: 4,
			mode:"nearest",
			intersect: 0,
			position:"nearest",
			xPadding:10,
			yPadding:10,
			caretPadding:10
		},
		layout:{
			padding:{left:15,right:15,top:15,bottom:15}
		}
	}
});