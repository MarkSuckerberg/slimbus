{% if stat.label.splain %}
	<div class="alert alert-secondary" role="alert">
		{{stat.label.splain}}
	</div>
{% endif %}
{% set grandTotal = 0 %}
{% set totalOrdered = 0 %}

<table class="table table-sm table-bordered sort">
	<thead>
		<th>Crate</th>
		<th>Number Ordered</th>
		<th>Total Cost</th>
	</thead>
	<tbody>
		{% for k,v in stat.output %}
			<tr>
				<td>{{k}}</td>
				<td>{{v.amount}}{% set totalOrdered = totalOrdered + v.amount %}</td>
				<td>{{v.cost}}{% set grandTotal = grandTotal + v.cost %}</td>
			</tr>
		{% endfor %}
	</tbody>
	<tfoot>
		<tr style="border-top: 2px solid grey;">
			<th>
				Grand Total Crates Ordered | Grand Total Spent
			</th>
			<td>{{ totalOrdered }}</td>
			<td>
				{{ grandTotal }}
			</td>
		</tr>
	</tfoot>
</table>
