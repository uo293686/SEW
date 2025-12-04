class Noticias{
    #busqueda;
    #url;
    constructor(busqueda){
        this.#busqueda = busqueda;
        this.#url = "https://api.thenewsapi.com/v1/news/all/";
    }

    #buscar() {
        const apiKey = "yFu7J1kc7ruttraycvnaPbsTQBtZM4XWtbnNhMVj";

        const urlCompleta = this.#url+"?api_token="+apiKey+"&search="+this.#busqueda+"&language=es";

        return fetch(urlCompleta)
            .then(response => {
                if (!response.ok) {
                    throw new Error("Error en la respuesta del servidor: " + response.status);
                }
                return response.json();
            })
            .catch(error => {
                console.error("Error al obtener noticias:", error);
            });
    }

    #procesarInformacion() {
        return this.#buscar()
            .then(json => {
                if (!json || !json.data) {
                    console.error("JSON inválido o sin noticias.");
                    return [];
                }

                const noticiasProcesadas = json.data.map(noticia => ({
                    titular: noticia.title,
                    entradilla: noticia.description,
                    fecha: noticia.published_at,
                    url: noticia.url,
                    fuente: noticia.source
                }));
                return noticiasProcesadas;
            })
            .catch(error => {
                console.error("Error al procesar las noticias:", error);
                throw error;
            });
    }

    mostrarNoticias(){
        this.#procesarInformacion().then(datos=>{
            if(!datos){
                return;
            }
            const main = document.querySelector("main");
            let section = document.createElement("article");
            section.innerHTML = "";
            datos.forEach( h =>{
                const titular = document.createElement("h3");
                titular.textContent = h.titular;
                section.appendChild(titular);

                const fecha = document.createElement("p");
                fecha.textContent = h.fecha;
                section.appendChild(fecha);

                const entradilla = document.createElement("p");
                entradilla.textContent = h.entradilla;
                section.appendChild(entradilla);

                const url = document.createElement("a");
                url.href = h.url;
                url.textContent = "Leer más"
                section.appendChild(url);

                const fuente = document.createElement("p");
                fuente.textContent = "Fuente: " + h.fuente;
                section.appendChild(fuente);
            })
            main.appendChild(section);
            
        })
    }
}