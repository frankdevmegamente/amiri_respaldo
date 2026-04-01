/**

 * Capitaliza la primera letra de cada palabra

 * @param {string} str - Texto a capitalizar

 * @returns {string} - Texto capitalizado

 */

function capitalizeWords(str) {
  if (!str || typeof str !== "string") return str;

  return str
    .trim()

    .toLowerCase()

    .split(" ")

    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))

    .join(" ");
}

/**

 * Auto-capitaliza inputs de texto al escribir

 * Excluye inputs tipo email, number, tel, url

 */

function initAutoCapitalize() {
  document.addEventListener(
    "blur",
    function (e) {
      const input = e.target;

      // Solo procesar inputs y textareas

      if (!input.matches('input[type="text"], input:not([type]), textarea')) {
        return;
      }

      // Excluir inputs con atributos específicos

      if (
        input.type === "email" ||
        input.type === "number" ||
        input.type === "tel" ||
        input.type === "url" ||
        input.type === "date" ||
        input.type === "time" ||
        input.hasAttribute("data-no-capitalize") ||
        input.classList.contains("no-capitalize") ||
        input.name === "telefono" ||
        input.name === "contacto"
      ) {
        return;
      }

      // Si el valor parece ser solo números, no capitalizar

      if (input.value && /^[\d\s\-\+\(\)\.]+$/.test(input.value.trim())) {
        return;
      }

      // Capitalizar el valor

      if (input.value) {
        input.value = capitalizeWords(input.value);
      }
    },
    true,
  );
}

// Inicializar al cargar el documento

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initAutoCapitalize);
} else {
  initAutoCapitalize();
}

function showToast(message, type = "success") {
  const bgColor =
    {
      success: "bg-success",

      error: "bg-danger",

      warning: "bg-warning text-dark",

      info: "bg-info text-dark",
    }[type] || "bg-secondary";

  const toastHtml = `

        <div class="toast ${bgColor} text-white" role="alert" aria-live="assertive" aria-atomic="true">

            <div class="toast-header ${bgColor === "bg-warning text-dark" || bgColor === "bg-info text-dark" ? "" : "text-white"}">

                <strong class="me-auto">

                    ${type === "success" ? "Éxito" : type === "error" ? "Error" : type === "warning" ? "Advertencia" : "Información"}

                </strong>

                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Cerrar"></button>

            </div>

            <div class="toast-body">

                ${message}

            </div>

        </div>

    `;

  const container = document.querySelector(".toast-container");

  container.insertAdjacentHTML("beforeend", toastHtml);

  const toastElement = container.lastElementChild;

  const toast = new bootstrap.Toast(toastElement, {
    autohide: true,

    delay: type === "success" ? 3000 : 5000,
  });

  toast.show();

  toastElement.addEventListener("hidden.bs.toast", () => {
    toastElement.remove();
  });
}

function showToast2(message, concept, resultAction, type) {
  const bgColor =
    {
      success: "bg-info",

      error: "bg-danger",

      warning: "bg-warning",
    }[type] || "bg-secondary";

  const toastHtml = `

        <div class="toast text-black" role="alert" aria-live="assertive" aria-atomic="true">

            <div class="toast-header text-white ${bgColor}">

                <strong class="me-auto">${concept}</strong>

                <small>${resultAction}</small>

                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>

            </div>

            <div class="toast-body">

                ${message}

            </div>

        </div>

    `;

  let container = document.querySelector(".toast-container");

  if (!container) {
    container = document.createElement("div");

    container.className = "toast-container position-fixed bottom-0 end-0 p-3";

    container.style.zIndex = "11";

    document.body.appendChild(container);
  }

  container.insertAdjacentHTML("beforeend", toastHtml);

  const toastElement = container.lastElementChild;

  const toast = new bootstrap.Toast(toastElement, {
    autohide: true,

    delay: type === "success" ? 3000 : 5000,
  });

  toast.show();

  toastElement.addEventListener("hidden.bs.toast", () => {
    toastElement.remove();
  });
}

// Función global para formatear Fechas con Hora en DataTables
function formatearFechaHora(data) {
  if (!data) return "";
  const fecha = new Date(data);
  if (isNaN(fecha)) return data; // Si falla, devuelve el texto original

  return fecha.toLocaleString("es-MX", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
    hour12: true,
  });
}
