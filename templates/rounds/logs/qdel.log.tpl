<table class="table table-bordered table-condensed table-hover sort">
	<thead>
		<tr>
			<th>Datum Path</th>
			<th>Destroy() Time</th>
			<th>Total Deletion Time</th>
			<th>Longest Deletion</th>
			<th data-toggle="tooltip" title="Sorted by ratio">Failures (Hard Dels) / Total Deletions</th>
		</tr>
	</thead>
	<tbody>
		{% for line in file %}
			<tr>
				<td>{{line.path}}</td>
				<td>{{line.destroyTime}}ms</td>
				<td>{{line.totalTime}}ms</td>
				<td>{{line.longestTime}}ms</td>
				<td data-text="{{line.sortRatio}}">{{line.failures}}
					{% if line.harddels != line.failures %}
						({{line.harddels}})
					{% endif %}
					/
					{{line.totalDel}}</td>
				<td></td>
			</tr>
		{% endfor %}
	</tbody>
</table>
