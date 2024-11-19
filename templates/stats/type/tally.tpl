<table class="table table-sm table-bordered sort">
	<thead>
		<th>{{stat.label.key}}</th>
		<th>{{stat.label.value}}</th>
	</thead>
	<tbody>
		{% for key, value in stat.data %}
			<tr>
				<th>
					{{ key }}
				</th>
				<td data-text={{ value }}>
					{{ value|format_number({grouping_used:true}, locale='en') }}{{ stat.label.unit }}
				</td>
			</tr>
		{% endfor %}
	</tbody>
	<tfoot>
		<tr style="border-top: 2px solid grey;">
			<th>
				{{ stat.label.total ?: "Total" }}
			</th>
			<td>
				{{ stat.total }}{{ stat.label.unit }}
			</td>
		</tr>
	</tfoot>
</table>
