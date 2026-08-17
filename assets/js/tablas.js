document.addEventListener("DOMContentLoaded", function () {

    const contenedores =
        document.querySelectorAll("[data-tabla-buscable]");

    contenedores.forEach(function (contenedor) {

        const buscador =
            contenedor.querySelector("[data-buscador]");

        const tabla =
            contenedor.querySelector("table");

        const contador =
            contenedor.querySelector(
                "[data-contador-resultados]"
            );

        const mensajeVacio =
            contenedor.querySelector(
                "[data-sin-resultados]"
            );

        if (!buscador || !tabla) {
            return;
        }

        const tbody = tabla.querySelector("tbody");

        if (!tbody) {
            return;
        }

        const obtenerFilas = function () {

            return Array.from(
                tbody.querySelectorAll(
                    "tr:not([data-sin-resultados])"
                )
            );
        };

        buscador.addEventListener(
            "input",
            function () {

                const texto =
                    buscador.value
                        .toLowerCase()
                        .trim();

                let visibles = 0;

                obtenerFilas().forEach(function (fila) {

                    const contenido =
                        fila.textContent
                            .toLowerCase()
                            .replace(/\s+/g, " ")
                            .trim();

                    const coincide =
                        contenido.includes(texto);

                    fila.style.display =
                        coincide ? "" : "none";

                    if (coincide) {
                        visibles++;
                    }

                });

                if (contador) {

                    if (texto === "") {

                        contador.textContent = "";

                    } else if (visibles === 1) {

                        contador.textContent =
                            "1 resultado encontrado";

                    } else {

                        contador.textContent =
                            visibles +
                            " resultados encontrados";
                    }
                }

                if (mensajeVacio) {

                    mensajeVacio.style.display =
                        visibles === 0 &&
                        texto !== ""
                            ? ""
                            : "none";
                }

            }
        );

        const encabezados =
            tabla.querySelectorAll(
                "th[data-ordenable]"
            );

        encabezados.forEach(function (encabezado) {

            encabezado.addEventListener(
                "click",
                function () {

                    const indice =
                        Array.from(
                            encabezado
                                .parentElement
                                .children
                        ).indexOf(encabezado);

                    const tipo =
                        encabezado.dataset.tipo ||
                        "texto";

                    const direccionAnterior =
                        encabezado.dataset.direccion ||
                        "desc";

                    const nuevaDireccion =
                        direccionAnterior === "asc"
                            ? "desc"
                            : "asc";

                    encabezados.forEach(
                        function (otro) {

                            otro.dataset.direccion = "";

                            otro.classList.remove(
                                "orden-asc",
                                "orden-desc"
                            );

                        }
                    );

                    encabezado.dataset.direccion =
                        nuevaDireccion;

                    encabezado.classList.add(
                        nuevaDireccion === "asc"
                            ? "orden-asc"
                            : "orden-desc"
                    );

                    const filas = obtenerFilas();

                    filas.sort(
                        function (filaA, filaB) {

                            const valorA =
                                filaA.children[indice]
                                    ?.dataset.orden ||
                                filaA.children[indice]
                                    ?.textContent
                                    .trim() ||
                                "";

                            const valorB =
                                filaB.children[indice]
                                    ?.dataset.orden ||
                                filaB.children[indice]
                                    ?.textContent
                                    .trim() ||
                                "";

                            let comparacion = 0;

                            if (tipo === "numero") {

                                const numeroA =
                                    parseFloat(
                                        valorA.replace(
                                            /[^0-9.-]/g,
                                            ""
                                        )
                                    ) || 0;

                                const numeroB =
                                    parseFloat(
                                        valorB.replace(
                                            /[^0-9.-]/g,
                                            ""
                                        )
                                    ) || 0;

                                comparacion =
                                    numeroA - numeroB;

                            } else {

                                comparacion =
                                    valorA.localeCompare(
                                        valorB,
                                        "es",
                                        {
                                            sensitivity:
                                                "base"
                                        }
                                    );
                            }

                            return nuevaDireccion ===
                                   "asc"
                                ? comparacion
                                : -comparacion;

                        }
                    );

                    filas.forEach(function (fila) {
                        tbody.appendChild(fila);
                    });

                }
            );

        });

    });

});