(function () {
    'use strict';

    var config = window.DeportesTotalPOS;
    if (!config) return;

    var cart = Object.create(null);
    var selectedClient = null;
    var selectedChannel = '';
    var selectedPayment = '';
    var currency = new Intl.NumberFormat('es-CO', { maximumFractionDigits: 0 });

    var form = document.getElementById('formVenta');
    var modal = document.getElementById('modalCliente');
    var modalForm = document.getElementById('formClienteNuevo');
    var modalError = document.getElementById('errorModalCliente');
    var finishButton = document.getElementById('finalizarVenta');

    function cents(value) {
        var parts = String(value || '0').split('.');
        var whole = parseInt(parts[0], 10) || 0;
        var fraction = (parts[1] || '').slice(0, 2).padEnd(2, '0');
        return (whole * 100) + (parseInt(fraction, 10) || 0);
    }

    function moneyCents(amount) {
        var whole = Math.floor(amount / 100);
        var fraction = amount % 100;
        return '$' + currency.format(whole) + (fraction ? ',' + String(fraction).padStart(2, '0') : '');
    }

    function money(value) {
        return moneyCents(cents(value));
    }

    function toast(message, type, persistent) {
        if (window.DeportesTotalNotifications) {
            window.DeportesTotalNotifications.show(message, type || 'success', !!persistent);
        }
    }

    function updateCsrf(payload) {
        if (!payload || !payload.csrf || !payload.csrf.name || !payload.csrf.hash) return;
        var previousName = config.csrfName;
        var nextName = payload.csrf.name;
        document.querySelectorAll('input[type="hidden"]').forEach(function (input) {
            if (input.name === previousName || input.name === nextName) {
                input.name = nextName;
                input.value = payload.csrf.hash;
            }
        });
        config.csrfName = nextName;
    }

    function setResultsMessage(container, message) {
        container.replaceChildren();
        var row = document.createElement('div');
        row.className = 'pos-results-message';
        row.textContent = message;
        container.appendChild(row);
        container.classList.add('open');
    }

    function hideResults(container) {
        container.classList.remove('open');
        container.replaceChildren();
    }

    function createResultButton(product, searchInput) {
        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'pos-result';
        button.setAttribute('role', 'option');

        var copy = document.createElement('span');
        copy.className = 'pos-result-copy';
        var name = document.createElement('strong');
        name.textContent = product.nombre;
        var details = document.createElement('span');
        details.textContent = 'Ref. ' + product.referencia + ' · Talla ' + (product.talla || 'Única') + ' · ' + money(product.precio);
        copy.appendChild(name);
        copy.appendChild(details);

        var stock = document.createElement('span');
        stock.className = 'pos-result-stock';
        stock.textContent = product.stock + ' disponibles';
        button.appendChild(copy);
        button.appendChild(stock);
        button.addEventListener('click', function () { addProductToCart(product, searchInput); });
        return button;
    }

    function highlightResult(results, index) {
        var options = Array.prototype.slice.call(results.querySelectorAll('.pos-result'));
        if (!options.length) return -1;
        index = Math.max(0, Math.min(index, options.length - 1));
        options.forEach(function (option, position) {
            option.classList.toggle('keyboard-active', position === index);
            option.setAttribute('aria-selected', position === index ? 'true' : 'false');
        });
        options[index].scrollIntoView({block: 'nearest'});
        return index;
    }

    function bindProductSearch(inputId, resultsId, field) {
        var input = document.getElementById(inputId);
        var results = document.getElementById(resultsId);
        var timer = null;
        var requestNumber = 0;

        input.addEventListener('keydown', function (event) {
            var options = results.querySelectorAll('.pos-result');
            var active = Array.prototype.findIndex.call(options, function (option) { return option.classList.contains('keyboard-active'); });
            if (event.key === 'ArrowDown' && options.length) {
                event.preventDefault();
                highlightResult(results, active < 0 ? 0 : active + 1);
            } else if (event.key === 'ArrowUp' && options.length) {
                event.preventDefault();
                highlightResult(results, active < 0 ? options.length - 1 : active - 1);
            } else if (event.key === 'Enter' && results.classList.contains('open')) {
                if (options.length) {
                    event.preventDefault();
                    var chosen = active >= 0 ? options[active] : options[0];
                    chosen.click();
                }
            } else if (event.key === 'Escape' && results.classList.contains('open')) {
                event.preventDefault();
                hideResults(results);
            }
        });

        input.addEventListener('input', function () {
            window.clearTimeout(timer);
            var term = input.value.trim();
            if (term.length < 2) {
                hideResults(results);
                return;
            }
            timer = window.setTimeout(async function () {
                var thisRequest = ++requestNumber;
                setResultsMessage(results, 'Buscando productos…');
                try {
                    var url = new URL(config.productSearchUrl, window.location.href);
                    url.searchParams.set('campo', field);
                    url.searchParams.set('q', term);
                    var response = await fetch(url.toString(), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
                    var data = await response.json();
                    if (thisRequest !== requestNumber || term !== input.value.trim()) return;
                    if (!response.ok) throw new Error('No fue posible buscar productos.');
                    results.replaceChildren();
                    if (!data.resultados || data.resultados.length === 0) {
                        setResultsMessage(results, 'No hay productos activos con existencias que coincidan.');
                        return;
                    }
                    data.resultados.forEach(function (product) { results.appendChild(createResultButton(product, input)); });
                    results.classList.add('open');
                } catch (error) {
                    if (thisRequest === requestNumber) setResultsMessage(results, error.message || 'Error de conexión.');
                }
            }, 260);
        });

        input.addEventListener('focus', function () {
            if (results.children.length) results.classList.add('open');
        });
    }

    function clampQuantity(input, max) {
        var quantity = Math.floor(Number(input.value));
        if (!Number.isFinite(quantity) || quantity < 1) quantity = 1;
        if (quantity > max) quantity = max;
        input.value = String(quantity);
        return quantity;
    }

    function addProductToCart(product, searchInput) {
        var key = String(product.id);
        var current = cart[key] ? cart[key].cantidad : 0;
        if (current + 1 > product.stock) {
            toast('La cantidad total supera las existencias disponibles (' + product.stock + ').', 'error', true);
            searchInput.focus();
            return;
        }
        if (cart[key]) cart[key].cantidad += 1;
        else cart[key] = { producto: product, cantidad: 1 };
        renderCart();
        searchInput.value = '';
        hideResults(document.getElementById('resultadosNombre'));
        hideResults(document.getElementById('resultadosReferencia'));
        searchInput.focus();
    }

    function createCartRow(key, line) {
        var row = document.createElement('tr');
        row.dataset.id = key;

        var productCell = document.createElement('td');
        var productCopy = document.createElement('div');
        productCopy.className = 'pos-cart-product';
        var productName = document.createElement('strong');
        productName.textContent = line.producto.nombre;
        var productMeta = document.createElement('span');
        productMeta.textContent = 'Ref. ' + line.producto.referencia + ' · Talla ' + (line.producto.talla || 'Única');
        productCopy.appendChild(productName);
        productCopy.appendChild(productMeta);
        productCell.appendChild(productCopy);

        var quantityCell = document.createElement('td');
        var quantity = document.createElement('input');
        quantity.type = 'number';
        quantity.className = 'pos-cart-qty';
        quantity.min = '1';
        quantity.max = String(line.producto.stock);
        quantity.step = '1';
        quantity.inputMode = 'numeric';
        quantity.value = String(line.cantidad);
        quantity.setAttribute('aria-label', 'Cantidad de ' + line.producto.nombre);
        quantity.addEventListener('change', function () {
            var next = clampQuantity(quantity, line.producto.stock);
            cart[key].cantidad = next;
            renderCart();
        });
        var controls = document.createElement('div');
        controls.className = 'pos-cart-quantity';
        var minus = document.createElement('button');
        minus.type = 'button';
        minus.className = 'pos-cart-adjust';
        minus.textContent = '−';
        minus.setAttribute('aria-label', 'Disminuir cantidad de ' + line.producto.nombre);
        minus.addEventListener('click', function () { cart[key].cantidad = Math.max(1, cart[key].cantidad - 1); renderCart(); });
        var plus = document.createElement('button');
        plus.type = 'button';
        plus.className = 'pos-cart-adjust';
        plus.textContent = '+';
        plus.setAttribute('aria-label', 'Aumentar cantidad de ' + line.producto.nombre);
        plus.addEventListener('click', function () {
            if (cart[key].cantidad >= line.producto.stock) { toast('No hay más existencias disponibles.', 'error', true); return; }
            cart[key].cantidad += 1;
            renderCart();
        });
        controls.appendChild(minus);
        controls.appendChild(quantity);
        controls.appendChild(plus);
        quantityCell.appendChild(controls);

        var priceCell = document.createElement('td');
        priceCell.textContent = money(line.producto.precio);
        var subtotalCell = document.createElement('td');
        subtotalCell.className = 'pos-cart-subtotal';
        subtotalCell.textContent = moneyCents(cents(line.producto.precio) * line.cantidad);

        var removeCell = document.createElement('td');
        var remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'pos-cart-remove';
        remove.textContent = '×';
        remove.setAttribute('aria-label', 'Eliminar ' + line.producto.nombre + ' del carrito');
        remove.addEventListener('click', function () {
            delete cart[key];
            renderCart();
        });
        removeCell.appendChild(remove);

        row.appendChild(productCell);
        row.appendChild(quantityCell);
        row.appendChild(priceCell);
        row.appendChild(subtotalCell);
        row.appendChild(removeCell);
        return row;
    }

    function renderCart() {
        var body = document.getElementById('carritoBody');
        var hidden = document.getElementById('carritoCampos');
        body.replaceChildren();
        hidden.replaceChildren();
        var lines = Object.keys(cart);
        var units = 0;
        var totalCentavos = 0;

        lines.forEach(function (key, index) {
            var line = cart[key];
            units += line.cantidad;
            totalCentavos += cents(line.producto.precio) * line.cantidad;
            body.appendChild(createCartRow(key, line));
            var idField = document.createElement('input');
            idField.type = 'hidden';
            idField.name = 'items[' + index + '][producto_id]';
            idField.value = String(line.producto.id);
            var quantityField = document.createElement('input');
            quantityField.type = 'hidden';
            quantityField.name = 'items[' + index + '][cantidad]';
            quantityField.value = String(line.cantidad);
            hidden.appendChild(idField);
            hidden.appendChild(quantityField);
        });

        document.getElementById('carritoVacio').hidden = lines.length > 0;
        document.getElementById('cantidadLineas').textContent = lines.length + (lines.length === 1 ? ' línea' : ' líneas');
        document.getElementById('unidadesTotal').textContent = String(units);
        document.getElementById('totalVenta').textContent = moneyCents(totalCentavos);
        document.getElementById('resumenUnidades').textContent = units + (units === 1 ? ' producto' : ' productos');
        document.getElementById('resumenTotal').textContent = moneyCents(totalCentavos);
    }

    function selectClient(client) {
        selectedClient = client;
        document.getElementById('clienteId').value = String(client.id);
        document.getElementById('clienteSeleccionadoNombre').textContent = client.nombre;
        var details = [client.telefono || 'Sin teléfono'];
        if (client.correo) details.push(client.correo);
        if (client.deporte_interes) details.push(client.deporte_interes);
        document.getElementById('clienteSeleccionadoMeta').textContent = details.join(' · ');
        document.getElementById('clienteSeleccionado').hidden = false;
        document.getElementById('buscarCliente').value = '';
        hideResults(document.getElementById('resultadosCliente'));
        document.getElementById('resumenCliente').textContent = client.nombre;
    }

    function bindClientSearch() {
        var input = document.getElementById('buscarCliente');
        var results = document.getElementById('resultadosCliente');
        var timer = null;
        var requestNumber = 0;
        input.addEventListener('input', function () {
            window.clearTimeout(timer);
            var term = input.value.trim();
            if (term.length < 2) { hideResults(results); return; }
            timer = window.setTimeout(async function () {
                var thisRequest = ++requestNumber;
                setResultsMessage(results, 'Buscando clientes…');
                try {
                    var url = new URL(config.clientSearchUrl, window.location.href);
                    url.searchParams.set('q', term);
                    var response = await fetch(url.toString(), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
                    var data = await response.json();
                    if (thisRequest !== requestNumber || term !== input.value.trim()) return;
                    if (!response.ok) throw new Error('No fue posible buscar clientes.');
                    results.replaceChildren();
                    if (!data.resultados || data.resultados.length === 0) {
                        setResultsMessage(results, 'No encontramos clientes. Puedes agregar uno nuevo.');
                        return;
                    }
                    data.resultados.forEach(function (client) {
                        var option = document.createElement('button');
                        option.type = 'button';
                        option.className = 'pos-result';
                        option.setAttribute('role', 'option');
                        var copy = document.createElement('span');
                        copy.className = 'pos-result-copy';
                        var name = document.createElement('strong');
                        name.textContent = client.nombre;
                        var detail = document.createElement('span');
                        detail.textContent = [client.telefono || 'Sin teléfono', client.correo || ''].filter(Boolean).join(' · ');
                        copy.appendChild(name);
                        copy.appendChild(detail);
                        option.appendChild(copy);
                        option.addEventListener('click', function () { selectClient(client); });
                        results.appendChild(option);
                    });
                    results.classList.add('open');
                } catch (error) {
                    if (thisRequest === requestNumber) setResultsMessage(results, error.message || 'Error de conexión.');
                }
            }, 240);
        });
    }

    function clearClient() {
        selectedClient = null;
        document.getElementById('clienteId').value = '';
        document.getElementById('clienteSeleccionado').hidden = true;
        document.getElementById('resumenCliente').textContent = 'Pendiente';
        document.getElementById('buscarCliente').focus();
    }

    function selectChoice(button) {
        var group = button.dataset.choice;
        document.querySelectorAll('[data-choice="' + group + '"]').forEach(function (item) {
            item.setAttribute('aria-pressed', item === button ? 'true' : 'false');
        });
        if (group === 'canal') {
            selectedChannel = button.dataset.value;
            document.getElementById('canalVenta').value = selectedChannel;
            document.getElementById('resumenCanal').textContent = config.channels[selectedChannel] || selectedChannel;
        } else {
            selectedPayment = button.dataset.value;
            document.getElementById('metodoPago').value = selectedPayment;
            document.getElementById('resumenPago').textContent = config.payments[selectedPayment] || selectedPayment;
        }
    }

    function openClientModal() {
        modal.hidden = false;
        document.body.classList.add('pos-modal-open');
        modalError.hidden = true;
        document.getElementById('nuevoNombre').focus();
    }

    function closeClientModal() {
        modal.hidden = true;
        document.body.classList.remove('pos-modal-open');
        modalError.hidden = true;
    }

    document.getElementById('abrirModalCliente').addEventListener('click', openClientModal);
    document.getElementById('cerrarModalCliente').addEventListener('click', closeClientModal);
    document.getElementById('cancelarModalCliente').addEventListener('click', closeClientModal);
    modal.addEventListener('click', function (event) { if (event.target === modal) closeClientModal(); });
    document.addEventListener('keydown', function (event) { if (event.key === 'Escape' && !modal.hidden) closeClientModal(); });

    modalForm.addEventListener('submit', async function (event) {
        event.preventDefault();
        modalError.hidden = true;
        var name = document.getElementById('nuevoNombre').value.trim();
        var phone = document.getElementById('nuevoTelefono').value.trim();
        if (!name || !phone) {
            modalError.textContent = 'El nombre y el número de teléfono son obligatorios.';
            modalError.hidden = false;
            return;
        }
        var submit = document.getElementById('guardarCliente');
        submit.disabled = true;
        try {
            var response = await fetch(modalForm.action, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'},
                body: new FormData(modalForm)
            });
            var data = await response.json();
            updateCsrf(data);
            if (!response.ok || !data.ok) {
                modalError.textContent = data.mensaje || 'No fue posible guardar el cliente.';
                modalError.hidden = false;
                return;
            }
            selectClient(data.cliente);
            modalForm.reset();
            document.getElementById('nuevoDeporte').value = 'No especificado';
            closeClientModal();
            toast(data.mensaje || 'Cliente registrado.', 'success', false);
        } catch (error) {
            modalError.textContent = 'No se pudo completar la solicitud. Revisa la conexión e inténtalo nuevamente.';
            modalError.hidden = false;
        } finally {
            submit.disabled = false;
        }
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        if (Object.keys(cart).length === 0) { toast('Agrega al menos un producto al carrito.', 'error', true); return; }
        if (!selectedClient) { toast('Busca y selecciona un cliente antes de finalizar.', 'error', true); document.getElementById('buscarCliente').focus(); return; }
        if (!selectedChannel) { toast('Selecciona el canal de venta.', 'error', true); document.getElementById('tituloCanal').scrollIntoView({behavior: 'smooth', block: 'center'}); return; }
        if (!selectedPayment) { toast('Selecciona el método de pago.', 'error', true); document.getElementById('tituloPago').scrollIntoView({behavior: 'smooth', block: 'center'}); return; }

        finishButton.disabled = true;
        finishButton.textContent = 'REGISTRANDO…';
        try {
            var response = await fetch(config.saveSaleUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'},
                body: new FormData(form)
            });
            var data = await response.json();
            updateCsrf(data);
            if (!response.ok || !data.ok) {
                toast(data.mensaje || 'No se pudo registrar la venta.', 'error', !!data.persistente);
                return;
            }

            toast(data.mensaje || 'Venta registrada correctamente.', 'success', false);
            Object.keys(cart).forEach(function (key) { delete cart[key]; });
            renderCart();
            clearClient();
            selectedChannel = '';
            selectedPayment = '';
            document.getElementById('canalVenta').value = '';
            document.getElementById('metodoPago').value = '';
            document.querySelectorAll('[data-choice]').forEach(function (button) { button.setAttribute('aria-pressed', 'false'); });
            document.getElementById('resumenCanal').textContent = 'Pendiente';
            document.getElementById('resumenPago').textContent = 'Pendiente';
            document.getElementById('buscarNombre').value = '';
            document.getElementById('buscarReferencia').value = '';
            document.getElementById('buscarCliente').value = '';
            document.getElementById('ayudaBusqueda').hidden = false;
            hideResults(document.getElementById('resultadosNombre'));
            hideResults(document.getElementById('resultadosReferencia'));
            hideResults(document.getElementById('resultadosCliente'));
            window.scrollTo({top: 0, behavior: 'smooth'});
        } catch (error) {
            toast('No se pudo completar la solicitud. No se confirmó el resultado; consulta el historial antes de reintentar.', 'error', true);
        } finally {
            finishButton.disabled = false;
            finishButton.textContent = 'FINALIZAR VENTA';
        }
    });

    bindProductSearch('buscarNombre', 'resultadosNombre', 'nombre');
    bindProductSearch('buscarReferencia', 'resultadosReferencia', 'referencia');
    bindClientSearch();
    document.getElementById('quitarCliente').addEventListener('click', clearClient);
    document.querySelectorAll('[data-choice]').forEach(function (button) { button.addEventListener('click', function () { selectChoice(button); }); });
    renderCart();
})();
