
class Ciudad{
    #nombre;
    #gentilicio;
    #pais;
    #poblacion;
    #latitud;
    #longitud;
    constructor(nombre, pais, gentilicio){
        this.#nombre = nombre;
        this.#gentilicio = gentilicio;
        this.#pais = pais;
    }
    setPoblacion(poblacion){
        this.#poblacion = poblacion;
    }
    setCoordenadas(latitud, longitud){
        this.#latitud = latitud;
        this.#longitud = longitud;
    }
    getNombre(){
        return ""+this.#nombre;
    }
    getPais(){
        return ""+this.#pais;
    }
    getInfoSecundaria() {
        var section = document.querySelector("main section");
        var lista = document.createElement("ul");

        var itemGentilicio = document.createElement("li");
        itemGentilicio.textContent = "Gentilicio: " + this.#gentilicio;
        lista.appendChild(itemGentilicio);

        var itemPoblacion = document.createElement("li");
        itemPoblacion.textContent = "Población: " + this.#poblacion;
        lista.appendChild(itemPoblacion);

        section.appendChild(lista);
    }
    getCoordenadas(){
        var section = document.querySelector("main section");
        const coord = document.createElement("p");
        coord.textContent = "Latitud: " + this.#latitud + ", Longitud: " + this.#longitud;
        section.appendChild(coord);
    }

