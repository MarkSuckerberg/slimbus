{% if stat is iterable %}
	{% set stat = stat[0] %}
{% endif %}
<ul class="list-group">
	{% for data in stat.output %}
		<li class="list-group-item">
			<a href="https://github.com/{{statbus.github}}/pull/{{data.number}}" target="_blank" rel="noopener noreferrer">
				<i class="fas fa-external-link-alt"></i>
				{{data.title}}
				by
				{{data.author}}</a>

			<a href="{{path_for('stat.testmerge', {'pr': data.number})}}" class="float-right">All rounds</a>
		</li>
	{% endfor %}
</ul>
