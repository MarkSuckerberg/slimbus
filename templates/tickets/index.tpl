{% extends ('base/index.html') %}
  {% block content %}
  {% include 'tgdb/html/nav.html' %}
  <div class="row">
    <div class="col">
    </div>
  </div>
  {% include 'tickets/html/listing.html' %}
  <br>
  {% set vars = {
    'nbPages': ticket.pages,
    'currentPage': ticket.page,
    'url': path_for('ticket.index')
    } 
  %}
  <div class="d-flex justify-content-center">{% include 'components/pagination.html' with vars %}</div>
  {% endblock %}
