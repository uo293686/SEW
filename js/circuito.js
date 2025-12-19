class Circuito {
  comprobarAPIFile() {
    if (!(window.File && window.FileReader && window.FileList && window.Blob)) {
      $("main").append("<p>Este navegador no soporta la API File</p>");
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
        $("main").append(`<p>Error al insertar el main: ${error.message}</p>`);
        return;
      }
  
      const parser = new DOMParser();
      const doc = parser.parseFromString(contenido, "text/html");
  
      const mainExterno = doc.querySelector("main");
      if (!mainExterno) {
        $("main").append("<p>El archivo no contiene un &lt;main&gt; válido.</p>");
        return;
      }
  
      $("main").append(mainExterno.innerHTML);
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
        callback(null, evento.target.result);
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

          $("main").append(contenido);
      });
  }
}

class CargadorKML {
  static mapa;

    constructor(mapa) {
        this.mapa = mapa;
    }

    leerArchivoKML(archivo) {
        if (!archivo) return;

        const lector = new FileReader();

        lector.onload = (evento) => {
            const xml = new DOMParser().parseFromString(evento.target.result, "application/xml");
            const coords = xml.querySelector("LineString coordinates")?.textContent;
            
            if (!coords) return;
            
            const puntos = coords
                .trim()
                .split(/\s+/)
                .map(linea => linea.split(",").map(Number))
                .filter(p => p.length >= 2);
            
            if (puntos.length > 1) this.insertarCapaKML(puntos);
        };

        lector.readAsText(archivo);
    }

    insertarCapaKML(puntos) {
        new mapboxgl.Marker()
            .setLngLat(puntos[0])
            .addTo(this.mapa);

        this.mapa.addSource("circuito", {
            type: "geojson",
            data: {
                type: "Feature",
                geometry: {
                    type: "LineString",
                    coordinates: puntos
                }
            }
        });

        this.mapa.addLayer({
            id: "coordinates",
            type: "line",
            source: "circuito",
            paint: {
                "line-color": "#ff0000",
                "line-width": 3
            }
            });

            this.mapa.setCenter(puntos[0]);
    }
}