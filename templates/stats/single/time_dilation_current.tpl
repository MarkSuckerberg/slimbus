{% block content %}
	<div id="chart"></div>
{% endblock %}

{% block js %}
	<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
	<script>
		var json = {{ stat.chartdata|raw }}
var options = {
chart: {
type: 'line',
height: 512,
animations: {
enabled: false
}
},
series: [
{
name: 'Current',
data: json['current']
}, {
name: 'Avg',
data: json['avg']
}
],
xaxis: {
type: "datetime",
categories: json['datetimes']
},
tooltip: {
x: {
format: 'dd MMM yyyy HH:00'
}
}
}
var chart = new ApexCharts(document.querySelector("#chart"), options);
chart.render();
	</script>
{% endblock %}
