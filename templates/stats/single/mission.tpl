{% if stat.label.splain %}
	<div class="alert alert-secondary" role="alert">
		{{stat.label.splain}}
	</div>
{% endif %}

{% set totalAccepted = 0 %}
{% set totalAbandoned = 0 %}
{% set totalSucceeded = 0 %}
{% set totalPayout = 0 %}

<table class="table table-sm table-bordered sort">
	<thead>
		<th>Mission Name</th>
		<th>Accepted</th>
		<th>Abandoned</th>
		<th>Abd. Rate</th>
		<th>Succeeded</th>
		<th>Success Rate</th>
		<th>Payout</th>
	</thead>
	<tbody>
		{% for k,v in stat.output %}
			<tr>
				<td>{{k}}</td>
				<td>{{v.accepted}}{% set totalAccepted = totalAccepted + v.accepted %}</td>
				<td>{% set abandoned = v.abandoned ?: 0 %}{{abandoned}}{% set totalAbandoned = totalAbandoned + abandoned %}</td>
				<td>{{(abandoned > 0 ? (abandoned / v.accepted) : 0)|format_number(style: 'percent')}}</td>
				<td>{% set succeeded = v.succeeded ?: 0 %}{{succeeded ?: 0}}{% set totalSucceeded = totalSucceeded + succeeded %}</td>
				<td>{{ (succeeded > 0 ? (succeeded / v.accepted) : 0)|format_number(style: 'percent') }}</td>
				<td>{{ v.payout ?: 0 }}cr{% set totalPayout = totalPayout + v.payout %}</td>
			</tr>
		{% endfor %}
	</tbody>
	<tfoot>
		<tr style="border-top: 2px solid grey;">
			<th>Grand Total</th>
			<td>{{totalAccepted}}</td>
			<td>{{totalAbandoned}}</td>
			<td>{{(totalAbandoned / totalAccepted)|format_number(style: 'percent')}}</td>
			<td>{{totalSucceeded}}</td>
			<td>{{(totalSucceeded / totalAccepted)|format_number(style: 'percent')}}</td>
			<td>{{totalPayout}}cr</td>
		</tr>
	</tfoot>
</table>