    #getMeteorologiaCarrera() {
        try{return $.ajax({
            url: "https://archive-api.open-meteo.com/v1/archive",
            method: "GET",
            data: {
                latitude: this.#latitud,
                longitude: this.#longitud,
                hourly: "temperature_2m,apparent_temperature,precipitation,relative_humidity_2m,wind_speed_10m,wind_direction_10m",
                daily: "sunrise,sunset",
                timezone: "auto",
                start_date: "2025-06-22",
                end_date: "2025-06-22"
            },
            dataType: "json"
            
        }) }
        catch (error) {
            console.error("Error al obtener los datos de la API:", error);
        }
        
    }  

   #procesarJSONCarrera() {
    return this.#getMeteorologiaCarrera()
        .then(json => {
            if (!json || !json.hourly || !json.daily) {
                console.error("JSON inválido o incompleto.");
                return null;
            }

            const { hourly, daily } = json;
            if (!hourly.time || !daily.time) {
                console.error("Faltan arrays de tiempo en la respuesta.");
                return null;
            }

            const lenH = hourly.time.length;
            const camposH = [
                "temperature_2m",
                "apparent_temperature",
                "precipitation",
                "relative_humidity_2m",
                "wind_speed_10m",
                "wind_direction_10m"
            ];
            const faltaCampo = camposH.some(k => !hourly[k] || hourly[k].length !== lenH);
            if (faltaCampo) {
                console.error("Longitudes inconsistentes en datos horarios.");
                return null;
            }

            const datosHorarios = hourly.time.map((hora, i) => ({
                hora,
                temperatura_2m: hourly.temperature_2m[i],
                sensacion_termica: hourly.apparent_temperature[i],
                lluvia: hourly.precipitation[i],
                humedad_relativa_2m: hourly.relative_humidity_2m[i],
                viento_velocidad_10m: (hourly.wind_speed_10m[i] * 3.6).toFixed(1),
                viento_direccion_10m: hourly.wind_direction_10m[i]
            }));

            const lenD = daily.time.length;
            if (!daily.sunrise || !daily.sunset || daily.sunrise.length !== lenD || daily.sunset.length !== lenD) {
                console.error("Longitudes inconsistentes en datos diarios.");
                return null;
            }

            const datosDiarios = daily.time.map((dia, i) => ({
                dia,
                salida_sol: daily.sunrise[i],
                puesta_sol: daily.sunset[i]
            }));

            return {
                fecha: "2025-06-22",
                datos_horarios: datosHorarios,
                datos_diarios: datosDiarios
            };
        })
        .catch(error => {
            console.error("Error al procesar los datos del JSON:", error);
            throw error;
        });
}


    mostrarDatosCarrera(){
        this.#procesarJSONCarrera().then(datos => {
        if (!datos) return;

        const main = document.querySelector("main");
        let seccion = document.createElement("section");
        seccion.innerHTML = "";

        const tituloDia = document.createElement("h3");
        tituloDia.textContent = "Carrera del día: " + datos.fecha;
        seccion.appendChild(tituloDia);

        const amanecer = document.createElement("p");
        amanecer.innerHTML = "<strong>Amanecer:</strong> " + datos.datos_diarios[0].salida_sol;
        seccion.appendChild(amanecer);

        const atardecer = document.createElement("p");
        atardecer.innerHTML = "<strong>Atardecer:</strong> " + datos.datos_diarios[0].puesta_sol;
        seccion.appendChild(atardecer);

        const subtitulo = document.createElement("h3");
        subtitulo.textContent = "Datos por hora:";
        seccion.appendChild(subtitulo);

        const lista = document.createElement("ul");
        datos.datos_horarios.forEach(h => {
            const item = document.createElement("li");
            item.innerHTML =
                "<h4>Hora: " + h.hora + "</h4>" +
                "<p>Temperatura: " + h.temperatura_2m + "°C</p>" +
                "<p>Sensación térmica: " + h.sensacion_termica + "°C</p>" +
                "<p>Lluvia: " + h.lluvia + " mm</p>" +
                "<p>Humedad: " + h.humedad_relativa_2m + "%</p>" +
                "<p>Viento: " + h.viento_velocidad_10m + " km/h</p>" +
                "<p>Dirección del viento: " + h.viento_direccion_10m + "°";
            lista.appendChild(item);
        });

        seccion.appendChild(lista);
        main.appendChild(seccion);
    });
    }

    #getMeteorologiaEntrenos() {
        try{return $.ajax({
            url: "https://archive-api.open-meteo.com/v1/archive",
            method: "GET",
            data: {
                latitude: this.#latitud,
                longitude: this.#longitud,
                hourly: "temperature_2m,apparent_temperature,precipitation,relative_humidity_2m,wind_speed_10m,wind_direction_10m",
                daily: "sunrise,sunset",
                timezone: "auto",
                start_date: "2025-06-19",
                end_date: "2025-06-21"
            },
            dataType: "json"
            
        }) }
        catch (error) {
            console.error("Error al obtener los datos de la API:", error);
        }
        
    }  

    #procesarJSONEntrenos() {
    return this.#getMeteorologiaEntrenos()
        .then(json => {
            if (!json || !json.hourly || !json.daily) {
                console.error("JSON inválido o incompleto.");
                return null;
            }

            const { hourly, daily } = json;
            const datosPorDia = {};

            hourly.time.forEach((hora, i) => {
                const dia = hora.split("T")[0];
                if (!datosPorDia[dia]) {
                    datosPorDia[dia] = {
                        temperatura: [],
                        sensacion_termica: [],
                        lluvia: [],
                        humedad: [],
                        viento_velocidad: [],
                        viento_direccion: []
                    };
                }

                datosPorDia[dia].temperatura.push(hourly.temperature_2m[i]);
                datosPorDia[dia].sensacion_termica.push(hourly.apparent_temperature[i]);
                datosPorDia[dia].lluvia.push(hourly.precipitation[i]);
                datosPorDia[dia].humedad.push(hourly.relative_humidity_2m[i]);
                datosPorDia[dia].viento_velocidad.push(hourly.wind_speed_10m[i] * 3.6);
                datosPorDia[dia].viento_direccion.push(hourly.wind_direction_10m[i]);
            });

            const mediasPorDia = Object.keys(datosPorDia).map(dia => {
                const datos = datosPorDia[dia];
                return {
                    dia,
                    temperatura_media: this.#calcularMedia(datos.temperatura),
                    sensacion_termica_media: this.#calcularMedia(datos.sensacion_termica),
                    lluvia_media: this.#calcularMedia(datos.lluvia),
                    humedad_media: this.#calcularMedia(datos.humedad),
                    viento_velocidad_media: this.#calcularMedia(datos.viento_velocidad),
                    viento_direccion_media: this.#calcularMedia(datos.viento_direccion),
                    salida_sol: daily.sunrise[daily.time.indexOf(dia)],
                    puesta_sol: daily.sunset[daily.time.indexOf(dia)]
                };
            });

            return {
                rango_fechas: `${daily.time[0]} a ${daily.time[daily.time.length - 1]}`,
                medias: mediasPorDia
            };
        })
        .catch(error => {
            console.error("Error al procesar los datos del JSON:", error);
            throw error;
        });
    }
    
    #calcularMedia(arr) {
        if (!arr || arr.length === 0) return 0;
        const suma = arr.reduce((total, valor) => total + valor, 0);
        return (suma / arr.length).toFixed(2);
    }

    mostrarDatosEntrenos() {
    this.#procesarJSONEntrenos().then(datos => {
        if (!datos) return;

        const main = document.querySelector("main");
        let seccion = document.createElement("section");
        seccion.innerHTML = "";

        const tituloRango = document.createElement("h3");
        tituloRango.textContent = "Entrenamientos: " + datos.rango_fechas;
        seccion.appendChild(tituloRango);

        datos.medias.forEach(d => {
            const tituloDia = document.createElement("h4");
            tituloDia.textContent = "Día: " + d.dia;
            seccion.appendChild(tituloDia);

            const amanecer = document.createElement("p");
            amanecer.innerHTML = "Amanecer: " + d.salida_sol;
            seccion.appendChild(amanecer);

            const atardecer = document.createElement("p");
            atardecer.innerHTML = "Atardecer: " + d.puesta_sol;
            seccion.appendChild(atardecer);

            const lista = document.createElement("ul");

            const itemTemp = document.createElement("li");
            itemTemp.textContent = "Temperatura media: " + d.temperatura_media + "°C";
            lista.appendChild(itemTemp);

            const itemSens = document.createElement("li");
            itemSens.textContent = "Sensación térmica media: " + d.sensacion_termica_media + "°C";
            lista.appendChild(itemSens);

            const itemLluvia = document.createElement("li");
            itemLluvia.textContent = "Lluvia media: " + d.lluvia_media + " mm";
            lista.appendChild(itemLluvia);

            const itemHum = document.createElement("li");
            itemHum.textContent = "Humedad media: " + d.humedad_media + "%";
            lista.appendChild(itemHum);

            const itemViento = document.createElement("li");
            itemViento.textContent = "Viento medio: " + d.viento_velocidad_media + " km/h";
            lista.appendChild(itemViento);

            const itemDir = document.createElement("li");
            itemDir.textContent = "Dirección media del viento: " + d.viento_direccion_media + "°";
            lista.appendChild(itemDir);

            seccion.appendChild(lista);
        });

        main.appendChild(seccion);
    });
}



}