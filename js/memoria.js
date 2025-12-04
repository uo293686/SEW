class Memoria{
    #tablero_bloqueado;
    #primera_carta;
    #segunda_carta;
    #cronometro;

    constructor(){
        this.#tablero_bloqueado = true;
        this.#primera_carta = null;
        this.#segunda_carta = null;
        this.#cronometro = new Cronometro();
        this.#barajarCartas();
        this.#tablero_bloqueado = false;
        this.#cronometro.arrancar();
    }
    volteaCarta(carta) {
        const estado = carta.getAttribute("data-estado");
    
        const cartaDeshabilitada = estado === "revelada";
        const cartaYaVolteada = estado === "volteada";
        const tableroBloqueado = this.#tablero_bloqueado;
    
        if (cartaDeshabilitada || cartaYaVolteada || tableroBloqueado) {
            return;
        }
    
        carta.setAttribute("data-estado", "volteada");
    
        if (!this.#primera_carta) {
            this.#primera_carta = carta;
            return;
        }
        this.#segunda_carta = carta;
    
        this.#tablero_bloqueado = true;
    
        this.#comprobarPareja();
    }
    

    #barajarCartas() {
        const main = document.querySelector("main");
        const cartas = Array.from(main.querySelectorAll("article"));
        for (let i = cartas.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [cartas[i], cartas[j]] = [cartas[j], cartas[i]];
        }
        cartas.forEach(carta => main.appendChild(carta));
    }

    #reiniciarAtributos(){
        this.#tablero_bloqueado = false;
        this.#primera_carta = null;
        this.#segunda_carta = null;
    }

    #deshabilitarCartas() {
        this.#primera_carta.setAttribute("data-estado", "revelada");
        this.#segunda_carta.setAttribute("data-estado", "revelada");
    
        this.#primera_carta.removeAttribute("onclick");
        this.#segunda_carta.removeAttribute("onclick");

        this.#comprobarJuego();
    
        this.#reiniciarAtributos();
    }

    #comprobarJuego() {
        const cartas = document.querySelectorAll("main article");
        const todasReveladas = Array.from(cartas).every(carta =>
            carta.getAttribute("data-estado") === "revelada"
        );
        if(todasReveladas==true){
            this.#cronometro.parar();
        }
    }

    #cubrirCartas() {
        this.#tablero_bloqueado = true;
    
        setTimeout(() => {
            this.#primera_carta.removeAttribute("data-estado");
            this.#segunda_carta.removeAttribute("data-estado");
            this.#reiniciarAtributos();
        }, 1500);
    }

    #comprobarPareja() {
        if (this.#primera_carta && this.#segunda_carta) {
            const img1 = this.#primera_carta.querySelector("img").getAttribute("src");
            const img2 = this.#segunda_carta.querySelector("img").getAttribute("src");
    
            img1 === img2 ? this.#deshabilitarCartas() : this.#cubrirCartas();
        }
    }
}