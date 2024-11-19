<p class='display-4 text-center'>{{stat.label.splain}}</p>
{% if stat.output is not iterable %}
	<span class="display-4">
		<code>{{stat.key_name}}</code>:</span>
	<span class="display-1">
		<strong>{{stat.output}}</strong>
	</span>
{% else %}
	<table class="table table-sm table-bordered sort">
		<thead>
			<th>{{stat.key_name}}</th>
		</thead>
		<tbody>
			{% for data in stat.output %}
				<tr>
					<td>{{data}}</td>
				</tr>
			{% endfor %}
		</tbody>
	</table>
{% endif %}
