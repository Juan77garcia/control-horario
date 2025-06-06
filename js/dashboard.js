 // Mostrar la hora actual en tiempo real
    setInterval(() => {
        const ahora = new Date();
        document.getElementById("hora").textContent = ahora.toLocaleTimeString();
    }, 1000);