<!DOCTYPE html>
<!-- http://gmaps-samples-v3.googlecode.com/svn/trunk/drawing/drawing-tools.html -->
<!-- https://developers.google.com/maps/documentation/javascript/examples/places-searchbox -->
<html>

<head>
  <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
  <meta charset="UTF-8">
  <title>Orçador de Mapas powered by SmartMatrix</title>
  <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false&v=3.21.5a&libraries=drawing&signed_in=true&libraries=places,drawing"></script>
  <script type="text/javascript" src="https://code.jquery.com/jquery-3.4.1.min.js"> </script>
  <style type="text/css">
    #map {
      height: 100%;
    }

    html,
    body {
      padding: 0;
      margin: 0;
      height: 100%;
    }

    #panel {
      width: 200px;
      font-family: Arial, sans-serif;
      font-size: 10px;
      float: right;
      margin: 10px;
    }

    #color-palette {
      clear: both;
    }

    .color-button {
      width: 14px;
      height: 14px;
      font-size: 0;
      margin: 2px;
      float: left;
      cursor: pointer;
    }

    #delete-button {
      margin-top: 100px;
      left: 5px;

    }

    #delete-forma {
      background-color: #fff;
      border: 1px solid #fff;
      color: #1a73e8;
      border-radius: 4px;
      font-family: Roboto, sans-serif;
      font-size: 14px;
      height: 36px;
      cursor: pointer;
      padding: 0 24px;
      margin-left: 12px;
      border: 1px solid #dadce0;
    }

    #divLocation {
      position: fixed;
      float: bottom;
      background-color: white;
      display: none;
      top: 40px;
      left: 5px;
      z-index: 100;
      border-radius: 3px;
      background-image: url("img/logo.png");
      width: 235px;
      background-repeat: no-repeat;
      margin-left: auto;
      margin-right: auto;
      border-radius: 4px;
      border: 1px solid #dadce0;
    }
  </style>

  <script type="text/javascript">
    var drawingManager;
    var selectedShape;
    var colors = ['#1E90FF', '#FF1493', '#32CD32', '#FF8C00', '#4B0082'];
    var selectedColor;
    var colorButtons = {};

    function clearSelection() {
      if (selectedShape) {
        if (typeof selectedShape.setEditable == 'function') {
          selectedShape.setEditable(false);
        }
        selectedShape = null;
      }
      curseldiv.innerHTML = "<b>cursel</b>:";
    }

    function updateCurSelText(shape) {
      posstr = "" + selectedShape.position;
      if (typeof selectedShape.position == 'object') {
        posstr = selectedShape.position.toUrlValue();
      }
      pathstr = "" + selectedShape.getPath;
      if (typeof selectedShape.getPath == 'function') {
        pathstr = "[ ";
        for (var i = 0; i < selectedShape.getPath().getLength(); i++) {
          pathstr += selectedShape.getPath().getAt(i).toUrlValue() + " , ";
        }
        pathstr += "]";
      }
      bndstr = "" + selectedShape.getBounds;
      cntstr = "" + selectedShape.getBounds;
      if (typeof selectedShape.getBounds == 'function') {
        var tmpbounds = selectedShape.getBounds();
        cntstr = "" + tmpbounds.getCenter().toUrlValue();
        bndstr = "[NE: " + tmpbounds.getNorthEast().toUrlValue() + " SW: " + tmpbounds.getSouthWest().toUrlValue() + "]";
      }
      cntrstr = "" + selectedShape.getCenter;
      if (typeof selectedShape.getCenter == 'function') {
        cntrstr = "" + selectedShape.getCenter().toUrlValue();
      }
      radstr = "" + selectedShape.getRadius;
      if (typeof selectedShape.getRadius == 'function') {
        radstr = "" + selectedShape.getRadius();
      }

      /// Here i get LAT/LNG of Map and create DIV
      /// Make panel of informations
      var valorPeloTipoDesenho = "";
      var tipoDesenho = "";
      var botaoDeletar = "";

      botaoDeletar = '<button id="delete-forma">Excluir forma selecionada</button><br>';
      tipoDesenho = "<b>Forma Usada:</b> " + retornaFormaUsadaEmPortugues(selectedShape.type);

      if (selectedShape.type == 'marker')
        valorPeloTipoDesenho = posstr;
      else
      if (selectedShape.type == 'rectangle')
        valorPeloTipoDesenho = bndstr;
      else
      if (selectedShape.type == 'circle')
        valorPeloTipoDesenho = bndstr;
      else
      if (selectedShape.type == 'polyline')
        valorPeloTipoDesenho = pathstr;
      else
      if (selectedShape.type == 'polygon')
        valorPeloTipoDesenho = pathstr;

      function retornaFormaUsadaEmPortugues(forma) {
        if (forma == 'rectangle') 
          return 'Retângulo'
        else
        if (forma == 'marker') 
          return 'Marcador'
         else
        if (forma == 'circle') 
          return 'Círculo'
        else
        if (forma == 'polyline') 
          return 'Linha'
        else
        if (forma == 'polygon')
          return 'Polígono'
      }

      if (valorPeloTipoDesenho != '') {
        var div;
        var tamanho;
        div = document.getElementById("divLocation");
        div.innerHTML = "<br>" + "<br>" + "<br>" + "<br>" + "<br>" + "<br>" + "<br>" + tipoDesenho +
                        "<br>" + "<b>Coordenadas:</b>" + valorPeloTipoDesenho.trim() + 
                        "<br>" + "<br>" + botaoDeletar + 
                        "<br>" + "<br>" +
                        '<p style="font-size: 10px">Nota* Ao alterar a largura ou altura de algum objeto ou forma, '+
                        'é necessário clicar no mesmo para atualizar as coordenadas</p>';
        tamanho = valorPeloTipoDesenho.length;
        div.style.display.height = tamanho;

        // Settings Panel info
        document.getElementById("divLocation").style.fontSize = "12px";

        if (valorPeloTipoDesenho > 2000)
          document.getElementById("divLocation").style.fontSize = "10px";

        // A little of JQuery Cause is so Easy - Like it
        $("#divLocation").fadeIn("slow");
        google.maps.event.addDomListener(document.getElementById('delete-forma'), 'click', deletarFormaeOcultarDiv);
        google.maps.event.addListener(map, 'click', clearSelection);
      }
    }

    function setSelection(shape, isNotMarker) {
      clearSelection();
      selectedShape = shape;
      if (isNotMarker)
        shape.setEditable(true);
      selectColor(shape.get('fillColor') || shape.get('strokeColor'));
      updateCurSelText(shape);
    }

    function deletarFormaeOcultarDiv(){
      deleteSelectedShape()
      var divloc;
      divloc = document.getElementById("divLocation");
      divloc.style.display = 'none';
    }

    function criaAlert() {
      alert('boao direito');
      
    }

    function deleteSelectedShape() {
      if (selectedShape) {
        selectedShape.setMap(null);
      }
    }

    function selectColor(color) {
      selectedColor = color;
      for (var i = 0; i < colors.length; ++i) {
        var currColor = colors[i];
        colorButtons[currColor].style.border = currColor == color ? '2px solid #789' : '2px solid #fff';
      }

      // Retrieves the current options from the drawing manager and replaces the
      // stroke or fill color as appropriate.
      var polylineOptions = drawingManager.get('polylineOptions');
      polylineOptions.strokeColor = color;
      drawingManager.set('polylineOptions', polylineOptions);

      var rectangleOptions = drawingManager.get('rectangleOptions');
      rectangleOptions.fillColor = color;
      drawingManager.set('rectangleOptions', rectangleOptions);

      var circleOptions = drawingManager.get('circleOptions');
      circleOptions.fillColor = color;
      drawingManager.set('circleOptions', circleOptions);

      var polygonOptions = drawingManager.get('polygonOptions');
      polygonOptions.fillColor = color;
      drawingManager.set('polygonOptions', polygonOptions);
    }

    function setSelectedShapeColor(color) {
      if (selectedShape) {
        if (selectedShape.type == google.maps.drawing.OverlayType.POLYLINE) {
          selectedShape.set('strokeColor', color);
        } else {
          selectedShape.set('fillColor', color);
        }
      }
    }

    function makeColorButton(color) {
      var button = document.createElement('span');
      button.className = 'color-button';
      button.style.backgroundColor = color;
      google.maps.event.addDomListener(button, 'click', function () {
        selectColor(color);
        setSelectedShapeColor(color);
      });

      return button;
    }

    function buildColorPalette() {
      var colorPalette = document.getElementById('color-palette');
      for (var i = 0; i < colors.length; ++i) {
        var currColor = colors[i];
        var colorButton = makeColorButton(currColor);
        colorPalette.appendChild(colorButton);
        colorButtons[currColor] = colorButton;
      }
      selectColor(colors[0]);
    }

    // these must have global refs too!:
    var map;
    var placeMarkers = [];
    var input;
    var searchBox;
    var curposdiv;
    var curseldiv;

    function deletePlacesSearchResults() {
      for (var i = 0, marker; marker = placeMarkers[i]; i++) {
        marker.setMap(null);
      }
      placeMarkers = [];
      input.value = ''; // clear the box too
    }

    function initialize() {

       /* Legend of Zoom by Maps
          1:  World 
          5:  Landmass/continent,  
          10: City  
          15: Streets 
          20: Buildings 
       */
        map = new google.maps.Map(document.getElementById('map'), {
        zoom: 5,
        center: new google.maps.LatLng(-25.4950501, -49.4298839), // CURITIBA - BRAZIL
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        disableDefaultUI: false,
        zoomControl: true,
        mapTypeControl: true,
        mapTypeControlOptions: {
          style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
          mapTypeIds: ['roadmap', 'terrain','satelite']
        },
        scaleControl: true,
        streetViewControl: true,
        rotateControl: true,
        fullscreenControl: true
      });
      
      
      var infoWindow = new google.maps.InfoWindow;   
      // Try HTML5 geolocation.
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
          
         var strAuxLat = position.coords.latitude;
         var strAuxLng = position.coords.longitude;

         var pos = {
           lat: position.coords.latitude,
           lng: position.coords.longitude
          };

         infoWindow.setPosition(pos);
         infoWindow.setContent('Localização encontrada'   + '<br>' + 
                                'Coordenadas:'            + '<br>' + 
                                'Latitude: ' + strAuxLat  + '<br>' + 
                                'Longitude:' + strAuxLng);
          infoWindow.open(map);
          map.setCenter(pos);
        }, function () {
          //handleLocationError(true, infoWindow, map.getCenter());
        });
      } else {
        // Browser doesn't support Geolocation
       // handleLocationError(false, infoWindow, map.getCenter());
      }
  
    function handleLocationError(browserHasGeolocation, infoWindow, pos) {
      infoWindow.setPosition(pos);
      infoWindow.setContent(browserHasGeolocation ?
                            'Error: The Geolocation service failed.' :
                            'Error: Your browser doesn\'t support geolocation.');
      infoWindow.open(map);
    }
      curposdiv = document.getElementById('curpos');
      curseldiv = document.getElementById('cursel');

      var polyOptions = {
        strokeWeight: 0,
        fillOpacity: 0.45,
        editable: true,
        draggable: true,
      };
      // Creates a drawing manager attached to the map that allows the user to draw
      // markers, lines, and shapes.
      drawingManager = new google.maps.drawing.DrawingManager({
        drawingMode: google.maps.drawing.OverlayType.POLYGON,
        markerOptions: {
          draggable: true,
          editable: true,
        },
        polylineOptions: {
          editable: true
        },
        rectangleOptions: polyOptions,
        circleOptions: polyOptions,
        polygonOptions: polyOptions,
        map: map
      });

      google.maps.event.addListener(drawingManager, 'overlaycomplete', function (e) {
        var isNotMarker = (e.type != google.maps.drawing.OverlayType.MARKER);
        // Switch back to non-drawing mode after drawing a shape.
        drawingManager.setDrawingMode(null);

        // Add an event listener that selects the newly-drawn shape when the user
        // mouses down on it.
        var newShape = e.overlay;
        newShape.type = e.type;
        google.maps.event.addListener(newShape, 'click', function () {
          setSelection(newShape, isNotMarker);
        });
        google.maps.event.addListener(newShape, 'drag', function () {
          updateCurSelText(newShape);
        });
        google.maps.event.addListener(newShape, 'dragend', function () {
          updateCurSelText(newShape);
        });
        setSelection(newShape, isNotMarker);
      });

      // Clear the current selection when the drawing mode is changed, or when the
      // map is clicked.
      google.maps.event.addListener(drawingManager, 'drawingmode_changed', clearSelection);
      google.maps.event.addListener(map, 'click', clearSelection);
      google.maps.event.addListener(map, 'rightclick', criaAlert);
      google.maps.event.addDomListener(document.getElementById('delete-button'), 'click', deleteSelectedShape);
      google.maps.event.addDomListener(document.getElementById('delete-forma'), 'click', deleteSelectedShape);

      buildColorPalette();

      input = /** @type {HTMLInputElement} */(document.getElementById('pac-input'));

      map.controls[google.maps.ControlPosition.TOP_RIGHT].push(input);
      searchBox = new google.maps.places.SearchBox( 
          /** @type {HTMLInputElement} */(input));

      // Listen for the event fired when the user selects an item from the
      // pick list. Retrieve the matching places for that item.
      google.maps.event.addListener(searchBox, 'places_changed', function () {
        var places = searchBox.getPlaces();

        if (places.length == 0) {
          return;
        }
        for (var i = 0, marker; marker = placeMarkers[i]; i++) {
          marker.setMap(null);
        }

        // For each place, get the icon, place name, and location.
        placeMarkers = [];
        var bounds = new google.maps.LatLngBounds();
        for (var i = 0, place; place = places[i]; i++) {
          var image = {
            url: place.icon,
            size: new google.maps.Size(71, 71),
            origin: new google.maps.Point(0, 0),
            anchor: new google.maps.Point(17, 34),
            scaledSize: new google.maps.Size(25, 25)
          };

          // Create a marker for each place.
          var marker = new google.maps.Marker({
            map: map,
            icon: image,
            title: place.name,
            position: place.geometry.location
          });

          placeMarkers.push(marker);

          bounds.extend(place.geometry.location[i]);
        }

        map.fitBounds(bounds);
      });
      
      // Bias the SearchBox results towards places that are within the bounds of the
      // current map's viewport.
      google.maps.event.addListener(map, 'bounds_changed', function () {
        var bounds = map.getBounds();
        searchBox.setBounds(bounds);
        curposdiv.innerHTML = "<b>curpos</b> Z: " + map.getZoom() + " C: " + map.getCenter().toUrlValue();
      });
    }
    
    google.maps.event.addDomListener(window, 'load', initialize);

  </script>
</head>
<body>
  <div id="panel" hidden>
    <div id="color-palette"></div>
    <div>
      <button id="delete-button">Delete Selected Shape</button>
    </div>
    <div id="curpos"></div>
    <div id="cursel"></div>
  </div>
  <input id="pac-input" type="text" placeholder="Search Box" hidden>
  <div id="map"></div>
  <div id="divLocation">
 

   <button id="delete-forma">Excluir forma selecionada</button>
  </div>

 </body>
</html>