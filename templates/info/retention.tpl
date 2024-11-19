{% extends "base/index.html"%}
{% block content %}

	<div id="population"></div>
	<p class="lead">
		This chart shows the retention of players over all time. New players had never played before the listed month, retained players had played the month prior, and returnin players had played before, but not the previous month.
	</p>
{% endblock %}

{% block js %}
	<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
	<script>
		var json = {{ data|raw }}
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
name: 'New',
data: unpack(json, 'new')
}, {
name: 'Retained',
data: unpack(json, 'retained')
}, {
name: 'Returning',
data: unpack(json, 'returned')
}
],
xaxis: {
type: "datetime",
categories: unpack(json, 'datestamp')
},
tooltip: {
x: {
format: 'dd MMM yyyy HH:00'
}
}
}
var chart = new ApexCharts(document.querySelector("#population"), options);
chart.render();
	</script>
{% endblock %}
