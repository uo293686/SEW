class Cronometro {
    #tiempoAcumulado;
    #intervalo;
    #inicio;

    constructor() {
        this.#tiempoAcumulado = 0;
        this.#intervalo = null;
        this.#inicio = null;
    }

    arrancar() {
        if (this.#intervalo) return; // Ya está corriendo

        try {
            this.#inicio = Temporal.Now.instant()
        } catch (e) {
            this.#inicio = new Date();
        }

        this.#intervalo = setInterval(() => {
            this.actualizar();
        }, 100);
    }

    actualizar() {
        let tiempoTranscurrido;
        try {
            const ahora = Temporal.Now.instant();
            tiempoTranscurrido = ahora.since(this.#inicio).total({ unit: "milliseconds" });
            this.tiempo = tiempoTranscurrido + this.#tiempoAcumulado;
        } catch(e) {
            tiempoTranscurrido = new Date() - this.#inicio;
            this.tiempo = tiempoTranscurrido + this.#tiempoAcumulado;
        }

        this.mostrar();
    }

    mostrar() {
        const ms = this.tiempo || 0;
        document.querySelector("main p").textContent = this.#formatear(ms);
    }

    #formatear(ms) {
        const totalSegundos = ms / 1000;
        const minutos = Math.floor(totalSegundos / 60);
        const segundos = Math.floor(totalSegundos % 60);
        const decimas = Math.floor((totalSegundos - Math.floor(totalSegundos)) * 10);

        const ss = String(segundos).padStart(2, "0");
        const mm = String(minutos).padStart(2, "0");
        return `${mm}:${ss}.${decimas}`;
    }

    parar() {
        if (this.#intervalo) {
            clearInterval(this.#intervalo);
            this.#intervalo = null;
            try {
                const ahora = Temporal.Now.instant();
                const transcurrido = ahora.since(this.#inicio).total({ unit: "milliseconds" });
                this.#tiempoAcumulado += transcurrido;
            } catch(e){
                this.#tiempoAcumulado += new Date() - this.#inicio;
            }
        }

        this.mostrar();
    }

    reiniciar() {
        this.parar();
        this.#tiempoAcumulado = 0;
        this.tiempo = 0;
        this.mostrar();
    }
}