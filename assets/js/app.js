// app.js
// JS general para TopicosBD
// Comentarios en español, nombres de archivo en inglés ;)

/**
 * Resalta una columna del horario (tabla .tb-table-schedule) según el día.
 *
 * @param {HTMLTableElement} table
 * @param {string} dayKey  - Clave del día, por ejemplo "LUN", "MAR", etc.
 * @param {boolean} active - true para activar el resaltado, false para quitarlo.
 */
function tbSetScheduleColumnHighlight(table, dayKey, active) {
    if (!table || !dayKey) return;

    // Cabecera del día
    const header = table.querySelector('thead th[data-day="' + dayKey + '"]');
    if (header) {
        if (active) {
            if (!header.dataset.origBg) {
                header.dataset.origBg = header.style.backgroundColor || '';
            }
            header.style.backgroundColor = 'rgba(139, 211, 206, 0.25)'; // teal suave
        } else {
            if ('origBg' in header.dataset) {
                header.style.backgroundColor = header.dataset.origBg;
            }
        }
    }

    // Celdas del cuerpo para ese día
    const cells = table.querySelectorAll('tbody td[data-day="' + dayKey + '"]');
    cells.forEach(function (cell) {
        if (active) {
            if (!cell.dataset.origBg) {
                cell.dataset.origBg = cell.style.backgroundColor || '';
            }
            cell.style.backgroundColor = 'rgba(139, 211, 206, 0.16)';
        } else {
            if ('origBg' in cell.dataset) {
                cell.style.backgroundColor = cell.dataset.origBg;
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    // Interacciones para tablas de horario
    var scheduleTables = document.querySelectorAll('.tb-table-schedule');

    scheduleTables.forEach(function (table) {
        var headers = table.querySelectorAll('thead th[data-day]');

        headers.forEach(function (header) {
            var dayKey = header.dataset.day;

            header.addEventListener('mouseenter', function () {
                tbSetScheduleColumnHighlight(table, dayKey, true);
            });

            header.addEventListener('mouseleave', function () {
                tbSetScheduleColumnHighlight(table, dayKey, false);
            });
        });
    });
});
