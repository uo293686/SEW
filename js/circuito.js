class Circuito {
  comprobarAPIFile() {
    const main = document.querySelector("main");
    const mensaje = document.createElement("p");
  
    if (!(window.File && window.FileReader && window.FileList && window.Blob)) {
      mensaje.textContent = "Este navegador no soporta la API File";
      main.appendChild(mensaje);
      return false;
    }
    return true;
  }
  
  leerArchivoHTML(file, callback) {
    const lector = new FileReader();
  
    lector.onload = (event) => {
      callback(null, event.target.result);
    };
  
    lector.onerror = (error) => {
      callback(error, null);
    };
  
    lector.readAsText(file);
  }
  
  rellenaDOM(file) {
    this.leerArchivoHTML(file, (error, contenido) => {
      if (error) {
        console.error("Error al insertar el main:", error);
        document.querySelector("main").innerHTML +=
          `<p>Error al insertar el main: ${error.message}</p>`;
        return;
      }
  
      const parser = new DOMParser();
      const doc = parser.parseFromString(contenido, "text/html");
  
      const mainExterno = doc.querySelector("main");
      if (!mainExterno) {
        document.querySelector("main").innerHTML +=
          "<p>El archivo no contiene un &lt;main&gt; válido.</p>";
        return;
      }
  
      const mainActual = document.querySelector("main");
      mainActual.innerHTML += mainExterno.innerHTML;
    });
  }
} 
class CargadorSVG {
  leerArchivoSVG(file, callback) {
    if (!(file instanceof File)) {
        callback("El argumento no es un archivo válido.", null);
        return;
    }

    if (!file.type.includes("svg") && !file.name.endsWith(".svg")) {
        callback("El archivo no es un SVG válido.", null);
        return;
    }

    const lector = new FileReader();

    lector.onload = (evento) => {
        callback(null, evento.target.result); // devolvemos el contenido
    };

    lector.onerror = () => {
        callback("Error al leer el archivo SVG.", null);
    };

    lector.readAsText(file);
  }

  insertarSVG(file) {
      this.leerArchivoSVG(file, (error, contenido) => {
          if (error) {
              console.error("Error al insertar el SVG:", error);
              return;
          }

          const destino = document.querySelector("main");
          if (destino) {
              destino.innerHTML += contenido;
          } else {
              console.error(`No se encontró el elemento destino: ${selectorDestino}`);
          }
      });
  }
}

class CargadorKML {
  static insertarCapaKML(map) {
    const fileInput = document.getElementById("inputKML");
    const file = fileInput.files[0];
    if (!file) {
      console.error("No se seleccionó ningún archivo.");
      return;
    }

    const lector = new FileReader();
    lector.onload = (e) => {
      const contenido = e.target.result;
      const xml = $.parseXML(contenido);
      const $xml = $(xml);

      const lat = parseFloat($xml.find("coordinates > latitud").text());
      const lng = parseFloat($xml.find("coordinates > longitud").text());
      const origen = [lng, lat];

      const tramos = [];
      $xml.find("tramo").each((i, tramo) => {
        const endLat = parseFloat($(tramo).find("endlatitud").text());
        const endLng = parseFloat($(tramo).find("endlongitud").text());
        tramos.push([endLng, endLat]);
      });

      const geojson = {
        type: "FeatureCollection",
        features: [
          {
            type: "Feature",
            geometry: {
              type: "LineString",
              coordinates: [origen, ...tramos]
            },
            properties: {}
          },
          {
            type: "Feature",
            geometry: {
              type: "Point",
              coordinates: origen
            },
            properties: { title: "Origen del circuito" }
          }
        ]
      };

      if (map.getSource("circuito")) {
        map.removeLayer("circuito-line");
        map.removeLayer("circuito-origen");
        map.removeSource("circuito");
      }

      map.addSource("circuito", { type: "geojson", data: geojson });

      map.addLayer({
        id: "circuito-line",
        type: "line",
        source: "circuito",
        paint: {
          "line-color": "#ff0000",
          "line-width": 3
        },
        filter: ["==", "$type", "LineString"]
      });

      map.addLayer({
        id: "circuito-origen",
        type: "circle",
        source: "circuito",
        paint: {
          "circle-radius": 6,
          "circle-color": "#0000ff"
        },
        filter: ["==", "$type", "Point"]
      });

      const bounds = geojson.features[0].geometry.coordinates.reduce((b, coord) => {
        return b.extend(coord);
      }, new mapboxgl.LngLatBounds(origen, origen));

      map.fitBounds(bounds, { padding: 50 });
    };

    lector.readAsText(file);
  }
}
