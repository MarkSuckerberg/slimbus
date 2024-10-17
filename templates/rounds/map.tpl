{% extends "base/dashboard.html"%}
{% block pagetitle %}Map Data - Round #{{round.id}}{% endblock %}
{% block dashtitle %}Map - Round #{{round.id}}{% endblock %}
{% block content %}

<div id="map"></div>

{% endblock %}

{% block sidebar %}

<h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted"><span>Database</span></h6>
<ul class="nav flex-column">
  <li class="nav-item">
    <a class="nav-link invisible" href="#" id="deaths">
      <i class="fas fa-user-times"></i> Deaths (<span id="deathCount"></span>)
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link invisible" href="#" id="bombs">
      <i class="fas fa-bomb"></i> Explosions
    </a>
  </li>
</ul>

<h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted"><span>Logfiles</span></h6>
<ul class="nav flex-column" id="logfiles">

</ul>

{% endblock %}

{% block js %}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>

<script>
fetch('/rounds/{{round.id}}?format=json')
  .then(response => response.json())
  .then(function(data) {
    var map = L.map("map", {
      attributionControl: false,
      minZoom: 1,
      maxBounds: [
        [64, -64],
        [-512, 512]
      ],
      maxBoundsViscosity: 1.0,
      maxZoom: 6,
      crs: L.CRS.Simple,
      preferCanvas: true,
    }).setView([-64, 64], 3);
    L.tileLayer("https://shiptest.ga/renders/tiles/{{round.map_url}}/tiles/{z}/tile_{x}-{y}.png", {
      minZoom: 1,
      maxZoom: 6,
      minNativeZoom: 2,
      maxNativeZoom: 3,
      continuousWorld: true,
      tms: false
    }).addTo(map);
    /*if(data.deaths){
      $('#deaths').removeClass('invisible')
      $('#deathCount').text(data.deaths)
    }
    if(data.stats.explosion){
      $('#bombs').removeClass('invisible')
    }
    fetch('/rounds/{{round.id}}/logs?format=json')
    .then(function(response) {
      return response.json();
    })
    .then(function(data){
      const logfiles = Object.keys(data)
      let logLayers = [];
      logfiles.forEach(function(log){
        log = log.split('.zip/')[1]
        var html = "<li class='nav-item'><a href='"+log+"' class='nav-link logfileLink'>"+log+"</a></li>";
        $('#logfiles').append(html)
        // logLayers.push(log)
        logLayers[log] = L.layerGroup()
        logLayers[log]['loaded'] = false
      })
      // return logLayers;
      $('body').on('click','.logfileLink', function(e){
        e.preventDefault();
        $(this).toggleClass('active')
        file = $(this).attr('href');
        // var loaded = false;
        console.log(logLayers[file])
        if(logLayers[file]['loaded']){
          if(map.hasLayer(logLayers[file])){
            map.removeLayer(logLayers[file])
            return
          } else {
            map.addLayer(logLayers[file])
            return
          }
        }
        fetch('/rounds/{{round.id}}/logs/'+file+'/json')
          .then(function(response) {
            return response.json();
          })
          .then(function(data){
            // lines = Object.values(data)
            data.forEach(function(line){
              if(line.z != 2) return;
              marker = L.polygon([
                tg2leaf(line.x,   line.y),
                tg2leaf(line.x-1, line.y),
                tg2leaf(line.x-1, line.y-1),
                tg2leaf(line.x,   line.y-1)
              ], {color: '#'+line.color})
              .bindPopup(line.text).addTo(logLayers[file]);
            })
            logLayers[file]['loaded'] = true
            logLayers[file].addTo(map)
          })
      });
    })*/
    return map;
  })
  /*.then(function(map){
    var corpses = L.layerGroup();
    var loaded = false
    $('#deaths').click(function(e){
      e.preventDefault();
      $(this).toggleClass('active')
      if(loaded){
        if(map.hasLayer(corpses)){
          map.removeLayer(corpses)
          return
        } else {
          map.addLayer(corpses)
          return
        }
      }
      loaded = true
      fetch('/deaths/round/{{round.id}}?format=json')
        .then(function(response) {
          return response.json();
        })
        .then(function(data){
          data.forEach(function(d){
            if(d.z != 2) return;
            death = L.polygon([
              tg2leaf(d.x,   d.y),
              tg2leaf(d.x-1, d.y),
              tg2leaf(d.x-1, d.y-1),
              tg2leaf(d.x,   d.y-1)
            ], {color: 'red'})
            .bindPopup("<table class='table table-sm table-bordered'><tr><th>ID</th><td><a target='_blank' href='"+window.location.origin+"/deaths/"+d.id+"'>"+d.id+"</a></td></tr><tr><th>Name</th><td>"+d.name+"/"+d.byondkey+"</td></tr><tr><th>Job</th><td>"+d.job+"</td></tr><tr><th>At</th><td>"+d.pod+"</td></tr><tr><th>Timestamp</th><td>"+d.tod+"</td></tr><tr><th>Vitals</th><td><span class='brute'>"+d.vitals.brute+"</span> / <span class='brain'>"+d.vitals.brain+"</span> / <span class='fire'>"+d.vitals.fire+"</span> / <span class='oxy'>"+d.vitals.oxy+"</span> / <span class='tox'>"+d.vitals.tox+"</span> / <span class='clone'>"+d.vitals.clone+"</span> / <span class='stamina'>"+d.vitals.stamina+"</span></td></tr></table>").addTo(corpses)
          })
        })
      corpses.addTo(map);
    })
    return map
  })
  .then(function(map){
    var bombs = L.layerGroup();
    var loaded = false
    $('#bombs').click(function(e){
      e.preventDefault();
      $(this).toggleClass('active')
      if(loaded){
        if(map.hasLayer(bombs)){
          map.removeLayer(bombs)
          return
        } else {
          map.addLayer(bombs)
          return
        }
      }
      loaded = true
      fetch('/rounds/{{round.id}}/explosion?format=json')
        .then(function(response) {
          return response.json();
        })
        .then(function(data){
          var data = data.data
          // console.log(data)
          data = Object.values(data)
          data.forEach(function(e){
            if (e.z != "2") {
              return
            }
            if(e.flash > 0){
              var flashCircle = L.circle(tg2leaf(e.x-.5,e.y-.5), {
                  color: 'white',
                  radius: +e.flash+.5
              }).bindPopup("Flash Range: " + e.flash + " from explosion at " + e.area+" ("+e.time+")").addTo(bombs);
            }
            if(e.light > 0){
              var lightCircle = L.circle(tg2leaf(e.x-.5,e.y-.5), {
                  color: 'yellow',
                  radius: +e.light+.5
              }).bindPopup("Light Damage Range: " + e.light + " from explosion at " + e.area+" ("+e.time+")").addTo(bombs);
            }
            if(e.heavy > 0){
              var heavyCircle = L.circle(tg2leaf(e.x-.5,e.y-.5), {
                  color: 'orange',
                  radius: +e.heavy+.5
              }).bindPopup("Heavy Damage Range: " + e.heavy + " from explosion at " + e.area+" ("+e.time+")").addTo(bombs);
            }
            if(e.dev > 0){
              var devCircle = L.circle(tg2leaf(e.x-.5,e.y-.5), {
                  color: 'red',
                  radius: +e.dev+.5
              }).bindPopup("Devestation Range: " + e.dev + " from explosion at " + e.area+" ("+e.time+")").addTo(bombs);
            }
          })
        })
      bombs.addTo(map);
      return map;
    })
  })*/
</script>
{% endblock %}