<div class="alert alert-secondary">Procs with less than 0.1 across the board and less than 25 calls have been removed from this display for the sake of efficency</div>
<table class="table table-bordered table-condensed table-sm table-hover sort">
	<thead>
		<tr>
			<th>Name</th>
			<th>Self</th>
			<th>Self Avg</th>
			<th>Total</th>
			<th>Total Avg</th>
			<th>Real</th>
			<th>Real Avg</th>
			<th>Over</th>
			<th>Over Avg</th>
			<th>Calls</th>
		</tr>
	</thead>
	<tbody>
		{% for line in file %}
			<tr>
				<td>{{line.name}}</td>
				<td>{{line.self}}</td>
				<td>{{line.selfAvg}}</td>
				<td>{{line.total}}</td>
				<td>{{line.totalAvg}}</td>
				<td>{{line.real}}</td>
				<td>{{line.realAvg}}</td>
				<td>{{line.over}}</td>
				<td>{{line.overAvg}}</td>
				<td>{{line.calls}}</td>
			</tr>
		{% endfor %}
	</tbody>
</table>
