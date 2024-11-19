<table class="table table-bordered table-condensed table-hover sort">
  <thead>
    <tr>
      <th>Timestamp</th>
      <th>Category</th>
      <th>Info</th>
      <th>Content</th>
    </tr>
  </thead>
  <tbody>
  {% for line in file %}
    <tr>
      <td class="align-middle">{{line.timestamp}}</td>
      <td class="align-middle" style="background: #{{line.color}}"><code class="bg">{{line.category}}</code></td>
      {% if line.info %}
        <td class="align-middle" style="background: #{{line.infocolor}}"><code class="bg">{{line.info}}</code></td>
      {% else %}
        <td class="align-middle"></td>
      {% endif %}
      <td class="align-middle">{{line.text|raw}}</td>
    </tr>
  {% endfor %}
  </tbody>
</table>