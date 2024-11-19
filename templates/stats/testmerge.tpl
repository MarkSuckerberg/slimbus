{% extends "base/index.html"%}
{% block pagetitle %}Round Index
{% endblock %}
{% block content %}
	<h3>Showing rounds where
		<a href="https://github.com/{{statbus.github}}/pull/{{pr}}" target="_blank" rel="noopener noreferrer">
			<i class="fas fa-external-link-alt"></i>
			{{data.prdata.title}}</a>
		was testmerged</h3>
	<hr>
	<div class="row">
		<div class="col">
			{% set vars = {
    'nbPages': data.pages,
    'currentPage': data.page,
    'url': path_for('stat.testmerge',{'pr': pr})
    } 
  %}
			<dl class="row">
				<dt class="col-sm-3">PR Number</dt>
				<dd class="col-sm-9">
					{{pr}}
				</dd>

				<dt class="col-sm-3">Latest Commit Testmerged</dt>
				<dd class="col-sm-9">
					<a href="https://github.com/{{statbus.github}}/pull/{{pr}}/commits/{{data.prdata.commit}}" target="_blank" rel="noopener noreferrer">
						<i class="fas fa-external-link-alt"></i>
						<code>{{data.prdata.commit}}</code>
					</a>
				</dd>
			</dd>
			<dt class="col-sm-3">First Date/Round</dt>
			<dd class="col-sm-9">
				<code>{{data.first_date}}
					/
					{{data.first_round}}</code>
			</dd>

			<dt class="col-sm-3">Most Recent Date/Round</dt>
			<dd class="col-sm-9">
				<code>{{data.last_date}}
					/
					{{data.last_round}}</code>
			</dd>

			<dt class="col-sm-3">Rounds Seen</dt>
			<dd class="col-sm-9">{{data.rounds|length}}</dd>
		</dl>
		{% include 'components/pagination.html' with vars %}
	</dd>
</div></div><table class="table table-sm table-bordered">
<thead>
	<tr>
		<th>ID</th>
		<th>Mode</th>
		<th>Result</th>
		<th>Start</th>
		<th>Duration</th>
		<th>End</th>
		<th>Server</th>
	</tr>
</thead>
<tbody>
	{% for round in rounds %}
		{% include('rounds/html/listingrow.tpl') %}
	{% endfor %}
</tbody></table>{% include 'components/pagination.html' with vars %}{% endblock %}
