{% extends "base/index.html"%}
{%block content %}
<style>
.hide {
    display: none;
}

.clear {
    float: none;
    clear: both;
}

.rating {
    width: 90px;
    unicode-bidi: bidi-override;
    direction: rtl;
    text-align: center;
    position: relative;
}

.rating > label {
    float: right;
    display: inline;
    padding: 0;
    margin: 0;
    position: relative;
    width: 1.1em;
    cursor: pointer;
    color: #000;
}

.rating > label:hover,
.rating > label:hover ~ label,
.rating > input.radio-btn:checked ~ label {
    color: transparent;
}

.rating > label:hover:before,
.rating > label:hover ~ label:before,
.rating > input.radio-btn:checked ~ label:before,
.rating > input.radio-btn:checked ~ label:before {
    content: "\2605";
    position: absolute;
    left: 0;
    color: #FFD700;
}
</style>
<h1>{{server.name}} Art Gallery</h1>
<hr>
<ul class="w-100" style="display: grid; grid-template-columns: repeat(3, 1fr)">
  {% for name, collection in art|sort((a, b) => b|length <=> a|length) %}
    {% if collection|length > 0 %}
        {% set path = name %}
        {% for collection in art[name]|sort((a, b) => b.rating <=> a.rating) %}
          {% include 'gallery/html/artwork.tpl' %}
        {% endfor %}
    {% endif %}
  {% endfor %}
</ul>

{% endblock %}

{% block js %}
<script>

$('form.star_rating .radio-btn').change(function(e){
  var form = $(this).parent().parent()
  $.ajax({
    url: form.attr('action'),
    method: form.attr('method'),
    data: form.serialize()
  }).done(function(reply){
    $('.sb_csrf_name').val(reply.csrf.sb_csrf_name)
    $('.sb_csrf_value').val(reply.csrf.sb_csrf_value)
    $('#collection-'+reply.votes.artwork+' #rating').text(reply.votes.rating)
    $('#collection-'+reply.votes.artwork+' #votes').text(reply.votes.votes)
  })
})

</script>
{% endblock %}