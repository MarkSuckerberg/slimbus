{% extends "base/index.html"%}
{% block content %}
	<div class="modal fade show" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalCenterTitle">Hey! Listen!</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					This is
					<strong>JUST FOR FUN</strong>. Votes cast here will have no effect on policy and will not be used to change anything. If it becomes un fun it will be removed.
				</div>
				<div class="modal-footer">
					<a href="#" class="btn btn-primary" data-dismiss="modal">I Understand This Is Just For Fun</a>
				</div>
			</div>
		</div>
	</div>
	<h1>Name Rater 5000</h1>
	<hr>
	<div class="btn-group" role="group">
		<a class="btn btn-primary text-white" href="{{path_for('nameVoter')}}">Vote on Names</a>
		<a class="btn btn-primary text-white" href="{{path_for('nameVoter.results',{'rank':'best'})}}">Best Names</a>
		<a class="btn btn-primary text-white" href="{{path_for('nameVoter.results',{'rank':'worst'})}}">Worst Names</a>
	</div>
	<hr>
	<p class="lead">Just for funsies*, select whether or not the name below is good or bad, in your opinion. Remember that we're simply judging the name, not the player behind the name.</p>
	<div class="jumbotron jumbotron-fluid">
		<div class="container text-center">
			<h3 class="display-4">Is this a good name?</h3>
			<h1 class="display-1" id="name">{{name.name}}</h1>
			<h2 class="display-4" id="job">({{name.job}})</h2>
			<hr>
			<form method="POST" id="vote" action="{{path_for('nameVoter.cast')}}" >
				<div class="row">
						<button class="btn btn-danger btn-block" name="vote" value="nay">
							<i class="fas fa-ban"></i>
							Bad Name</button>
						<button class="btn btn-success btn-block" name="vote" value="yea">
							<i class="fas fa-check"></i>
							Good Name</button>
				</div>
				<input type="hidden" name="name" value="{{name.name}}" id="nameField">
        <input type="hidden" class="sb_csrf_name" name="sb_csrf_name" value="{{csrf.sb_csrf_name}}">
        <input type="hidden" class="sb_csrf_value" name="sb_csrf_value" value="{{csrf.sb_csrf_value}}">
			</form>
		</div>
	</div>
	<p>* No action will be taken against anyone based on any votes cast here.</p>
	<p>As species data is not tracked, there is no way to differentiate between human/lizard/moth/fly person names.</p>
	<p>Duplicate votes are discarded, so dont worry if you see the same name twice.</p>
{% endblock %}
