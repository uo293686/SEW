class Carrusel {
    #busqueda;
    #actual;
    #maximo;

    constructor(busqueda) {
        this.#busqueda = busqueda;
        this.#actual = 0;
        this.#maximo = 4;
    }

    #getFotografias() {
        const flickrAPI = "https://api.flickr.com/services/feeds/photos_public.gne?jsoncallback=?";
        return $.getJSON(
            flickrAPI,
            {
                tags: "Autodromo Internazionale del Mugello,mugello circuit,mugello,motogp",
                tagmode: "any",
                format: "json"
            }
        );
    }

    async #procesarJSONFotografias() {
        const json = await this.#getFotografias();

        if (!json || !json.items) {
            console.error("JSON no válido");
            return [];
        }

        const fotos = [];
        const total = json.items.length;

        for (let i = 0; i < total && i <= this.#maximo; i++) {
            const item = json.items[i];
            fotos.push({
                titulo: item.title,
                url: item.media.m
            });
        }

        return fotos;
    }

    async mostrarFotografias() {
        const fotos = await this.#procesarJSONFotografias();

        if (fotos.length === 0) {
            console.error("No hay fotos para mostrar");
            return;
        }

        const section = document.querySelector("main");

        const article = document.createElement("article");

        const h2 = document.createElement("h2");
        h2.textContent = `Imágenes del circuito de ${this.#busqueda}`;

        const img = document.createElement("img");
        img.id = "foto-carrusel";
        img.src = fotos[0].url;
        img.alt = fotos[0].titulo;

        
        img.dataset.fotos = JSON.stringify(fotos);

        article.appendChild(h2);
        article.appendChild(img);
        section.appendChild(article);

        setInterval(this.cambiarFotografia.bind(this), 3000);
    }

    cambiarFotografia() {
        const img = document.querySelector("#foto-carrusel");
        if (!img) return;

        const fotos = JSON.parse(img.dataset.fotos);
        if (!fotos || fotos.length === 0) return;

        this.#actual = (this.#actual + 1) % fotos.length;

        const nuevaFoto = fotos[this.#actual];

        $(img)
            .attr("src", nuevaFoto.url)
            .attr("alt", nuevaFoto.titulo);
    }
}
